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
namespace Phoenix\Cleanup\Api;

interface HandlerInterface
{
    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup();
}
