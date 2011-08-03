<?php
/**
 * DbSync_Table_Adapter_AdapterInterface
 *
 * @version $Id$
 */
interface DbSync_Table_Adapter_AdapterInterface
{
    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * Is db table empty
     *
     * @param string $tableName
     * @return boolen
     */
    public function isEmpty($tableName);

    /**
     * Truncate table
     *
     * @param string $tableName
     * @return number
     */
    public function truncate($tableName);

    /**
     * Merge data to db table
     *
     * @throws Exception
     * @return boolean
     */
    public function merge($data, $tableName);

    /**
     * Push data to db table
     *
     * @param boolen $force
     * @return boolen
     * @throws Exception
     */
    public function insert($data, $tableName);

    /**
     * Is table exists
     *
     * @return boolen
     */
    public function hasTable($tableName);

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTableList();

    /**
     * Parse schema
     *
     * @return array
     */
    public function parseSchema($tableName);

    /**
     * Fetch all data from table
     *
     * @return array
     */
    public function fetchData($tableName);

    /**
     * Execute sql query
     *
     * @param string $sql
     * @return integer
     */
    public function execute($sql);

    /**
     * Generate Alter Table
     *
     * @param array  $config
     * @param string $tableName
     * @return string
     */
    public function createAlter($config, $tableName);
}