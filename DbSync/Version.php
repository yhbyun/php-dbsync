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
 * @package  DbSync_Version
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Version
 *
 * @category DbSync
 * @package  DbSync_Version
 * @version  $Id$
 */
class DbSync_Version
{
    const VERSION = '1.11';

    /**
     * Get credits
     *
     * @return string
     */
    public static function getCredits()
    {
        return "Database Synchronization Toolkit " . self::VERSION . " by SM";
    }
}