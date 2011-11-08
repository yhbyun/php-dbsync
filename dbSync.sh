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
 * @version  $Id: schema.sh 44 2011-11-02 00:38:43Z maks.slesarenko@gmail.com $
 */

require_once 'config.php';

$console = new DbSync_Console();
$console->parse();

$front = new DbSync_Controller_FrontController($config);

$front->dispatch($console);