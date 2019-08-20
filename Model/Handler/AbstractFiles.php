<?php

namespace Phoenix\Cleanup\Model\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Phoenix\Cleanup\Api\HandlerInterface;
use Phoenix\Cleanup\Helper\Data as Helper;
use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;

abstract class AbstractFiles extends AbstractHandler
{
    const CLEANUP_LOG_FILE = 'cleanup.log';
    const CLEANUP_ARCHIVE_DIR = 'archive';
    const CLEANUP_ARCHIVE_FILE_EXTENSION = '.gz';

    /**
     * Magento log path
     *
     * @var string
     */
    protected $logPath;

    /**
     * Path to the log archive
     *
     * @var string
     */
    protected $logArchivePath;

    /**
     * Path to cleanup log file
     *
     * @var string
     */
    protected $cleanupLogFile;

    /**
     * @var int
     */
    protected $sizeCount = 0;

    /**
     * really delete items
     *
     * @var bool
     */
    protected $dryRun = false;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Archive
     */
    protected $archive;

    /**
     * @var File
     */
    protected $io;


    /**
     * Files constructor.
     * @param Config $config
     * @param Logger $logger
     * @param Helper $helper
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param Archive $archive
     * @param File $io
     */
    public function __construct(
        Config $config,
        Logger $logger,
        Helper $helper,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        Archive $archive,
        File $io
    ) {
        parent::__construct($config, $logger);
        $this->helper = $helper;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->archive = $archive;
        $this->io = $io;
    }

    /**
     * ping the database to avoid a mysql timeout
     */
    protected function pingDb()
    {
        $this->helper->pingDb();
    }

    /**
     * do initialization stuff
     */
    protected function init()
    {
        $this->logPath             = $this->directoryList->getPath(DirectoryList::LOG) . DIRECTORY_SEPARATOR;
        $this->logArchivePath      = $this->logPath . self::CLEANUP_ARCHIVE_DIR;
        $this->cleanupLogFile      = $this->logPath . self::CLEANUP_LOG_FILE;
        $this->dryRun              = $this->config->isDryRun();
    }

    /**
     * delete path (with option for recursive)
     *
     * @param string $path
     * @param bool   $recursive
     */
    protected function deletePath($path, $recursive = false)
    {
        if (is_dir($path) == true) {
            $this->io->rmdir($path, $recursive);
        } else {
            $this->calculateSavings($path);
            $this->io->rm($path);
        }
    }

    protected function logSavedBytes()
    {
        if ($this->sizeCount) {
            $bytesFormatted = $this->helper->getBytesFormatted($this->sizeCount, 2);
            $this->logger->info(
                'saved: ' . number_format($this->sizeCount, 0, ',', '.') . ' bytes (' . $bytesFormatted . ')'
            );
        }
    }

    /**
     * cleanup archive folder
     *
     * @param string $archivePath
     * @param int    $cleanupDays
     * @param bool   $recursive
     */
    protected function cleanupArchive($archivePath, $cleanupDays = null, $recursive = true)
    {
        $this->log('cleaning up archive: ' . $archivePath);

        if (empty($cleanupDays)) {
            $logFileDays = $this->config->getKeepLogFileDays();
        } else {
            $logFileDays = $cleanupDays;
        }

        $this->log('days to keep files in archive (' . $archivePath . '): ' . $logFileDays);

        $expiredTimestamp   = mktime(23, 59, 59, date('m'), (date('d') - $logFileDays), date('Y'));
        $archiveFolders     = glob($archivePath . '/*', GLOB_ONLYDIR);

        if (is_array($archiveFolders)) {
            foreach ($archiveFolders as $folder) {
                $folderTimestamp    = (int) (str_replace('_', '', basename($folder)));
                $expired            = (int) (date('YmdHis', $expiredTimestamp));

                if ($folderTimestamp < $expired) {
                    $this->deletePath($folder, $recursive);
                    $this->log('deleted outdated archive folder: ' . $folder);
                }
            }
        }

        $this->log('finished cleaning up archive: ' . $archivePath);
    }

    /**
     * returns a list of files within a path
     *
     * @param string $path
     * @param string $mask
     *
     * @return array
     */
    protected function getFileList($path, $mask = '*')
    {
        $this->logger->debug('checking files in folder: ' . $path);

        //setting file mask
        if (empty($mask) == true) {
            $fileMask = '*';
        } else {
            $fileMask = $mask;
        }
        $this->logger->debug('file mask: ' . $fileMask);

        $files = glob($path . '/' . $fileMask);

        $fileList = [];
        foreach ($files as $file) {
            if (is_dir($file) == false) {
                $fileList[] = $file;
            } else {
                $this->logger->debug('skipping folder: ' . $file);
            }
        }

        return $fileList;
    }

    /**
     * calculate savings with deleted / zipped files
     *
     * @param string $oldFile
     * @param string $newFile
     */
    protected function calculateSavings($oldFile, $newFile = null)
    {
        $this->sizeCount += intval(filesize($oldFile));
        if ($newFile) {
            $this->sizeCount -= intval(filesize($newFile));
        }
    }

    /**
     * @param string $src
     * @param string $dst
     * @param bool $keepSrc
     */
    protected function rotateFiles($src, $dst, $keepSrc = false)
    {
        try {
            $this->archive->pack($src, $dst);
            $this->calculateSavings($src, $dst);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->io->cp($src, dirname($dst) . DIRECTORY_SEPARATOR . basename($src));
        }

        if ($keepSrc === false) {
            $this->io->rm($src);
        }
    }
}
