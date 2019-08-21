<?php
namespace Phoenix\Cleanup\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Serializer $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Serializer $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Return if cleanup is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/general/is_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return is dry run
     *
     * @return bool
     */
    public function isDryRun()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/general/dry_run',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return database ping time
     *
     * @return int
     */
    public function getDatabasePingTime()
    {
        return intval($this->scopeConfig->getValue(
            'phoenix_cleanup/general/database_ping_time',
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Return cleanup log files
     *
     * @return bool
     */
    public function getCleanupLogFiles()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/files/cleanup_logfiles',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return log file keep days
     *
     * @return int
     */
    public function getKeepLogFileDays()
    {
        return intval($this->scopeConfig->getValue(
            'phoenix_cleanup/files/keep_logfile_days',
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Returns whether all files should be cleaned up
     *
     * @return bool
     */
    public function getCleanupAllFiles()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/files/cleanup_all_files',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return if reports should get cleaned
     *
     * @return bool
     */
    public function getCleanupReports()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/files/cleanup_reports',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return days to keep reports
     *
     * @return int
     */
    public function getKeepReportsDays()
    {
        return intval($this->scopeConfig->getValue(
            'phoenix_cleanup/files/keep_reports_days',
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Return if optional folder cleanup is enabled
     *
     * @return bool
     */
    public function isOptionalFolderCleanupEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/files/cleanup_optional_folders_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns optional cleanup folders
     *
     * @return array
     */
    public function getCleanupOptionalFolders()
    {
        $folders = $this->serializer->unserialize($this->scopeConfig->getValue(
            'phoenix_cleanup/files/cleanup_optional_folders',
            ScopeInterface::SCOPE_STORE
        ));
        if (!is_array($folders)) {
            $folders = [];
        }

        return $folders;
    }

    /**
     * Return if media should get cleaned up
     *
     * @return bool
     */
    public function getCleanupMedia()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/media/cleanup_media',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return retention days for media
     *
     * @return int
     */
    public function getKeepMediaDays()
    {
        return intval($this->scopeConfig->getValue(
            'phoenix_cleanup/media/keep_days',
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Return if customer quotes should get cleaned
     *
     * @return bool
     */
    public function getCleanupSalesQuoteCustomers()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/quotes/cleanup_sales_quote_customers',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return days to keep customer quotes
     *
     * @return int
     */
    public function getKeepCartQuotesCustomerDays()
    {
        return intval($this->scopeConfig->getValue(
            'phoenix_cleanup/quotes/keep_cart_quotes_customers_days',
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Return if visitor quotes should get cleaned
     *
     * @return bool
     */
    public function getCleanupSalesQuoteGuests()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/quotes/cleanup_sales_quote_guests',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return days to keep visitor quotes
     *
     * @return int
     */
    public function getKeepCartQuotesGuestDays()
    {
        return intval($this->scopeConfig->getValue(
            'phoenix_cleanup/quotes/keep_cart_quotes_guests_days',
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Return if admin notifications should get cleaned up
     *
     * @return bool
     */
    public function isAdminNotificationsCleanupEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/adminnotifications/is_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return retention days for admin notifications
     *
     * @return int
     */
    public function getAdminNotificationsKeepDays()
    {
        return intval($this->scopeConfig->getValue(
            'phoenix_cleanup/adminnotifications/keep_days',
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Return delete admin notfications
     *
     * @return bool
     */
    public function getDeleteAdminNotifications()
    {
        return (bool)$this->scopeConfig->getValue(
            'phoenix_cleanup/adminnotifications/delete_notifications',
            ScopeInterface::SCOPE_STORE
        );
    }
}
