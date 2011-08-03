<?php
/**
 * DbSync_Table_Data
 *
 * @version $Id$
 */
class DbSync_Table_Data extends DbSync_Table
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
            throw new Exception('Table name not set');
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
            throw new Exception("Data for table {$this->_tableName} not found");
        }
        $data = $this->load($filename);

        if (!current($data)) {
            throw new Exception("Data for '{$this->_tableName}' is empty");
        }

        if (!$this->hasDbTable()) {
            throw new Exception("Table '{$this->_tableName}' not found");
        }
        if (!$this->isEmptyTable()) {
            if (!$force) {
                throw new Exception("Table '{$this->_tableName}' is not empty");
            }
            $this->_adapter->truncate($this->_tableName);
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
            throw new Exception("Data for table {$this->_tableName} not found");
        }
        $data = $this->load($filename);

        if (!current($data)) {
            throw new Exception("Data for '{$this->_tableName}' is empty");
        }

        if (!$this->hasDbTable()) {
            throw new Exception("Table '{$this->_tableName}' not found");
        }

        return $this->_adapter->merge($data, $this->_tableName);
    }

    /**
     * Get status
     *
     * @return boolen
     */
    public function getStatus()
    {
        if (!$this->hasFile()) {
            throw new Exception("Data for table {$this->_tableName} not found");
        }
        return parent::getStatus();
    }

    /**
     * Init
     *
     * @param boolen $force
     * @return boolen
     * @throws Exception
     */
    public function init($force = false)
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        if (!$this->isWriteable()) {
            throw new Exception("Data dir is not writable");
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
            $output[] = "Data for table {$this->_tableName} not found";
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
     * Pull all data from db table
     *
     */
    public function pull()
    {
        $this->init(true);
    }

    /**
     * Is db table dirty
     *
     * @return boolean
     */
    public function isEmptyTable()
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        return $this->_adapter->isEmpty($this->_tableName);
    }
}