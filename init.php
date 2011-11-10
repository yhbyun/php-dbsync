<?php
/**
 * DbSync
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/php-dbsync/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to maks.slesarenko@gmail.com so we can send you a copy immediately.
 *
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * Check environment
 *
 * @version $Id$
 */
if (version_compare(PHP_VERSION, '5.2.6', '<')) {
    echo "PHP 5.2.6 or newer is required", PHP_EOL;exit;
}

if (!extension_loaded('pdo_mysql')) {
    echo "PDO extension is required", PHP_EOL;exit;
}

/**
 * File adapter classes
 */
require_once 'dependencies/SymfonyComponents/YAML/sfYaml.php';
require_once 'DbSync/FileAdapter/AdapterInterface.php';
require_once 'DbSync/FileAdapter/SfYaml.php';

/**
 * Database adapter classes
 */
require_once 'DbSync/DbAdapter/AdapterInterface.php';
require_once 'DbSync/DbAdapter/Pdo/AbstractAdapter.php';
require_once 'DbSync/DbAdapter/Pdo/Mysql.php';

/**
 * App classes
 */
require_once 'DbSync/Exception.php';
require_once 'DbSync/Version.php';
require_once 'DbSync/Model/AbstractModel.php';
require_once 'DbSync/Model/Table/AbstractTable.php';
require_once 'DbSync/Console.php';
require_once 'DbSync/Controller/FrontController.php';
require_once 'DbSync/Controller/AbstractController.php';

/**
 * Schema sync classes
 */
require_once 'DbSync/Model/Table/Schema.php';
require_once 'DbSync/Controller/SchemaController.php';

/**
 * Data sync classes
 */
require_once 'DbSync/Model/Table/Data.php';
require_once 'DbSync/Controller/DataController.php';

/**
 * Trigger sync classes
 */
require_once 'DbSync/Model/Table/Trigger.php';
require_once 'DbSync/Controller/TriggerController.php';