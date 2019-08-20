<?php

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
