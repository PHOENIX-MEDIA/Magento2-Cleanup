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

class FilesFolders extends AbstractFiles implements HandlerInterface
{
    /**
     * Returns is configuration allowed execution
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isOptionalFolderCleanupEnabled();
    }

    /**
     * Runs cleanup
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function cleanup()
    {
        $this->init();

        foreach ($this->config->getCleanupOptionalFolders() as $folder) {
            $pathToCleanup = $this->directoryList->getPath(DirectoryList::ROOT) . DIRECTORY_SEPARATOR . $folder['path'];
            if (substr($pathToCleanup, -1) !== DIRECTORY_SEPARATOR) {
                $pathToCleanup .= DIRECTORY_SEPARATOR;
            }
            $pathToCleanup = str_replace('//', DIRECTORY_SEPARATOR, $pathToCleanup);

            if (file_exists($pathToCleanup) === true) {
                $archivePath = $pathToCleanup . self::CLEANUP_ARCHIVE_DIR;
                if (file_exists($archivePath) === false) {
                    if ($this->io->mkdir($archivePath) === false) {
                        $this->log('folder: ' . $pathToCleanup . ' is not writable: skipping!');
                    }
                }
                $archivePath .= DIRECTORY_SEPARATOR;

                $currentArchive = $archivePath . date('Ymd_His');
                if ($this->io->mkdir($currentArchive) === false) {
                    $this->log('error creating archive subfolder: ' . $currentArchive);
                }
                $currentArchive .= DIRECTORY_SEPARATOR;

                //check if there are any files
                $files = $this->getFileList($pathToCleanup, $folder['mask']);
                $this->log('cleaning up ' . count($files) . ' files in optional path: ' . $folder['path']);

                foreach ($files as $file) {
                    $this->logger->debug('cleaning up: ' . $file);
                    $this->rotateFiles($file, $currentArchive . basename($file) . self::CLEANUP_ARCHIVE_FILE_EXTENSION, $this->dryRun);
                    $this->pingDb();
                }

                $this->cleanupArchive($archivePath, $folder['days']);

                $this->log('cleaning up of optional path: ' . $folder['path'] . ' finished. ' . count($files) . ' files cleaned.');
                $this->log(str_repeat('-', 72));
            } else {
                $this->log('optional path: ' . $folder['path'] . ' does not exist in Magento base directory.');
                $this->log(str_repeat('-', 72));
            }
        }

        $this->logSavedBytes();

        return $this;
    }
}
