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
 * @subpackage Function
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_Model_Function
 *
 * @category   DbSync
 * @package    DbSync_Model
 * @subpackage Function
 * @version    $Id$
 */
class DbSync_Model_Function extends DbSync_Model_AbstractModel
{
    /**
     * @var string
     */
    protected $_functionName;

    /**
     * Get table name
     *
     * @return string
     * @throws DbSync_Exception
     */
    public function getFunctionName()
    {
        if (!$this->_functionName) {
            throw new $this->_exceptionClass('Function name not set');
        }
        return $this->_functionName;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFunctionName();
    }

    /**
     * Set function name
     *
     * @param string $functionName
     * @return DbSync_Model_Function_AbstractFunction
     */
    public function setFunctionName($functionName)
    {
        $this->_functionName = (string) $functionName;

        return $this;
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
     * Get config function list
     *
     * @return array
     */
    public function getListConfig()
    {
        return $this->_fileAdapter->getFunctionList($this);
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
    public function hasDbFunction()
    {
        return $this->_dbAdapter->hasTable($this->getFunctionName());
    }

    /**
     * Get data to store in config file
     *
     * @return array
     */
    public function generateConfigData()
    {
        return $this->_dbAdapter->parseFunction($this->getFunctionName());
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
            throw new $this->_exceptionClass("Config for '{$this->getFunctionName()}' not found");
        }
        $data = $this->_fileAdapter->load($filename);

        return $this->_dbAdapter->createFunctionAlter($data, $this->getFunctionName());
    }

    /**
     * Delete Table
     *
     * @return boolen
     */
    public function dropDbTable()
    {
        return $this->_dbAdapter->dropFunction($this->getFunctionName());
    }
}