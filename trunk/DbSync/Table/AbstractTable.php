<?php
/**
 * DbSync_Table
 *
 * @version $Id$
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
    protected $_exceptionClass = 'Exception';

    /**
     * Constructor
     *
     * @param DbSync_Table_DbAdapter_AdapterInterface $db
     * @param DbSync_Table_FileAdapter_AdapterInterface $file
     * @param string $tableName
     * @param string $diffProg
     */
    public function __construct(
        DbSync_Table_DbAdapter_AdapterInterface $db,
        DbSync_Table_FileAdapter_AdapterInterface $file,
        $tableName = null,
        $diffProg = null)
    {
        $this->_dbAdapter = $db;


        $this->_fileAdapter = $file;

        if ($tableName) {
            $this->setTableName($tableName);
        }
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
     * Get tables list
     *
     * @return array
     */
    public function getDbTableList()
    {
        return $this->_dbAdapter->getTableList();
    }

    /**
     * Get data tables list
     *
     * @return array
     */
    public function getFileTableList()
    {
        return $this->_fileAdapter->getTableList($this->_filename);
    }

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTableList()
    {
        $tables = array_merge($this->getDbTableList(), $this->getFileTableList());
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
        $path = $this->_fileAdapter->getFilePath($this->getTableName(), $this->_filename, true);

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

            if (file_get_contents($filename) !== file_get_contents($tmp)) {
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
            throw new $this->_exceptionClass("Filepath is not writable");
        }

        if (!realpath($path) || $force) {
            $this->save($path);

            return true;
        }
        return false;
    }
}