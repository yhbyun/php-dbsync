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
    protected $_filename = 'data';

    /**
     * Fetch all data from table
     *
     * @param string $filename
     */
    public function save($filename)
    {
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Data dir is not writable");
        }

        $this->_fileAdapter->write($filename, $this->_dbAdapter->fetchData($this->getTableName()));
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
            throw new $this->_exceptionClass("Data for {$this->getTableName()} not found");
        }

        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->getTableName()}' not found");
        }

        if (!$this->isEmptyTable()) {
            if (!$force) {
                throw new $this->_exceptionClass("Table '{$this->getTableName()}' is not empty");
            }
            $this->_dbAdapter->truncate($this->getTableName());
        }

        $data = $this->_fileAdapter->load($filename);

        if (!current($data)) {
            return false;
        }
        return $this->_dbAdapter->insert($data, $this->getTableName());
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
            throw new $this->_exceptionClass("Data for table {$this->getTableName()} not found");
        }
        $data = $this->_fileAdapter->load($filename);

        if (!current($data)) {
            throw new $this->_exceptionClass("Data for '{$this->getTableName()}' is empty");
        }

        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->getTableName()}' not found");
        }

        return $this->_dbAdapter->merge($data, $this->getTableName());
    }

    /**
     * Is db table dirty
     *
     * @return boolean
     */
    public function isEmptyTable()
    {
        return $this->_dbAdapter->isEmpty($this->getTableName());
    }
}