<?php

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
