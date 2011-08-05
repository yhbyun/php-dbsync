<?php
/**
 * DbSync_Table_Schema
 *
 * @version $Id$
 */
class DbSync_Table_Schema extends DbSync_Table
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
            throw new Exception('Table name not set');
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
            throw new Exception("Scheme for table {$this->_tableName} not found");
        }
        $data = $this->load($filename);

        return $this->_adapter->createAlter($data, $this->_tableName);
    }

    /**
     * Alter db table
     *
     * @return integer
     */
    public function push()
    {
        return $this->_adapter->execute($this->createAlter());
    }

    /**
     * Get status
     *
     * @return boolen
     */
    public function getStatus()
    {
        if (!$this->hasFile()) {
            throw new Exception("Scheme for table {$this->_tableName} not found");
        }
        return parent::getStatus();
    }

    /**
     * Init
     *
     * @param boolen $force
     * @throws Exception
     * @return boolean
     */
    public function init($force = false)
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        if (!$this->isWriteable()) {
            throw new Exception("Schema dir is not writable");
        }
        $path = $this->_path . '/' . $this->_tableName . '/' . $this->_filename;

        if (!realpath($path) || $force) {
            $this->save($path);

            return true;
        }
        return false;
    }

    /**
     * Get diff
     *
     * @return array
     */
    public function diff()
    {
        $output = array();

        if (!$filename = $this->getFilePath()) {
            $output[] = "scheme for table {$this->_tableName} not found";
        } else {
            $tmp = $filename . '.tmp';

            $this->save($tmp);

            if (file_get_contents($filename) !== file_get_contents($tmp)) {
                exec("diff {$filename} {$tmp}", $output);
            }
            unlink($tmp);
        }
        return $output;
    }

    /**
     * Pull schema from db table to file
     *
     * @return boolen
     */
    public function pull()
    {
        return $this->init(true);
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
            throw new Exception('Table name not set');
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
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        if (!$this->hasFile()) {
            throw new Exception("Data for table {$this->_tableName} not found");
        }
        if (!$this->isWriteable()) {
            throw new Exception("Data dir is not writable");
        }
        $filename = $this->getFilePath();

        return @unlink($filename);
    }
}