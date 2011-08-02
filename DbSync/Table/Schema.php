<?php
/**
 * DbSync_Table_Schema
 *
 * @version $Id$
 */
class DbSync_Table_Schema extends DbSync_Table
{
    /**
     * Generate schema
     *
     * @return Zend_Config
     */
    public function generateSchema()
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        return new Zend_Config($this->_adapter->generateSchema($this->_tableName));
    }

    /**
     * Generate Alter Table
     *
     * @return string
     */
    public function generateAlter()
    {
        if (!$schema = $this->getSchema()) {
            throw new Exception("Scheme for table {$this->_tableName} not found");
        }
        $config = new Zend_Config_Yaml($schema);

        return $this->_adapter->generateAlter($config->toArray(), $this->_tableName);
    }

    /**
     * Alter db table
     *
     * @return integer
     */
    public function push()
    {
        return $this->_adapter->execute($this->generateAlter());
    }

    /**
     * Get schema
     *
     * @throws Exception
     * @return Zend_Config|null
     */
    public function getSchema()
    {
        if (!$this->getTableName()) {
            throw new Exception('Table name not set');
        }
        $path = $this->_path . '/' . $this->_tableName . '/schema.yml';

        return realpath($path);
    }

    /**
     * Get status
     *
     * @return boolen
     */
    public function getStatus()
    {
        $syncronised = false;

        if (!$scheme = $this->getSchema()) {
            throw new Exception("Scheme for table {$this->_tableName} not found");
        } else {
            $tmp = $scheme . '.tmp';
            $writer = new Zend_Config_Writer_Yaml();
            $writer->write($tmp, $this->generateSchema());

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
        $path = $this->_path . '/' . $this->_tableName;

        $path .= '/schema.yml';
        if (!realpath($path) || $force) {
            $writer = new Zend_Config_Writer_Yaml();
            $writer->write($path, $this->generateSchema());

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

        if (!$scheme = $this->getSchema()) {
            $output[] = "scheme for table {$this->_tableName} not found";
        } else {
            $tmp = $scheme . '.tmp';

            $writer = new Zend_Config_Writer_Yaml();
            $writer->write($tmp, $this->generateSchema());

            if (file_get_contents($scheme) !== file_get_contents($tmp)) {
                exec("diff {$scheme} {$tmp}", $output);
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
     * Has file schema
     *
     * @return boolen
     */
    public function hasFileSchema()
    {
        return (bool) $this->getSchema();
    }

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTableList()
    {
        return $this->getDbTableList() + $this->getSchemaTableList();
    }

    /**
     * Get schema tables list
     *
     * @return array
     */
    public function getSchemaTableList()
    {
        $list = array();

        foreach (new GlobIterator("{$this->_path}/*/schema.yml") as $file) {
            $list[] = basename(dirname($file->getPathname()));
        }

        return $list;
    }
}