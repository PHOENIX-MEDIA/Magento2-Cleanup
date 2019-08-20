## Phoenix Media Cleanup
The module archives and removes old entities. It supports log files, product images and quotes.

### What it does

For files it basically replaces logrotate. It archives and compresses log files and cleans
up the archive folder after a configurable period.
For product images it finds unreferenced files in media/catalog/product and moves them to a
recycle bin which gets purged after a configurable period.
For quotes it simply removes customer and guest quotes after a configurable period.

### How it works

The module provides a cron job and a shell command to execute the cleanup jobs. Those jobs are
implemented as "handlers" which clean up a specific entity. The set of bundled
handlers can be easily extended by custom handlers via di.xml. A resolver will then expose
configured handlers to the cron job and the shell command which then can execute the 
cleanup without additional configuration.

New handlers need to implement the HandlerInterface and be registerdto the handlerPool
using di.xml. 

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
