<?php
/**
 * DbSync_Table
 *
 * @version $Id$
 */
abstract class DbSync_Table_AbstractTable
{
    protected $_adapter;

    protected $_path;

    protected $_tableName;

    protected $_filename;

    protected $_diff = 'diff';

    protected $_exceptionClass = 'Exception';

    /**
     * Constructor
     *
     * @param DbSync_Table_DbAdapter_AdapterInterface $db
     * @param string $path
     * @param string $tableName
     * @param string $diffProg
     */
    public function __construct(DbSync_Table_DbAdapter_AdapterInterface $adapter,
        $path, $tableName = null, $diffProg = null)
    {
        $this->_adapter = $adapter;

        $this->_path = $path;

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
        return $this->_adapter->getTableList();
    }

    /**
     * Get data tables list
     *
     * @return array
     */
    public function getFileTableList()
    {
        $list = array();

        foreach (new GlobIterator("{$this->_path}/*/{$this->_filename}") as $file) {
            $list[] = basename(dirname($file->getPathname()));
        }

        return $list;
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
        if (!$this->getTableName()) {
            throw new $this->_exceptionClass('Table name not set');
        }
        return $this->_adapter->hasTable($this->_tableName);
    }

    /**
     * Write data to file
     *
     * @param string $filename
     * @param array $data
     * @return int The function returns the number of bytes that were written to the file, or
     * false on failure.
     */
    public function write($filename, array $data)
    {
        $yaml = sfYaml::dump($data, 100);
        return file_put_contents($filename, $yaml);
    }

    /**
     * Load data from file
     *
     * @param string $filename
     * @return array
     */
    public function load($filename)
    {
        return sfYaml::load($filename);
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
        if (!$this->getTableName()) {
            throw new $this->_exceptionClass('Table name not set');
        }
        $path = $this->_path . '/' . $this->_tableName . '/' . $this->_filename;

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
            throw new $this->_exceptionClass("Config for table '{$this->_tableName}' not found");
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
            $output[] = "Config for '{$this->_tableName}' not found";
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