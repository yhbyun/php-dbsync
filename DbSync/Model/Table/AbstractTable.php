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
 * DbSync_Model_Table_AbstractTable
 *
 * @category   DbSync
 * @package    DbSync_Model
 * @subpackage Table
 * @version    $Id$
 */
abstract class DbSync_Model_Table_AbstractTable extends DbSync_Model_AbstractModel
{
    /**
     * @var string
     */
    protected $_tableName;

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        if (!$this->_tableName) {
            throw new $this->_exceptionClass('Table name not set');
        }
        return $this->_tableName;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getTableName();
    }

    /**
     * Set table name
     *
     * @param string $tableName
     * @return DbSync_Model_Table_AbstractTable
     */
    public function setTableName($tableName)
    {
        $this->_tableName = (string) $tableName;

        return $this;
    }

    /**
     * Get db tables list
     *
     * @return array
     */
    public function getListDb()
    {
        return $this->_dbAdapter->getTableList();
    }

    /**
     * Get config tables list
     *
     * @return array
     */
    public function getListConfig()
    {
        return $this->_fileAdapter->getTableList($this);
    }

    /**
     * Get list
     *
     * @return array
     */
    public function getList()
    {
        $tables = array_merge($this->getListDb(), $this->getListConfig());
        return array_unique($tables);
    }

    /**
     * Is db table exists
     *
     * @return boolen
     */
    public function hasDbTable()
    {
        return $this->_dbAdapter->hasTable($this->getTableName());
    }
}