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
 * DbSync_Model_Table_Data
 *
 * @category   DbSync
 * @package    DbSync_Model
 * @subpackage Table
 * @version    $Id$
 */
class DbSync_Model_Table_Data extends DbSync_Model_Table_AbstractTable
{
    const PUSH_TYPE_FORCE = 1;
    const PUSH_TYPE_MERGE = 2;

    /**
     * Get data to store in config file
     *
     * @return array
     */
    public function generateConfigData()
    {
        return $this->_dbAdapter->fetchData($this->getTableName());
    }

    /**
     * Push data to db table
     *
     * @param boolen $force false
     * @param boolen $merge false
     * @return boolen
     * @throws DbSync_Exception
     */
    public function push($type = null)
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Config for '{$this->getTableName()}' not found");
        }

        $data = $this->_fileAdapter->load($filename);

        if (!current($data)) {
            return false;
        }

        if (!$this->isEmptyTable()) {
            if (self::PUSH_TYPE_MERGE == $type) {
                return $this->_dbAdapter->merge($data, $this->getTableName());
            }

            if (self::PUSH_TYPE_FORCE != $type) {
                throw new $this->_exceptionClass("Table '{$this->getTableName()}' is not empty");
            }
            $this->_dbAdapter->truncate($this->getTableName());
        }

        return $this->_dbAdapter->insert($data, $this->getTableName());
    }

    /**
     * Is db table dirty
     *
     * @return boolean
     * @throws DbSync_Exception
     */
    public function isEmptyTable()
    {
        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->getTableName()}' not found");
        }

        return $this->_dbAdapter->isEmpty($this->getTableName());
    }
}