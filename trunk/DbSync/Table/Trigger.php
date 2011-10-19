<?php
/**
 * DbSync_Table_TableTrigger
 *
 * @version $Id$
 */
class DbSync_Table_Trigger extends DbSync_Table_AbstractTable
{
    protected $_adapter;

    protected $_path;

    protected $_tableName;

    protected $_triggerName;

    protected $_exceptionClass = 'Exception';

    /**
     * Constructor
     *
     * @param DbSync_Table_DbAdapter_AdapterInterface $db
     * @param string $path
     * @param string $tableName
     * @param string $triggerName
     */
    public function __construct(DbSync_Table_DbAdapter_AdapterInterface $adapter,
    $path, $tableName = null, $triggerName = null)
    {
        $this->_adapter = $adapter;

        $this->_path = $path;

        if ($tableName) {
            $this->setTableName($tableName);
        }
    }

    /**
     * Get trigger name
     *
     * @return string
     */
    public function getTriggerName()
    {
        return $this->_triggerName;
    }

    /**
     * Set trigger name
     *
     * @param string $triggerName
     * @return DbSync_Table
     */
    public function setTriggerName($triggerName)
    {
        $this->_triggerName = (string) $triggerName;

        return $this;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        if (!$this->_tableName) {
            $trigger = $this->_adapter->getTriggerInfo($this->getTriggerName());
            if (isset($trigger->Table)) {
                $this->_tableName = $trigger->Table;
            } else {
                foreach (new GlobIterator("{$this->_path}/*/triggers/{$this->_triggerName}.sql") as $file) {
                    $this->_tableName = basename(dirname(dirname($file->getPathname())));
                    break;
                }
            }
        }
        return $this->_tableName;
    }

    /**
     * Get config filepath
     *
     * @param boolen $real
     * @throws Exception
     * @return string
     */
    public function getFilePath($real = true)
    {
        if (!$this->getTableName()) {
            throw new $this->_exceptionClass('Table name not set');
        }
        $path = $this->_path . '/' . $this->_tableName . '/triggers/'
              . $this->_triggerName . '.sql';

        if ($real) {
            return realpath($path);
        }
        return $path;
    }

    /**
     * Write data to file
     *
     * @param string $filename
     * @param array $data
     * @return int The function returns the number of bytes that were written to the file, or
     * false on failure.
     */
    public function write($filename, $data)
    {
        return file_put_contents($filename, $data);
    }

    /**
     * Load data from file
     *
     * @param string $filename
     * @return array
     */
    public function load($filename)
    {
        $file = file_get_contents($filename);
        return $file;
    }

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
            throw new $this->_exceptionClass("Triggers dir is not writable");
        }

        $this->write($filename, $this->_adapter->fetchTrigger($this->getTriggerName()));
    }

    /**
     * Generate Sql code
     *
     * @return string
     */
    public function createSql()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Scheme for table {$this->_tableName} not found");
        }
        return $this->load($filename);
    }

    /**
     * Alter db table
     *
     * @return boolen
     */
    public function push()
    {
        return false !== $this->_adapter->execute($this->createSql());
    }

    /**
     * Delete Table
     *
     * @throws Exception
     * @return boolen
     */
    public function dropTrigger()
    {
        if (!$this->getTriggerName()) {
            throw new $this->_exceptionClass('Trigger name not set');
        }
        return $this->_adapter->dropTrigger($this->_triggerName);
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


    /**
     * Get tables list
     *
     * @return array
     */
    public function getDbTriggerList()
    {
        return $this->_adapter->getTriggerList();
    }

    /**
     * Get data tables list
     *
     * @return array
     */
    public function getFileTriggerList()
    {
        $list = array();

        foreach (new GlobIterator("{$this->_path}/*/triggers/*") as $file) {
            $list[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        }
        return $list;
    }

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTriggerList()
    {
        $triggers = array_merge($this->getDbTriggerList(), $this->getFileTriggerList());
        return array_unique($triggers);
    }

    /**
     * Is db table exists
     *
     * @return boolen
     */
    public function hasDbTrigger()
    {
        if (!$this->getTriggerName()) {
            throw new $this->_exceptionClass('Trigger name not set');
        }
        return $this->_adapter->hasTable($this->_tableName);
    }
}