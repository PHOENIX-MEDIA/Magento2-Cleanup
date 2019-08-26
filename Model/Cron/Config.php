<?php
namespace Phoenix\Cleanup\Model\Cron;

use Magento\Framework\App\Config\Value as AppConfigValue;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Config extends AppConfigValue
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/phoenix_cleanup_magento/schedule/cron_expr';

    /**
     * @var ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->_configValueFactory = $configValueFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $cronExpr   = $this->getValue();
        $configPath = self::CRON_STRING_PATH;

        try {
            $this->_configValueFactory->create()->load(
                $configPath,
                'path'
            )->setValue(
                $cronExpr
            )->setPath(
                $configPath
            )->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }
}
