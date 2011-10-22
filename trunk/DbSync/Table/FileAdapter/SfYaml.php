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
 * @package    DbSync_Table
 * @subpackage FileAdapter
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_Table_FileAdapter_SfYaml
 *
 * @category   DbSync
 * @package    DbSync_Table
 * @subpackage FileAdapter
 * @version    $Id: Trigger.php 31 2011-10-20 23:10:06Z maks.slesarenko@gmail.com $
 */
class DbSync_Table_FileAdapter_SfYaml
    implements DbSync_Table_FileAdapter_AdapterInterface
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

    /**
     * Get tableName by triggerName
     *
     * @param string $triggerName
     * @return string
     */
    public function getTableNameByTriggerName($triggerName)
    {
        foreach (new GlobIterator("{$this->_path}/*/triggers/{$triggerName}." . self::FILE_EXTENSION) as $file) {
            return basename(dirname(dirname($file->getPathname())));
        }
        return '';
    }

    /**
     * Get triggers list
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