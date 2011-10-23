<?php
/**
 * DbSync
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/phplizard/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to maks.slesarenko@gmail.com so we can send you a copy immediately.
 *
 * @category DbSync
 * @package  DbSync_Table
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Table_TableTrigger
 *
 * @category DbSync
 * @package  DbSync_Table
 * @version  $Id$
 */
class DbSync_Table_Trigger extends DbSync_Table_AbstractTable
{
    /**
     * @var string
     */
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

        $this->_tableName = null;

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
     * Get triggers list
     *
     * @param array $tables
     * @return array
     */
    public function getListDb($tables)
    {
        return $this->_dbAdapter->getTriggerList($tables);
    }

    /**
     * Get data tables list
     *
     * @param array $tables
     * @return array
     */
    public function getListConfig($tables)
    {
        return $this->_fileAdapter->getTriggerList($tables);
    }

    /**
     * Get db tables list
     *
     * @param array $tables
     * @return array
     */
    public function getList($tables)
    {
        $tables = array_merge($this->getListDb($tables), $this->getListConfig($tables));
        return array_unique($tables);
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