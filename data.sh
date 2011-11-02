#!/usr/bin/php
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

require_once 'config.php';

require_once 'DbSync/Model/Table/Data.php';
require_once 'DbSync/Controller/DataController.php';

$console = new DbSync_Console();
$console->parse();

$controller = new DbSync_Controller_DataController($config);
$controller->dispatch($console);