<?php
/**
 * DbSync_Table
 *
 * @version $Id$
 */
class DbSync_Table
{
    protected $_adapter;

    protected $_path;

    protected $_tableName;

    /**
     * Constructor
     *
     * @param DbSync_Table_Adapter_AdapterInterface $db
     * @param string $path
     * @param string $tableName
     */
    public function __construct(DbSync_Table_Adapter_AdapterInterface $adapter,
        $path, $tableName = null)
    {
        $this->_adapter = $adapter;

        $this->_path = $path;

        if ($tableName) {
            $this->setTableName($tableName);
        }
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * Set table name
     *
     * @return DbSync_Table
     */
    public function setTableName($tableName)
    {
        $this->_tableName = $tableName;

        return $this;
    }

    /**
     * Is directory writable
     *
     * @return boolean
     */
    public function isWriteable()
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        $path = $this->_path . '/' . $this->_tableName;
        if (!realpath($path)) {
            @mkdir($path, 0777, true);
        }

        return is_writable($path);
    }

    /**
     * Get tables list
     *
     * @return array
     */
    public function getDbTableList()
    {
        return $this->_adapter->getTableList();
    }

    /**
     * Is db table exists
     *
     * @return boolen
     */
    public function hasDbTable()
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        return $this->_adapter->hasDbTable($this->_tableName);
    }
}