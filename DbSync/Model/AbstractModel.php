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
 * @package  DbSync_Model
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Model
 *
 * @category DbSync
 * @package  DbSync_Model
 * @version  $Id$
 */
abstract class DbSync_Model_AbstractModel
{
    /**
     * @var DbSync_DbAdapter_AdapterInterface
     */
    protected $_dbAdapter;

    /**
     * @var DbSync_FileAdapter_AdapterInterface
     */
    protected $_fileAdapter;

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
     * @param DbSync_DbAdapter_AdapterInterface $db
     * @param DbSync_FileAdapter_AdapterInterface $file
     * @param string $diffProg
     */
    public function __construct(
        DbSync_DbAdapter_AdapterInterface $db,
        DbSync_FileAdapter_AdapterInterface $file,
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
     * @return DbSync_Model_AbstractModel
     */
    public function setDiffProg($diffProg)
    {
        $this->_diff = (string) $diffProg;
        return $this;
    }

    /**
     * Get item name
     *
     * @return string
     */
    abstract function getName();

    /**
     * Save config file
     *
     * @param string $filename
     * @throws DbSync_Exception
     */
    public function save($filename)
    {
        if (!$this->isWriteable()) {
            throw new $this->_exceptionClass("Path '{$filename}' is not writable");
        }

        $this->_fileAdapter->write($filename, $this->generateConfigData());
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
     * Get config filepath
     *
     * @param boolen $real
     * @return string
     */
    public function getFilePath($real = true)
    {
        $path = $this->_fileAdapter->getFilePath($this);

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
     * @throws DbSync_Exception
     * @return boolen
     */
    public function deleteFile()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Config file not found");
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
     * Alter db table
     *
     * @return boolen
     */
    public function push()
    {
        return false !== $this->_dbAdapter->execute($this->generateSql());
    }

    /**
     * Get diff
     *
     * @return array
     * @throws DbSync_Exception
     */
    public function diff()
    {
        if (!$filename = $this->getFilePath()) {
            throw new $this->_exceptionClass("Config file not found");
        }

        $output = array();
        $tmp = $filename . '.tmp';

        $this->save($tmp);

        if (sha1_file($filename) !== sha1_file($tmp)) {
            $output[] = $filename;
            exec("{$this->_diff} {$filename} {$tmp}", $output);
        }
        unlink($tmp);

        return $output;
    }

    /**
     * Init
     *
     * @param boolen $force
     * @throws DbSync_Exception
     * @return boolean
     */
    public function init($force = false)
    {
        if ($force || !$this->getFilePath()) {
            $path = $this->getFilePath(false);

            if (!$this->isWriteable()) {
                throw new $this->_exceptionClass("Path '{$path}' is not writable");
            }
            $this->save($path);

            return true;
        }
        return false;
    }
}