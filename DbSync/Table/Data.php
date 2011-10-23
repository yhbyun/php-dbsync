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
    /**
     * @var string
     */
    protected $_filename = 'data';

    /**
     * Fetch all data from table
     *
     * @param string $filename
     */
    public function save($filename)
    {
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Data dir is not writable");
        }

        $this->_fileAdapter->write($filename, $this->_dbAdapter->fetchData($this->getTableName()));
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
            throw new $this->_exceptionClass("Data for {$this->getTableName()} not found");
        }

        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->getTableName()}' not found");
        }

        if (!$this->isEmptyTable()) {
            if (!$force) {
                throw new $this->_exceptionClass("Table '{$this->getTableName()}' is not empty");
            }
            $this->_dbAdapter->truncate($this->getTableName());
        }

        $data = $this->_fileAdapter->load($filename);

        if (!current($data)) {
            return false;
        }
        return $this->_dbAdapter->insert($data, $this->getTableName());
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
            throw new $this->_exceptionClass("Data for table {$this->getTableName()} not found");
        }
        $data = $this->_fileAdapter->load($filename);

        if (!current($data)) {
            throw new $this->_exceptionClass("Data for '{$this->getTableName()}' is empty");
        }

        if (!$this->hasDbTable()) {
            throw new $this->_exceptionClass("Table '{$this->getTableName()}' not found");
        }

        return $this->_dbAdapter->merge($data, $this->getTableName());
    }

    /**
     * Is db table dirty
     *
     * @return boolean
     */
    public function isEmptyTable()
    {
        return $this->_dbAdapter->isEmpty($this->getTableName());
    }
}