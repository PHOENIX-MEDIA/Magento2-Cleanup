<?php
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

        // @todo $handlerPool is not correctly set by di.xml. fix issue.
        if (count($this->handlerPool) == 0) {
            $this->handlerPool['logFiles'] = 'Phoenix\Cleanup\Model\Handler\FilesLogs';
            $this->handlerPool['reportFiles'] = 'Phoenix\Cleanup\Model\Handler\FilesReports';
            $this->handlerPool['optionalFolders'] = 'Phoenix\Cleanup\Model\Handler\FilesFolders';
            //$this->handlerPool['media'] = 'Phoenix\Cleanup\Model\Handler\Media';
            //$this->handlerPool['customerQuotes'] = 'Phoenix\Cleanup\Model\Handler\QuotesCustomer';
            //$this->handlerPool['guestQuotes'] = 'Phoenix\Cleanup\Model\Handler\QuotesGuest';
            $this->handlerPool['adminNotification'] = 'Phoenix\Cleanup\Model\Handler\AdminNotifications';
        }
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
