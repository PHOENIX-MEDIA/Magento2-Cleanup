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

use Magento\Framework\ObjectManagerInterface;
use Phoenix\Cleanup\Api\HandlerInterface;

/**
 * Class Resolver
 * @package Phoenix\Cleanup
 */
class Resolver
{
    /**
     * Handler pool
     *
     * @var array
     */
    protected $handlerPool = [];

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @param array $handlerPool
	 */
    public function __construct(
		ObjectManagerInterface $objectManager,
        $handlerPool = []
    ) {
        $this->objectManager = $objectManager;
        $this->handlerPool = $handlerPool;
	}

    /**
     * Get handles keys
     *
     * @return array
     */
	public function getHandlers()
    {
        return array_keys($this->handlerPool);
    }

    /**
     * Return license block type.
     *
     * @param string $handlerKey
     *
     * @return HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function get($handlerKey)
    {
        if (!isset($this->handlerPool[$handlerKey])) {
            throw new \InvalidArgumentException('Requested handler "'.$handlerKey.'" not found.');
        }

        $handler = $this->objectManager->create($this->handlerPool[$handlerKey]);
        if (!($handler instanceof HandlerInterface)) {
            throw new \InvalidArgumentException('Handler does not implement HandlerInterface');
        }
        return $handler;
    }
}
