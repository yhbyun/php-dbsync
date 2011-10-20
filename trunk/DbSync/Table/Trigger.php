<?php
/**
 * DbSync_Table_TableTrigger
 *
 * @version $Id$
 */
class DbSync_Table_Trigger extends DbSync_Table_AbstractTable
{
    protected $_triggerName;

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
        parent::__construct($adapter, $path, $tableName);

        if ($triggerName) {
            $this->setTriggerName($triggerName);
        }
    }

    /**
     * Get trigger name
     *
     * @return string
     */
    public function getTriggerName()
    {
        if (!$this->_triggerName) {
            throw new $this->_exceptionClass('Trigger name not set');
        }
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
            $trigger = $this->_dbAdapter->getTriggerInfo($this->getTriggerName());
            if (isset($trigger->Table)) {
                $this->_tableName = $trigger->Table;
            } else {
                $this->_tableName = $this->_fileAdapter->getTableNameByTriggerName($this->getTriggerName());
            }
        }
        return parent::getTableName();
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
        $path = $this->_fileAdapter->getFilePath(
            $this->getTableName(),
            $this->getTriggerName(),
            true
        );

        if ($real) {
            return realpath($path);
        }
        return $path;
    }

    /**
     * Save schema
     *
     * @param string $filename
     */
    public function save($filename)
    {
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Triggers dir is not writable");
        }

        $this->_fileAdapter->write($filename, $this->_dbAdapter->parseTrigger($this->getTriggerName()));
    }

    /**
     * Generate Sql code
     *
     * @return string
     */
    public function createSql()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Config for {$this->getTriggerName()} not found");
        }

        $config = $this->_fileAdapter->load($filename);

        return $this->_dbAdapter->createTriggerSql($config);
    }

    /**
     * Alter db table
     *
     * @return boolen
     */
    public function push()
    {
        return false !== $this->_dbAdapter->execute($this->createSql());
    }

    /**
     * Delete Table
     *
     * @throws Exception
     * @return boolen
     */
    public function dropTrigger()
    {
        return $this->_dbAdapter->dropTrigger($this->getTriggerName());
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
            throw new $this->_exceptionClass("Config for {$this->getTriggerName()} not found");
        }

        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Config file is not writable");
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
        return $this->_dbAdapter->getTriggerList();
    }

    /**
     * Get data tables list
     *
     * @return array
     */
    public function getFileTriggerList()
    {
        return $this->_fileAdapter->getTriggerList();
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
        return $this->_dbAdapter->hasTrigger($this->getTriggerName());
    }
}