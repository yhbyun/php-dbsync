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
 * @category DbSync
 * @package  Tests
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Model_Table_AbstractTableTest
 *
 * @group    table
 * @category DbSync
 * @package  Tests
 * @version  $Id$
 */
class DbSync_Model_Table_AbstractTableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileAdapter;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dbAdapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        global $config;

        $this->_dbAdapter = $this->getMock($config['dbAdapter'], array(), array($config['dbParams']));
        $this->_fileAdapter = $this->getMock($config['fileAdapter'], array(), array($config['path']));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated Console::tearDown()
        parent::tearDown();
    }

    /**
     * Get mock
     *
     * @param array $methods
     * @param array $stubs
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMock($methods = array(), array $stubs = array('getTableName' => 'users'))
    {
        if (is_array($methods)) {
            $methods = array_merge($methods, array_keys($stubs));
        }

        $mock = $this->getMock(
            'DbSync_Model_Table_Schema',
            $methods,
            array($this->_dbAdapter, $this->_fileAdapter, 'diff')
        );
        if ($stubs) {
            foreach ($stubs as $stubMethod => $stubValue) {
                $mock->expects($this->any())
                     ->method($stubMethod)
                     ->will($this->returnValue($stubValue));
            }
        }
        return $mock;
    }

    /**
     * getTableName
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Table name not set
     */
    public function test_getTableName()
    {
        $model = $this->_getMock(null);

        $model->getTableName();
    }

    /**
     * getName
     *
     * @depends test_getTableName
     */
    public function test_getName()
    {
        $tableName = 'users';

        $model = $this->_getMock();

        $this->assertEquals($tableName, $model->getName());
    }

    /**
     * setTableName
     *
     * @depends test_getTableName
     */
    public function test_setTableName()
    {
        $tableName = 'users';

        $model = $this->_getMock(null);

        $model->setTableName($tableName);

        $this->assertEquals($tableName, $model->getTableName());
    }

    /**
     * hasDbTable
     *
     */
    public function test_hasDbTable()
    {
        $tableName = 'users';

        $model = $this->_getMock();

        $this->_dbAdapter->expects($this->once())
                         ->method('hasTable')
                         ->with($this->equalTo($tableName))
                         ->will($this->returnValue(true));

        $this->assertTrue($model->hasDbTable());
    }

    /**
     * hasDbTable
     *
     */
    public function test_hasDbTable_false()
    {
        $tableName = 'users';

        $model = $this->_getMock();

        $this->_dbAdapter->expects($this->once())
                         ->method('hasTable')
                         ->with($this->equalTo($tableName))
                         ->will($this->returnValue(false));

        $this->assertFalse($model->hasDbTable());
    }

    /**
     * getListDb
     *
     */
    public function test_getListDb()
    {
        $list = array('users', 'articles', 'settings');

        $model = $this->_getMock(null);

        $this->_dbAdapter->expects($this->once())
                         ->method('getTableList')
                         ->will($this->returnValue($list));

        $this->assertEquals($list, $model->getListDb());
    }

    /**
     * getListConfig
     *
     */
    public function test_getListConfig()
    {
        $list = array('users', 'articles', 'settings');

        $model = $this->_getMock(null);

        $this->_fileAdapter->expects($this->once())
                           ->method('getTableList')
                           ->will($this->returnValue($list));

        $this->assertEquals($list, $model->getListConfig());
    }

    /**
     * getList
     *
     * @depends test_getListConfig
     * @depends test_getListDb
     */
    public function test_getList()
    {
        $list1 = array('users', 'articles', 'settings');
        $list2 = array('users1', 'articles', 'settings2');
        $list = array_unique(array_merge($list1, $list2));
        sort($list);

        $model = $this->_getMock(array('getListConfig', 'getListDb'));

        $model->expects($this->once())
              ->method('getListConfig')
              ->will($this->returnValue($list1));

        $model->expects($this->once())
              ->method('getListDb')
              ->will($this->returnValue($list2));

        $result = $model->getList();
        sort($result);

        $this->assertEquals($list, $result);
    }
}

