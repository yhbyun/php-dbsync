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
 * @package    DbSync_DbAdapter
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_DbAdapter_AdapterInterface
 *
 * @category   DbSync
 * @package    DbSync_DbAdapter
 * @version    $Id$
 */
interface DbSync_DbAdapter_AdapterInterface
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
     * @param string $tableName
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
    public function createAlter(array $config, $tableName);

    /**
     * Fetch db triggers
     *
     * @param string $triggerName
     * @return string
     */
    public function parseTrigger($triggerName);

    /**
     * Generate trigger sql
     *
     * @param array $config
     * @return string
     */
    public function createTriggerSql(array $config);

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
     * @param array $tables
     * @return array
     */
    public function getTriggerList($tables = array());

    /**
     * Get table name by trigger name
     *
     * @param string $triggerName
     * @return string
     */
    public function getTableByTrigger($triggerName);

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTableList();

    /**
     * Is db table exists
     *
     * @param string $tableName
     * @return boolen
     */
    public function hasTable($tableName);

    /**
     * Is db trigger exists
     *
     * @param string $triggerName
     * @return boolen
     */
    public function hasTrigger($triggerName);

    /**
     * Fetch all data from table
     *
     * @param string $tableName
     * @return array
     */
    public function fetchData($tableName);

    /**
     * Push data to db table
     *
     * @param array  $data
     * @param string $tableName
     * @return boolen
     */
    public function insert(array $data, $tableName);

    /**
     * Merge data to db table
     *
     * @param array  $data
     * @param string $tableName
     * @return boolean
     */
    public function merge(array $data, $tableName);

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