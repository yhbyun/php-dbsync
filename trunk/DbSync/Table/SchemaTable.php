<?php
/**
 * DbSync_Table_SchemaTable
 *
 * @version $Id$
 */
class DbSync_Table_SchemaTable extends DbSync_Table_AbstractTable
{
    /**
     * @var string
     */
    protected $_filename = 'schema.yml';

    /**
     * Save schema
     *
     * @param string $filename
     */
    public function save($filename)
    {
        if (!$this->getTableName()) {
            throw new $this->_exceptionClass('Table name not set');
        }
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Schema dir is not writable");
        }

        $this->write($filename, $this->_adapter->parseSchema($this->_tableName));
    }

    /**
     * Generate Alter Table
     *
     * @return string
     */
    public function createAlter()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Scheme for table {$this->_tableName} not found");
        }
        $data = $this->load($filename);

        return $this->_adapter->createAlter($data, $this->_tableName);
    }

    /**
     * Alter db table
     *
     * @return boolen
     */
    public function push()
    {
        return false !== $this->_adapter->execute($this->createAlter());
    }

    /**
     * Delete Table
     *
     * @throws Exception
     * @return boolen
     */
    public function deleteDbTable()
    {
        if (!$this->getTableName()) {
            throw new $this->_exceptionClass('Table name not set');
        }
        return $this->_adapter->delete($this->_tableName);
    }

    /**
     * Delete file
     *
     * @throws Exception
     * @return boolen
     */
    public function deleteFile()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Data for table {$this->_tableName} not found");
        }

        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Data file is not writable");
        }

        return @unlink($filename);
    }
}