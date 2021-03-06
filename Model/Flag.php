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
namespace Phoenix\Cleanup\Model;

class Flag extends \Magento\Framework\Flag
{
    const FLAG_TTL = 300;
    const FLAG_STATE_RUNNING = 1;
    const FLAG_STATE_STOPPED = 0;

    /**
     * @var string
     */
    protected $_flagCode = 'phoenix_cleanup';


    /**
     * @return bool
     */
    public function isRunning()
    {
        if (($this->getState() == self::FLAG_STATE_RUNNING) &&
            (time() <= strtotime($this->getLastUpdate()) + self::FLAG_TTL)) {
            return true;
        }
        return false;
    }

    /**
     * @return \Magento\Framework\Flag
     */
    public function start()
    {
        try {
            $this->setState(self::FLAG_STATE_RUNNING)->save();
        } catch (\Exception $e) {
            // do nothing
        }
        return $this;
    }

    /**
     * @return \Magento\Framework\Flag
     */
    public function stop()
    {
        try {
            $this->setState(self::FLAG_STATE_STOPPED)->save();
        } catch (\Exception $e) {
            // do nothing
        }
        return $this;
    }
}
