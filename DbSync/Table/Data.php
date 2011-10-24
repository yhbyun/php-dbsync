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
 * DbSync_Table_Data
 *
 * @category DbSync
 * @package  DbSync_Table
 * @version  $Id$
 */
class DbSync_Table_Data extends DbSync_Table_AbstractTable
{
    const PUSH_TYPE_FORCE = 1;
    const PUSH_TYPE_MERGE = 2;

    /**
     * @var string
     */
    protected $_filename = 'data';

    /**
     * Get data to store in config file
     *
     * @return array
     */
    public function getDataToStore()
    {
        return $this->_dbAdapter->fetchData($this->getTableName());
    }

    /**
     * Push data to db table
     *
     * @param boolen $force false
     * @param boolen $merge false
     * @return boolen
     * @throws Exception
     */
    public function push($type = null)
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Config for '{$this->getTableName()}' not found");
        }

        if (!$this->isEmptyTable() && self::PUSH_TYPE_MERGE != $type) {
            if (self::PUSH_TYPE_FORCE != $type) {
                throw new $this->_exceptionClass("Table '{$this->getTableName()}' is not empty");
            }
            $this->_dbAdapter->truncate($this->getTableName());
        }

        $data = $this->_fileAdapter->load($filename);

        if (!current($data)) {
            return false;
        }
        if (self::PUSH_TYPE_MERGE == $type && !$this->isEmptyTable()) {
            return $this->_dbAdapter->merge($data, $this->getTableName());
        }
        return $this->_dbAdapter->insert($data, $this->getTableName());
    }

    /**
     * Is db table dirty
     *
     * @return boolean
     */
    public function isEmptyTable()
    {
        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->getTableName()}' not found");
        }

        return $this->_dbAdapter->isEmpty($this->getTableName());
    }
}