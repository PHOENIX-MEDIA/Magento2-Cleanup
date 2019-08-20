<?php
/**
 * PHOENIX MEDIA - Cleanup
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to license that is bundled with
 * this package in the file LICENSE.
 *
 * @category   Phoenix
 * @package	   Phoenix_Cleanup
 * @copyright  Copyright (c) 2013-2019 PHOENIX MEDIA GmbH (http://www.phoenix-media.eu)
 */
namespace Phoenix\Cleanup\Model\Handler;

use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Phoenix\Cleanup\Api\HandlerInterface;
use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;

class QuotesCustomer extends AbstractHandler implements HandlerInterface
{
    /**
     * @var CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * Quotes constructor.
     * @param Config $config
     * @param Logger $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Config $config, Logger $logger, CollectionFactory $collectionFactory)
    {
        parent::__construct($config, $logger);
        $this->quoteCollectionFactory = $collectionFactory;
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
        $lifetime = $keepCartQuotesDays * 86400;

        /** @var $quotes \Magento\Quote\Model\ResourceModel\Quote\Collection */
        $quotes = $this->quoteCollectionFactory->create();

        $quotes->addFieldToFilter('updated_at', ['to' => date('Y-m-d', time() - $lifetime)]);
        $quotes->addFieldToFilter('customer_id', ['neq' => 0]);
        $quotes->addFieldToFilter('customer_id', ['notnull' => true]);

        if ($this->config->isDryRun() === false) {
            $recordCount = $quotes->count();
            $quotes->walk('delete');
            $this->log($recordCount . ' records deleted');
        }

        return $this;
    }
}
