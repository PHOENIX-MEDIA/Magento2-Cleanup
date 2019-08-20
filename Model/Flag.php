<?php
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
