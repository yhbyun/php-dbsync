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
 * @category DbSync
 * @package  Tests
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id: AbstractTableTest.php 42 2011-10-24 20:09:38Z maks.slesarenko@gmail.com $
 */

/**
 * DbSync_Table_DbAdapter_PDO
 *
 * @group    table
 * @category DbSync
 * @package  Tests
 * @version  $Id: AbstractTableTest.php 42 2011-10-24 20:09:38Z maks.slesarenko@gmail.com $
 */
class DbSync_Table_DbAdapter_PDO extends PDO
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }
}

