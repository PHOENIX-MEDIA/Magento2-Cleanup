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
namespace Phoenix\Cleanup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Phoenix\Cleanup\Model\Config;

class Data extends AbstractHelper
{
    /**
     * timestamp of last ping to db
     *
     * @var int
     */
    protected $lastPing = 0;

    /**
     * number of seconds between database pings
     *
     * @var int
     */
    protected $pingFrequency = 0;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;


    /**
     * Data constructor.
     * @param Context $context
     * @param Config $config
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        Config $config,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;

        $this->pingFrequency = $config->getDatabasePingTime();
    }

    /**
     * ping the database to avoid a mysql timeout
     */
    public function pingDb()
    {
        if (time() - $this->lastPing >= $this->pingFrequency) {
            $this->lastPing = time();
            $this->resourceConnection->getConnection('core_read')->fetchOne('SELECT 1');
        }
    }

    /**
     * returns formatted bytes
     *
     * @param int $bytes
     * @param int $decimals
     *
     * @return string
     */
    public function getBytesFormatted($bytes, $decimals = 0)
    {
        if ($bytes <= 0) {
            return 'n/a';
        }

        $units     = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        $unitIndex = 0;

        while ($bytes > 1024) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, $decimals) . ' ' . $units[$unitIndex];
    }
}
