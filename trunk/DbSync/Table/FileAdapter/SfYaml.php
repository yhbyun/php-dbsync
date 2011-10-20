<?php

class DbSync_Table_FileAdapter_SfYaml
    implements DbSync_Table_FileAdapter_AdapterInterface
{
    const FILE_EXTENSION = 'yml';

    protected $_path;

    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * Write data to file
     *
     * @param string $filename Full path
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
     * Get data tables list
     *
     * @return array
     */
    public function getTableList($filename)
    {
        $list = array();

        foreach (new GlobIterator("{$this->_path}/*/{$filename}." . self::FILE_EXTENSION) as $file) {
            $list[] = basename(dirname($file->getPathname()));
        }

        return $list;
    }

    /**
     * Get config filepath
     *
     * @param boolen $real
     * @throws Exception
     * @return string
     */
    public function getFilePath($tableName, $filename, $trigger = false)
    {
        $path = $this->_path . '/' . $tableName . '/';

        if ($trigger) {
            $path .= 'triggers/';
        }
        $path .= $filename . '.' . self::FILE_EXTENSION;

        return $path;
    }

    public function getTableNameByTriggerName($triggerName)
    {
        foreach (new GlobIterator("{$this->_path}/*/triggers/{$triggerName}." . self::FILE_EXTENSION) as $file) {
            return basename(dirname(dirname($file->getPathname())));
        }
    }

    /**
     * Get data tables list
     *
     * @return array
     */
    public function getTriggerList()
    {
        $list = array();

        foreach (new GlobIterator("{$this->_path}/*/triggers/*." . self::FILE_EXTENSION) as $file) {
            $list[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        }
        return $list;
    }
}