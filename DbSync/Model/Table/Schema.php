<?php
/**
 * DbSync
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/php-dbsync/wiki/License
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
 * DbSync_Model_Table_Schema
 *
 * @category   DbSync
 * @package    DbSync_Model
 * @subpackage Table
 * @version    $Id$
 */
class DbSync_Model_Table_Schema extends DbSync_Model_Table_AbstractTable
{
    /**
     * Get data to store in config file
     *
     * @return array
     */
    public function generateConfigData()
    {
        return $this->_dbAdapter->parseSchema($this->getTableName());
    }

    /**
     * Generate Alter Table
     *
     * @return string
     * @throws DbSync_Exception
     */
    public function generateSql()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Config for '{$this->getTableName()}' not found");
        }
        $data = $this->_fileAdapter->load($filename);

        return $this->_dbAdapter->createAlter($data, $this->getTableName());
    }

    /**
     * Delete Table
     *
     * @return boolen
     */
    public function dropDbTable()
    {
        return $this->_dbAdapter->dropTable($this->getTableName());
    }
}