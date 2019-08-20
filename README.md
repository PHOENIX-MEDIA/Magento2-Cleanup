## Phoenix Media Cleanup
The module archives and removes old entities. It supports log files, product images and quotes.

### What it does

For files is basically replaces logrotate. It archives and compresses log files and cleans
up the archive folder after a configurable period.
For product images it finds unreferenced files in media/catalog/product and moves them in a
recycle bin which gets purged after a configurable period.
For quotes is simple removes customer and guest quotes after configurable period.

### How it works

The module provides a cron job and a shell command to execute the cleanup jobs. The set of bundled
handlers can be easily extended by own handlers via di.xml.

### How to use

1. Install the module via Composer:
``` 
composer require phoenix-media/magento2-cleanup
```
2. Enable it
``` bin/magento module:enable Phoenix_Cleanup ```
3. Install the module and rebuild the DI cache
``` bin/magento setup:upgrade ```

### How to configure

Find the modules configuration in the PHOENIX MEDIA section of your Magento configuration.
