<?php
/**
 * DbSync
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/phplizard/wiki/License
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