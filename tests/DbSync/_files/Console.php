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
 * @category DbSync
 * @package  Tests
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id: Console.php 36 2011-10-23 15:15:19Z maks.slesarenko@gmail.com $
 */

/**
 * Stub_Console
 *
 * @category DbSync
 * @package  Tests
 * @version  $Id$
 */
class Stub_Console extends DbSync_Console
{
    protected $_stdin;

    /**
     * Set stub stdin
     *
     * @param resource $stdin
     */
    public function setStdin($std)
    {
        $this->_stdin = $std;
    }
}

