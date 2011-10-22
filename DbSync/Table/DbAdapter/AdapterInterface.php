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
 * @category   DbSync
 * @package    DbSync_Table
 * @subpackage DbAdapter
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_Table_DbAdapter_AdapterInterface
 *
 * @category   DbSync
 * @package    DbSync_Table
 * @subpackage DbAdapter
 * @version    $Id$
 */
interface DbSync_Table_DbAdapter_AdapterInterface
{
    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * Parse schema
     *
     * @return array
     */
    public function parseSchema($tableName);

    /**
     * Generate Alter Table
     *
     * @param array  $config
     * @param string $tableName
     * @return string
     */
    public function createAlter($config, $tableName);

    /**
     * Fetch db triggers
     *
     * @return string
     */
    public function parseTrigger($triggerName);

    /**
     * Generate trigger sql
     *
     * @param array $config
     * @return string
     */
    public function createTriggerSql($config);

    /**
     * Execute sql query
     *
     * @param string $sql
     * @return integer
     */
    public function execute($sql);

    /**
     * Get triggers list
     *
     * @return array
     */
    public function getTriggerList();

    /**
     * Get trigger info
     *
     * @return string
     */
    public function getTriggerInfo($triggerName);

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTableList();

    /**
     * Is db table exists
     *
     * @return boolen
     */
    public function hasTable($tableName);

    /**
     * Is db trigger exists
     *
     * @return boolen
     */
    public function hasTrigger($triggerName);

    /**
     * Fetch all data from table
     *
     * @return array
     */
    public function fetchData($tableName);

    /**
     * Push data to db table
     *
     * @param boolen $force
     * @return boolen
     * @throws Exception
     */
    public function insert($data, $tableName);

    /**
     * Merge data to db table
     *
     * @throws Exception
     * @return boolean
     */
    public function merge($data, $tableName);

    /**
     * Truncate table
     *
     * @param string $tableName
     * @return number
     */
    public function truncate($tableName);

    /**
     * Drop table
     *
     * @param string $tableName
     * @return number
     */
    public function dropTable($tableName);

    /**
     * Drop trigger
     *
     * @param string $triggerName
     * @return number
     */
    public function dropTrigger($triggerName);

    /**
     * Is db table empty
     *
     * @param string $tableName
     * @return boolean
     */
    public function isEmpty($tableName);
}