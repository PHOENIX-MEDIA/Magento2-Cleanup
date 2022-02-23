<?php declare(strict_types=1);
/**
 * @category Phoenix
 * @package Phoenix\Cleanup\Setup\Patch
 * @copyright Copyright (c) 2022 PHOENIX MEDIA GmbH (http://www.phoenix-media.eu)
 */

namespace Phoenix\Cleanup\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

class RemoveConfigValueInDefaultCronGroup implements DataPatchInterface
{
    const CONFIG_PATH_TO_REMOVE = 'crontab/default/jobs/phoenix_cleanup_magento/schedule';

    /**
     * @var Data
     */
    private $configResource;
    /**
     * @var CollectionFactory
     */
    private $configCollectionFactory;


    public function __construct(Data $configResource, CollectionFactory $configCollectionFactory)
    {
        $this->configResource = $configResource;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $collection = $this->configCollectionFactory->create()
            ->addPathFilter(self::CONFIG_PATH_TO_REMOVE);
        foreach ($collection as $config) {
            $this->configResource->delete($config);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
