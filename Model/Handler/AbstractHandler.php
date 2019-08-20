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

use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;

class AbstractHandler
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * AbstractEntity constructor.
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * log to file
     *
     * @param $message
     */
    protected function log($message)
    {
        $this->logger->info($message);
    }
}
