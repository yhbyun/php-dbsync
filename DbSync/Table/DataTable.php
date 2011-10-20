<?php
/**
 * DbSync_Table_DataTable
 *
 * @version $Id$
 */
class DbSync_Table_DataTable extends DbSync_Table_AbstractTable
{
    /**
     * @var string
     */
    protected $_filename = 'data.yml';

    /**
     * Fetch all data from table
     *
     * @param string $filename
     */
    public function save($filename)
    {
        if (!$this->getTableName()) {
            throw new $this->_exceptionClass('Table name not set');
        }
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Data dir is not writable");
        }

        $this->write($filename, $this->_adapter->fetchData($this->_tableName));
    }

    /**
     * Push data to db table
     *
     * @param boolen $force
     * @return boolen
     * @throws Exception
     */
    public function push($force = false)
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Data for table {$this->_tableName} not found");
        }

        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->_tableName}' not found");
        }

        if (!$this->isEmptyTable()) {
            if (!$force) {
                throw new $this->_exceptionClass("Table '{$this->_tableName}' is not empty");
            }
            $this->_adapter->truncate($this->_tableName);
        }

        $data = $this->load($filename);

        if (!current($data)) {
            return false;
        }
        return $this->_adapter->insert($data, $this->_tableName);
    }

    /**
     * Merge data to db table
     *
     * @throws Exception
     * @return boolean
     */
    public function merge()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Data for table {$this->_tableName} not found");
        }
        $data = $this->load($filename);

        if (!current($data)) {
            throw new $this->_exceptionClass("Data for '{$this->_tableName}' is empty");
        }

        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->_tableName}' not found");
        }

        return $this->_adapter->merge($data, $this->_tableName);
    }

    /**
     * Is db table dirty
     *
     * @return boolean
     */
    public function isEmptyTable()
    {
        if (!$this->getTableName()) {
            throw new $this->_exceptionClass('Table name not set');
        }
        return $this->_adapter->isEmpty($this->_tableName);
    }
}