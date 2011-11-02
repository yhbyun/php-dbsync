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
 * @category   DbSync
 * @package    DbSync_Model
 * @subpackage Table
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_Model_Table_Trigger
 *
 * @category   DbSync
 * @package    DbSync_Model
 * @subpackage Table
 * @version    $Id$
 */
class DbSync_Model_Table_Trigger extends DbSync_Model_Table_AbstractTable
{
    /**
     * @var string
     */
    protected $_triggerName;

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
     * @return DbSync_Model_Table_Trigger
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
            $trigger = $this->getTriggerName();
            $this->_tableName = $this->_dbAdapter->getTableByTrigger($trigger);

            if (!$this->_tableName){
                $this->_tableName = $this->_fileAdapter->getTableByTrigger($trigger);
            }
        }
        return parent::getTableName();
    }

    /**
     * Get data to store in config file
     *
     * @return array
     */
    public function generateConfigData()
    {
        return $this->_dbAdapter->parseTrigger($this->getTriggerName());
    }

    /**
     * Generate Sql code
     *
     * @return string
     */
    public function generateSql()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Config for '{$this->getTriggerName()}' not found");
        }

        $config = $this->_fileAdapter->load($filename);

        return $this->_dbAdapter->createTriggerSql($config);
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