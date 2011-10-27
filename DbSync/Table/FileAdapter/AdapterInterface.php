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
 * DbSync_Table_FileAdapter_AdapterInterface
 *
 * @category   DbSync
 * @package    DbSync_Table
 * @subpackage FileAdapter
 * @version    $Id$
 */
interface DbSync_Table_FileAdapter_AdapterInterface
{
    /**
     * Contructor
     *
     * @param string $path
     */
    public function __construct($path);

    /**
     * Write data to file
     *
     * @param string $filename Full path
     * @param array $data
     * @return int The function returns the number of bytes that were written
     * to the file, or false on failure.
     */
    public function write($filename, array $data);

    /**
     * Load data from file
     *
     * @param string $filename
     * @return array
     */
    public function load($filename);

    /**
     * Get data tables list
     *
     * @return array
     */
    public function getTableList($filename);

    /**
     * Get config filepath
     *
     * @param boolen $real
     * @throws Exception
     * @return string
     */
    public function getFilePath($tableName, $filename, $trigger = false);

    /**
     * Get tableName by triggerName
     *
     * @param string $triggerName
     * @return string
     */
    public function getTableByTrigger($triggerName);

    /**
     * Get triggers list
     *
     * @return array
     */
    public function getTriggerList();
}