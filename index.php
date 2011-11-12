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

defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(__FILE__));

defined('REAL_PATH') || define('REAL_PATH', realpath('.'));

require_once APPLICATION_PATH . '/init.php';

$console = new DbSync_Console();

$front = new DbSync_Controller_FrontController();

$front->dispatch($console->parse());

__HALT_COMPILER();