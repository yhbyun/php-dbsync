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
 * @package    DbSync_FileAdapter
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_FileAdapter_SfYaml
 *
 * @category   DbSync
 * @package    DbSync_FileAdapter
 * @version    $Id$
 */
class DbSync_FileAdapter_SfYaml implements DbSync_FileAdapter_AdapterInterface
{
    /**
     * @var string
     */
    const FILE_EXTENSION = 'yml';

    /**
     * @var string
     */
    protected $_path;

    /**
     * Contructor
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * Write data to file
     *
     * @param string $filename Full path
     * @param array $data
     * @return int The function returns the number of bytes that were written
     * to the file, or false on failure.
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
    public function getTableList(DbSync_Model_AbstractModel $model)
    {
        $list = array();

        switch (true) {
            case $model instanceof DbSync_Model_Table_Schema:
                $path = "schema";
                break;
            case $model instanceof DbSync_Model_Table_Data:
                $path = "data";
                break;
            case $model instanceof DbSync_Model_Table_Trigger:
                $path = "trigger";
                break;
            default:
                throw new Exception('Model not supported');
        }
        $path .= "/*." . self::FILE_EXTENSION;

        foreach ($this->getIterator($path) as $file) {
            $list[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
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
    public function getFilePath(DbSync_Model_AbstractModel $model)
    {
        switch (true) {
            case $model instanceof DbSync_Model_Table_Schema:
                $path = 'schema/' . $model->getTableName();
                break;
            case $model instanceof DbSync_Model_Table_Data:
                $path = 'data/' . $model->getTableName();
                break;
            case $model instanceof DbSync_Model_Table_Trigger:
                $path = 'trigger/' . $model->getTriggerName();
                break;
            default:
                throw new Exception('Model not supported');
        }
        return $this->_path . '/' . $path . '.' . self::FILE_EXTENSION;
    }

    /**
     * Get tableName by triggerName
     *
     * @param string $triggerName
     * @return string
     */
    public function getTableByTrigger($triggerName)
    {
        $path = "trigger/{$triggerName}." . self::FILE_EXTENSION;

        foreach ($this->getIterator($path) as $file) {
            $config = $this->load($file->getPathname());
            if (!empty($config['table'])) {
                return $config['table'];
            }
        }
        return '';
    }

    /**
     * Get triggers list
     *
     * @param array $tables
     * @return array
     */
    public function getTriggerList($tables = array())
    {
        $list = array();
        $path = "trigger/*." . self::FILE_EXTENSION;

        foreach ($this->getIterator($path) as $file) {
            $triggerName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if ($tables) {
                $tableName = $this->getTableByTrigger($triggerName);
                if (!in_array($tableName, $tables)) {
                    continue;
                }
            }
            $list[] = $triggerName;
        }
        return $list;
    }

    /**
     * Get iterator
     *
     * @param string $path
     * @param integer $flags
     * @return GlobIterator
     */
    public function getIterator($path, $flags = FilesystemIterator::SKIP_DOTS)
    {
        return new GlobIterator($this->_path . '/' . $path, $flags);
    }
}