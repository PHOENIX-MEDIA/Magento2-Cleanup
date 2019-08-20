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

use Magento\AdminNotification\Model\InboxFactory;
use Phoenix\Cleanup\Api\HandlerInterface;
use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;

class AdminNotifications extends AbstractHandler implements HandlerInterface
{
    /**
     * @var InboxFactory
     */
    protected $adminNotificationInboxFactory;

    public function __construct(Config $config, Logger $logger, InboxFactory $adminNotificationInboxFactory)
    {
        parent::__construct($config, $logger);
        $this->adminNotificationInboxFactory = $adminNotificationInboxFactory;
    }

    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isAdminNotificationsCleanupEnabled();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        try {
            $notifications      = $this->adminNotificationInboxFactory->create();
            $notificationIds    = $notifications->getCollection()->addFieldToFilter(
                array(
                    'is_read',
                    'is_remove'
                ),
                array(
                    array('eq' => 0),
                    array('eq' => 0)
                )
            )->getAllIds();

            $deleteDays = $this->config->getAdminNotificationsKeepDays();

            foreach ($notificationIds as $notificationId) {
                $notification = $notifications->load($notificationId);

                //get days
                $today = new \Zend_Date();
                $dateAdded = new \Zend_Date();
                $dateAdded->setLocale('en');
                $dateAddedValue = $notification->getDateAdded();
                $dateAdded->set($dateAddedValue);
                $diff = $today->sub($dateAdded)->toValue();
                $days = ceil($diff / 60 / 60 / 24) + 1;

                //@todo: check strange behavior where some dates are returned as future dates
                if ($days > $deleteDays || $deleteDays == 0 || $days < 0) {
                    if ($notification->getIsRead() != 1) {
                        $notification->setIsRead(1);
                    }

                    if ($this->config->getDeleteAdminNotifications()) {
                        $notification->setIsRemove(1);
                    }

                    if ($notification->hasDataChanges()) {
                        $notification->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }
}
