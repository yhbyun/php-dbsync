<?php
/**
 * DbSync_Table_Data
 *
 * @version $Id$
 */
class DbSync_Table_Data extends DbSync_Table
{
    /**
     * Fetch all data from table
     *
     * @return Zend_Config
     */
    public function fetchData()
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        return new Zend_Config($this->_adapter->fetchData($this->_tableName));
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
        $config = $this->getData(true);

        if (!$config->current()) {
            throw new Exception("Data for '{$this->_tableName}' is empty");
        }

        if (!$this->hasDbTable()) {
            throw new Exception("Table '{$this->_tableName}' not found");
        }
        if ($this->isDirtyDb()) {
            if (!$force) {
                throw new Exception("Table '{$this->_tableName}' is dirty");
            }
            $this->_adapter->truncate($this->_tableName);
        }

        return $this->_adapter->insert($config->toArray(), $this->_tableName);
    }

    /**
     * Merge data to db table
     *
     * @throws Exception
     * @return boolean
     */
    public function merge()
    {
        $config = $this->getData(true);

        if (!$config->current()) {
            throw new Exception("Data for '{$this->_tableName}' is empty");
        }

        if (!$this->hasDbTable()) {
            throw new Exception("Table '{$this->_tableName}' not found");
        }

        return $this->_adapter->merge($config->toArray(), $this->_tableName);
    }

    /**
     * Get all data from config
     *
     * @param boolen $asConfig
     * @throws Exception
     * @return Zend_Config|string
     */
    public function getData($asConfig = false)
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        $path = $this->_path . '/' . $this->_tableName . '/data.yml';

        $schema = realpath($path);
        if ($asConfig) {
            if (!$schema) {
                throw new Exception("Data file for '{$this->_tableName}' not found in '{$this->_path}'");
            }
            $schema = new Zend_Config_Yaml($schema);
        }
        return $schema;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        $syncronised = false;

        if (!$scheme = $this->getData()) {
            echo "Data for table {$this->_tableName} not found", PHP_EOL;
        } else {
            $tmp = $scheme . '.tmp';
            $writer = new Zend_Config_Writer_Yaml();
            $writer->write($tmp, $this->fetchData());

            if (file_get_contents($scheme) === file_get_contents($tmp)) {
                $syncronised = true;
            }
            unlink($tmp);
        }
        return $syncronised;
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
        $path = $this->_path . '/' . $this->_tableName;

        $path .= '/data.yml';
        if (!realpath($path) || $force) {
            $writer = new Zend_Config_Writer_Yaml();
            $writer->write($path, $this->fetchData());

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

        if (!$scheme = $this->getData()) {
            $output[] = "Data for table {$this->_tableName} not found";
        } else {
            $tmp = $scheme . '.tmp';
            $writer = new Zend_Config_Writer_Yaml();
            $writer->write($tmp, $this->fetchData());

            if (file_get_contents($scheme) !== file_get_contents($tmp)) {
                exec("diff {$scheme} {$tmp}", $output);
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


    public function hasFileData()
    {
        return (bool) $this->getData();
    }

    /**
     * Is db table dirty
     *
     * @return boolean
     */
    public function isDirtyDb()
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        return $this->_adapter->isDirtyDbTable($this->_tableName);
    }

    /**
     * Get data tables list
     *
     * @return array
     */
    public function getDataTableList()
    {
        $list = array();

        foreach (new GlobIterator("{$this->_path}/*/data.yml") as $file) {
            $list[] = basename(dirname($file->getPathname()));
        }

        return $list;
    }

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTableList()
    {
        return $this->getDbTableList() + $this->getDataTableList();
    }
}