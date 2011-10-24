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
 * DbSync_Table
 *
 * @category DbSync
 * @package  DbSync_Table
 * @version  $Id$
 */
abstract class DbSync_Table_AbstractTable
{
    /**
     * @var DbSync_Table_DbAdapter_AdapterInterface
     */
    protected $_dbAdapter;

    /**
     * @var DbSync_Table_FileAdapter_AdapterInterface
     */
    protected $_fileAdapter;

    /**
     * @var string
     */
    protected $_tableName;

    /**
     * @var string
     */
    protected $_filename;

    /**
     * @var string
     */
    protected $_diff = 'diff';

    /**
     * @var string
     */
    protected $_exceptionClass = 'DbSync_Exception';

    /**
     * Constructor
     *
     * @param DbSync_Table_DbAdapter_AdapterInterface $db
     * @param DbSync_Table_FileAdapter_AdapterInterface $file
     * @param string $diffProg
     */
    public function __construct(
        DbSync_Table_DbAdapter_AdapterInterface $db,
        DbSync_Table_FileAdapter_AdapterInterface $file,
        $diffProg = null)
    {
        $this->_dbAdapter = $db;

        $this->_fileAdapter = $file;

        if ($diffProg) {
            $this->setDiffProg($diffProg);
        }
    }

    /**
     * Set diff programm
     *
     * @param string $diffProg
     * @return DbSync_Table_AbstractTable
     */
    public function setDiffProg($diffProg)
    {
        $this->_diff = (string) $diffProg;
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
            throw new $this->_exceptionClass('Table name not set');
        }
        return $this->_tableName;
    }

    /**
     * Set table name
     *
     * @param string $tableName
     * @return DbSync_Table_AbstractTable
     */
    public function setTableName($tableName)
    {
        $this->_tableName = (string) $tableName;

        return $this;
    }

    /**
     * Save config file
     *
     * @param string $filename
     */
    public function save($filename)
    {
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("path '{$filename}' is not writable");
        }

        $this->_fileAdapter->write($filename, $this->getDataToStore());
    }

    /**
     * Is directory writable
     *
     * @return boolean
     */
    public function isWriteable()
    {
        if (!$path = $this->getFilePath()) {
            $path = dirname($this->getFilePath(false));

            if (!realpath($path)) {
                @mkdir($path, 0777, true);
            }
        }

        return is_writable($path);
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
        return $this->_fileAdapter->getTableList($this->_filename);
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
            $this->_filename
        );

        if ($real) {
            return realpath($path);
        }
        return $path;
    }

    /**
     * Has file
     *
     * @return boolen
     */
    public function hasFile()
    {
        return (bool) $this->getFilePath();
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
            throw new $this->_exceptionClass("Config for '{$this->getTriggerName()}' not found");
        }

        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Config file '{$filename}' is not writable");
        }

        return @unlink($filename);
    }

    /**
     * Get status
     *
     * @return boolen
     */
    public function getStatus()
    {
        if (!$this->hasFile()) {
            throw new $this->_exceptionClass("Config for '{$this->getTableName()}' not found");
        }

        $diff = $this->diff();

        return empty($diff);
    }

    /**
     * Pull schema or data from db table to config file
     *
     */
    public function pull()
    {
        $this->init(true);
    }

    /**
     * Get diff
     *
     * @return array
     */
    public function diff()
    {
        $output = array();

        if (!$filename = $this->getFilePath()) {
            $output[] = "Config for '{$this->getTableName()}' not found";
        } else {
            $tmp = $filename . '.tmp';

            $this->save($tmp);

            if (sha1_file($filename) !== sha1_file($tmp)) {
                exec("{$this->_diff} {$filename} {$tmp}", $output);
            }
            unlink($tmp);
        }
        return $output;
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
        $path = $this->getFilePath(false);

        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Path '{$path}' is not writable");
        }

        if (!realpath($path) || $force) {
            $this->save($path);

            return true;
        }
        return false;
    }
}