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
 * @package    DbSync_FileAdapter
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_FileAdapter_AdapterInterface
 *
 * @category   DbSync
 * @package    DbSync_FileAdapter
 * @version    $Id$
 */
interface DbSync_FileAdapter_AdapterInterface
{
    /**
     * Constructor
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
     * @param DbSync_Model_AbstractModel $model
     * @return array
     */
    public function getTableList(DbSync_Model_AbstractModel $model);

    /**
     * Get config filepath
     *
     * @param DbSync_Model_AbstractModel $model
     * @throws Exception
     * @return string
     */
    public function getFilePath(DbSync_Model_AbstractModel $model);

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