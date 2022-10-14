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

use DateTime;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Archive;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Phoenix\Cleanup\Api\HandlerInterface;
use Phoenix\Cleanup\Helper\Data as Helper;
use Phoenix\Cleanup\Logger\Logger;
use Phoenix\Cleanup\Model\Config;
use Zend_Db_Select;

class Media extends AbstractFiles implements HandlerInterface
{
    /**
     * Magento media path
     *
     * @var string
     */
    protected $magentoMediaPath;

    /**
     * path where the images are copied to until deletion
     *
     * @var string
     */
    protected $mediaRecycleBinPath;

    /**
     * Magento cache path
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Magento watermark path
     *
     * @var string
     */
    protected $watermarkPath;

    /**
     * Magento placeholder path
     *
     * @var string
     */
    protected $placeholderPath;

    /**
     * list of media files in the media directory
     *
     * @var array
     */
    protected $fileList = [];

    /**
     * list of files with no assignment
     *
     * @var array
     */
    protected $deleteList = [];

    /**
     * contains number of files in folders and sub-folders
     *
     * @var array
     */
    protected $directoryHashMap = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        Config $config,
        Logger $logger,
        Helper $helper,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        Archive $archive,
        File $io,
        DateTime $dateTime
    ) {
        parent::__construct($config, $logger, $helper, $filesystem, $directoryList, $archive, $io, $dateTime);

        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->getCleanupMedia();
    }

    /**
     * Runs cleanup
     *
     * @return HandlerInterface
     */
    public function cleanup(): HandlerInterface
    {
        $this->init();
        $this->io->mkdir($this->mediaRecycleBinPath);

        $this->log('start collecting files from ' . $this->magentoMediaPath);
        $this->getFilesRecursive($this->magentoMediaPath);

        $this->log('detected ' . count($this->fileList) . ' files in media folder');

        $this->createDeleteList();
        $this->deleteFiles();
        $this->cleanupMediaRecycleBin();

        $this->log('files in media directory: ' . count($this->fileList));
        $this->log('files with no assignment: ' . count($this->deleteList));

        $this->log('checking for empty directories in media folder');
        $this->deleteEmptyFolders();
        $this->log('finished checking for empty directories in media folder');

        //output the savings in bytes
        $bytesFormatted = $this->helper->getBytesFormatted($this->sizeCount, 2);
        $this->logger->info(
            'moved to recycle bin: ' . number_format($this->sizeCount, 0, ',', '.') . ' Bytes (' . $bytesFormatted . ')'
        );

        return $this;
    }

    /**
     * Initialize needed paths
     *
     * @return void
     * @throws FileSystemException
     */
    protected function init(): void
    {
        parent::init();
        $this->magentoMediaPath = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        $this->mediaRecycleBinPath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . 'media_recyclebin';
        $this->cachePath = $this->magentoMediaPath . DIRECTORY_SEPARATOR . 'cache';
        $this->placeholderPath = $this->magentoMediaPath . DIRECTORY_SEPARATOR . 'placeholder';
        $this->watermarkPath = $this->magentoMediaPath . DIRECTORY_SEPARATOR . 'watermark';
    }

    /**
     * Remove the files from the recycle bin
     *
     * @return void
     */
    public function cleanupMediaRecycleBin(): void
    {
        $mediaCleanupDays = $this->config->getKeepMediaDays();
        $this->cleanupArchive($this->mediaRecycleBinPath, $mediaCleanupDays, true);
    }

    /**
     * Recursively detect empty folders
     *
     * @param string $path
     * @return void
     */
    protected function detectEmptyFolders(string $path): void
    {
        $directories = glob($path . '/*', GLOB_ONLYDIR);
        $files = glob($path . '/*.*');

        $pathParts = explode('/', str_replace($this->magentoMediaPath, '', $path));

        $depth = count($pathParts) - 1;
        $fileCount = count($files);
        $currentPath = $path;

        for ($level = $depth; $level >= 0; $level--) {
            if (isset($pathParts[$level]) && $pathParts[$level] != '') {
                if (empty($this->directoryHashMap[$level][$currentPath])) {
                    $this->directoryHashMap[$level][$currentPath] = $fileCount;
                } else {
                    $this->directoryHashMap[$level][$currentPath] += $fileCount;
                }

                //set path to one level above
                $currentPath = substr($currentPath, 0, (strlen('/' . $pathParts[$level]) * -1));
            } else {
                if ($level != 0) {
                    $currentPath = substr($currentPath, 0, -1);
                }
            }
        }

        // Do recursive call
        if (!empty($directories)) {
            foreach ($directories as $directory) {
                //ignore placeholder and watermark
                if ($directory != $this->placeholderPath && $directory != $this->watermarkPath) {
                    $this->detectEmptyFolders($directory);
                }
            }
        }
    }

    /**
     * Delete empty folders in media directory
     *
     * @return void
     */
    protected function deleteEmptyFolders(): void
    {
        $this->detectEmptyFolders($this->magentoMediaPath);

        //delete the empty folders
        $depth = !empty($this->directoryHashMap) ? max(array_keys($this->directoryHashMap)) : 0;
        for ($level = $depth; $level >= 0; $level--) {
            if (!empty($this->directoryHashMap[$level])) {
                foreach ($this->directoryHashMap[$level] as $directory => $fileCount) {
                    if ($fileCount == 0) {
                        try {
                            if (!$this->dryRun) {
                                rmdir($directory);
                            }
                            $this->log('delete empty folder: ' . $directory);
                        } catch (Exception $e) {
                            $this->log('error deleting empty folder: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * Creates a list of files which are going to be deleted
     *
     * @return void
     */
    protected function createDeleteList(): void
    {
        $this->log('checking database');

        /* @var ResourceConnection $resource */
        $resource = $this->resourceConnection;
        $connection = $resource->getConnection('core_read');

        $tblCatalogProductEntityMediaGallery = $this->resourceConnection
            ->getTableName('catalog_product_entity_media_gallery');
        $query = $connection->select()
            ->from($tblCatalogProductEntityMediaGallery)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(['value'])
            ->group('value');
        $result = $connection->fetchAll($query);

        $dbValues = [];
        foreach ($result as $row) {
            $dbValues[] = $row['value'];
        }

        $this->deleteList = array_flip(
            array_diff(
                $this->fileList,
                $dbValues
            )
        );

        $this->log('done');
    }

    /**
     * Get files from a directory recursively
     *
     * @param string $path
     * @return void
     */
    protected function getFilesRecursive(string $path): void
    {
        $directories = glob($path . '/*', GLOB_ONLYDIR);

        $files = glob($path . '/*.*');

        if (!empty($files)) {
            foreach ($files as $file) {
                // Check if current entry is not a directory, as directories may contain a . as well
                if (!is_dir($file)) {
                    $this->fileList[$file] = $this->sanitizeFilePathForDbLookup($file);
                }
            }
        }

        if (!empty($directories)) {
            foreach ($directories as $directory) {
                // Ignore placeholder and watermark
                if ($directory != $this->placeholderPath && $directory != $this->watermarkPath) {
                    $this->getFilesRecursive($directory);
                } else {
                    switch ($directory) {
                        case $this->placeholderPath:
                            $this->log('skipping placeholder path');
                            break;
                        case $this->watermarkPath:
                            $this->log('skipping watermark path');
                            break;
                    }
                }

                $this->pingDb();
            }
        }
    }

    /**
     * Delete files from the filesystem and move them to the media recycle bin
     *
     * @return void
     */
    protected function deleteFiles(): void
    {
        if (count($this->deleteList) > 0) {
            //check and create the recycle bin path
            $currentRecycleBinPath = $this->mediaRecycleBinPath . DIRECTORY_SEPARATOR . date('Ymd_His');
            if (!file_exists($currentRecycleBinPath)) {
                $this->io->mkdir($currentRecycleBinPath, 0777, true);
            }
        }

        $deleteCounter = 0;
        foreach ($this->deleteList as $file) {
            if (file_exists($file) === true) {
                if (!$this->dryRun) {
                    try {
                        //get recycle bin path
                        $newFilePath = $currentRecycleBinPath . str_replace($this->magentoMediaPath, '', $file);
                        $this->log('moving: ' . $file . ' to recycle bin: ' . $newFilePath);

                        //check if directory exists
                        $pathToCheck = str_replace(basename($newFilePath), '', $newFilePath);
                        if (!file_exists($pathToCheck)) {
                            $this->io->mkdir($pathToCheck, 0777, true);
                        }

                        //move to recycle bin
                        copy($file, $newFilePath);

                        //delete the original file
                        $this->calculateSavings($file);
                        $this->log('deleting: ' . $file);
                        unlink($file);
                    } catch (Exception $e) {
                        $this->log('error during file cleanup: ' . $e->getMessage());
                    }
                }

                $deleteCounter++;
            }

            $this->pingDb();
        }

        $this->log('deleted ' . $deleteCounter . ' obsolete media files');
    }

    /**
     * Sanitize given filepath to match the value as stored in the database
     *
     * @param string $file
     * @return string
     */
    protected function sanitizeFilePathForDbLookup(string $file): string
    {
        $file = str_replace($this->magentoMediaPath, '', $file);

        if (substr($file, 0, 6) === '/cache') {
            $fileParts = explode('/', $file);
            array_shift($fileParts);
            array_shift($fileParts);
            array_shift($fileParts);

            $file = '/' . implode('/', $fileParts);
        }

        return $file;
    }
}
