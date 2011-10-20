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
    protected $_filename = 'schema';

    /**
     * Save schema
     *
     * @param string $filename
     */
    public function save($filename)
    {
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Schema dir is not writable");
        }

        $this->_fileAdapter->write($filename, $this->_dbAdapter->parseSchema($this->getTableName()));
    }

    /**
     * Generate Alter Table
     *
     * @return string
     */
    public function createAlter()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Scheme for table {$this->getTableName()} not found");
        }
        $data = $this->_fileAdapter->load($filename);

        return $this->_dbAdapter->createAlter($data, $this->getTableName());
    }

    /**
     * Alter db table
     *
     * @return boolen
     */
    public function push()
    {
        return false !== $this->_dbAdapter->execute($this->createAlter());
    }

    /**
     * Delete Table
     *
     * @throws Exception
     * @return boolen
     */
    public function dropDbTable()
    {
        return $this->_dbAdapter->dropTable($this->getTableName());
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
            throw new $this->_exceptionClass("Data for table {$this->getTableName()} not found");
        }

        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Data file is not writable");
        }

        return @unlink($filename);
    }
}