<?php

namespace Phoenix\Cleanup\Model\Handler;

use Magento\Framework\App\ResourceConnection;
use Phoenix\Cleanup\Api\HandlerInterface;
use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;

class QuotesCustomer extends AbstractHandler implements HandlerInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Quotes constructor.
     * @param Config $config
     * @param Logger $logger
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(Config $config, Logger $logger, ResourceConnection $resourceConnection)
    {
        parent::__construct($config, $logger);
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->getCleanupSalesQuoteCustomers();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        $keepCartQuotesDays = $this->config->getKeepCartQuotesCustomerDays();
        $this->log('days to keep quotes for registered users in database: ' .$keepCartQuotesDays);

        /* @var $resource Mage_Core_Model_Resource */
        $resource = $this->resourceConnection;
        $connection = $resource->getConnection('core_write');
        $tblSalesFlatQuotes = $this->resourceConnection->getTableName('sales/quote');

        $query = "
                SELECT COUNT(entity_id) as cnt
                    FROM " . $tblSalesFlatQuotes . "
                    WHERE updated_at < (CURRENT_DATE - INTERVAL " . $keepCartQuotesDays . " DAY)
                        AND customer_id <> 0
                        AND customer_id IS NOT NULL
            ";

        $recordCount = $connection->fetchOne($query);
        $this->log($recordCount . ' records detected');

        if ($this->dryRun == false) {
            $query = "
                DELETE
                    FROM " . $tblSalesFlatQuotes . "
                    WHERE updated_at < (CURRENT_DATE - INTERVAL " . $keepCartQuotesDays . " DAY)
                        AND customer_id <> 0
                        AND customer_id IS NOT NULL
            ";

            $connection->query($query);
            $this->log($recordCount . ' records deleted');
        }

        unset($connection);
        unset($resource);

        return $this;
    }
}
