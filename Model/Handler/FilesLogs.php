<?php

namespace Phoenix\Cleanup\Model\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Phoenix\Cleanup\Api\HandlerInterface;
use Phoenix\Cleanup\Helper\Data as Helper;
use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;

class FilesLogs extends AbstractFiles implements HandlerInterface
{
    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->getCleanupLogFiles();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     */
    public function cleanup()
    {
        $this->init();
        $this->io->mkdir($this->logArchivePath);

        if ($this->config->getCleanupAllFiles()) {
            $fileMask = '*';
        } else {
            $fileMask = '*.log';
        }

        // check if there are any files
        $logFiles = glob($this->logPath . $fileMask);
        foreach($logFiles as $key => $file) {
            if (is_file($file) == false) {
                unset($logFiles[$key]);
            }
        }

        $currentArchive = $this->logArchivePath . DIRECTORY_SEPARATOR . date('Ymd_His');
        if (count($logFiles) > 0) {
            $this->io->mkdir($currentArchive);
            $currentArchive .= DIRECTORY_SEPARATOR;
        }

        // cleanup old log first
        if (file_exists($this->cleanupLogFile) === true) {
            $this->rotateFiles($this->cleanupLogFile, $currentArchive . self::CLEANUP_LOG_FILE . self::CLEANUP_ARCHIVE_FILE_EXTENSION);
            $this->logger->debug('cleaned up old '.basename($this->cleanupLogFile).' file');
        }

        // check if it is a dry-run
        if ($this->dryRun == true) {
            $this->log('!!! running in dry-run mode !!! No files or folders are deleted');
        }

        //process other log files
        $this->log('cleaning up log-files');
        foreach ($logFiles as $logFile) {
            if ($logFile != $this->cleanupLogFile) {
                $this->logger->debug('cleaning up: ' . $logFile);
                $this->rotateFiles($logFile, $currentArchive . basename($logFile) . self::CLEANUP_ARCHIVE_FILE_EXTENSION, $this->dryRun);
                $this->pingDb();
            }
        }

        $this->cleanupArchive($this->logArchivePath);
        $this->logSavedBytes();

        return $this;
    }
}
