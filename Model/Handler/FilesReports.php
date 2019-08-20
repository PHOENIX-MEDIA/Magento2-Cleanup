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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Phoenix\Cleanup\Api\HandlerInterface;
use Phoenix\Cleanup\Helper\Data as Helper;
use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;

class FilesReports extends AbstractFiles implements HandlerInterface
{
    /**
     * path for report files
     *
     * @var string
     */
    protected $reportPath;

    /**
     * path for the report archive
     *
     * @var string
     */
    protected $reportArchivePath;


    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->getCleanupReports();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        $this->init();
        $this->io->mkdir($this->reportArchivePath);

        //check if there are any files and create archive directory only if there is something to archive
        $files = glob($this->reportPath . '/*');
        foreach($files as $key => $file) {
            if (is_file($file) == false) {
                unset($files[$key]);
            }
        }

        $currentArchive = $this->reportArchivePath . DIRECTORY_SEPARATOR . date('Ymd_His');
        if (count($files)) {
            $this->io->mkdir($currentArchive);
            $currentArchive .= DIRECTORY_SEPARATOR;
        }

        // process reports
        foreach ($files as $file) {
            $this->logger->debug('cleaning up: ' . $file);
            $this->rotateFiles($file, $currentArchive . basename($file) . self::CLEANUP_ARCHIVE_FILE_EXTENSION, $this->dryRun);
            $this->pingDb();
        }

        $this->cleanupArchive($this->reportArchivePath, $this->config->getKeepReportsDays());
        $this->logSavedBytes();

        return $this;
    }

    protected function init()
    {
        parent::init();
        $this->reportPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . 'report';
        $this->reportArchivePath = $this->reportPath . DIRECTORY_SEPARATOR . self::CLEANUP_ARCHIVE_DIR;
    }
}
