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
 * DbSync_Model_Table_DataTest
 *
 * @group    table
 * @category DbSync
 * @package  Tests
 * @version  $Id$
 */
class DbSync_Model_Table_DataTest extends PHPUnit_Framework_TestCase
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
            'DbSync_Model_Table_Data',
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
     * @test
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Config for 'users' not found
     */
    public function push_configNotFound()
    {
        $model = $this->_getMock(array('getFilePath', 'isEmptyTable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue(false));

        $model->expects($this->never())
              ->method('isEmptyTable');

        $this->_fileAdapter->expects($this->never())
                           ->method('load');

        $this->_dbAdapter->expects($this->never())
                         ->method('truncate');

        $this->_dbAdapter->expects($this->never())
                         ->method('insert');
        $model->push();
    }

    /**
     * @test
     *
     */
    public function push()
    {
        $data = array(
            array('id' => 1, 'name' => 'john')
        );
        $tableName = 'users';
        $filepath = 'tables/users';

        $model = $this->_getMock(array('getFilePath', 'isEmptyTable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isEmptyTable')
              ->will($this->returnValue(true));

        $this->_fileAdapter->expects($this->once())
                           ->method('load')
                           ->with($this->equalTo($filepath))
                           ->will($this->returnValue($data));

        $this->_dbAdapter->expects($this->never())
                         ->method('truncate');
        $this->_dbAdapter->expects($this->once())
                         ->method('insert')
                         ->with($this->equalTo($data), $this->equalTo($tableName));
        $model->push();
    }

    /**
     * @test
     *
     */
    public function push_emptyData()
    {
        $data = array();
        $filepath = 'tables/users';

        $model = $this->_getMock(array('getFilePath', 'isEmptyTable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->never())
              ->method('isEmptyTable');

        $this->_fileAdapter->expects($this->once())
                           ->method('load')
                           ->with($this->equalTo($filepath))
                           ->will($this->returnValue($data));

        $this->_dbAdapter->expects($this->never())
                         ->method('truncate');
        $this->_dbAdapter->expects($this->never())
                         ->method('insert');
        $model->push();
    }

    /**
     * @test
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage  Table 'users' is not empty
     */
    public function push_notEmptyTable()
    {
        $data = array(
            array('id' => 1, 'name' => 'john')
        );
        $filepath = 'tables/users';

        $model = $this->_getMock(array('getFilePath', 'isEmptyTable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isEmptyTable')
              ->will($this->returnValue(false));

        $this->_fileAdapter->expects($this->once())
                           ->method('load')
                           ->with($this->equalTo($filepath))
                           ->will($this->returnValue($data));

        $this->_dbAdapter->expects($this->never())
                         ->method('insert');

        $this->_dbAdapter->expects($this->never())
                         ->method('truncate');
        $model->push();
    }

    /**
     * @test
     *
     */
    public function push_force()
    {
        $data = array(
            array('id' => 1, 'name' => 'john')
        );
        $tableName = 'users';
        $filepath = 'tables/users';

        $model = $this->_getMock(array('getFilePath', 'isEmptyTable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isEmptyTable')
              ->will($this->returnValue(false));

        $this->_fileAdapter->expects($this->once())
                           ->method('load')
                           ->with($this->equalTo($filepath))
                           ->will($this->returnValue($data));

        $this->_dbAdapter->expects($this->once())
                         ->method('truncate')
                         ->with($this->equalTo($tableName));
        $this->_dbAdapter->expects($this->once())
                         ->method('insert')
                         ->with($this->equalTo($data), $this->equalTo($tableName));

        $model->push(DbSync_Model_Table_Data::PUSH_TYPE_FORCE);
    }

    /**
     * @test
     *
     */
    public function push_merge()
    {
        $data = array(
            array('id' => 1, 'name' => 'john')
        );
        $tableName = 'users';
        $filepath = 'tables/users';

        $model = $this->_getMock(array('getFilePath', 'isEmptyTable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isEmptyTable')
              ->will($this->returnValue(false));

        $this->_fileAdapter->expects($this->once())
                           ->method('load')
                           ->with($this->equalTo($filepath))
                           ->will($this->returnValue($data));

        $this->_dbAdapter->expects($this->never())
                         ->method('truncate');

        $this->_dbAdapter->expects($this->once())
                         ->method('merge')
                         ->with($this->equalTo($data), $this->equalTo($tableName));
        $model->push(DbSync_Model_Table_Data::PUSH_TYPE_MERGE);
    }

    /**
     * @test
     *
     */
    public function isEmptyTable_true()
    {
        $tableName = 'users';

        $model = $this->_getMock(array('hasDbTable'));

        $model->expects($this->atLeastOnce())
              ->method('hasDbTable')
              ->will($this->returnValue(true));

        $this->_dbAdapter->expects($this->once())
                         ->method('isEmpty')
                         ->with($this->equalTo($tableName))
                         ->will($this->returnValue(true));

        $this->assertTrue($model->isEmptyTable());
    }

    /**
     * @test
     *
     */
    public function isEmptyTable_false()
    {
        $tableName = 'users';

        $model = $this->_getMock(array('hasDbTable'));

        $model->expects($this->once())
              ->method('hasDbTable')
              ->will($this->returnValue(true));

        $this->_dbAdapter->expects($this->once())
                         ->method('isEmpty')
                         ->with($this->equalTo($tableName))
                         ->will($this->returnValue(false));

        $this->assertFalse($model->isEmptyTable());
    }

    /**
     * @test
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Table 'users' not found
     */
    public function isEmptyTable_exception()
    {
        $model = $this->_getMock(array('hasDbTable'));

        $model->expects($this->once())
              ->method('hasDbTable')
              ->will($this->returnValue(false));

        $this->_dbAdapter->expects($this->never())
                         ->method('isEmpty');

        $model->isEmptyTable();
    }

    /**
     * @test
     */
    public function generateConfigData()
    {
        $tableName = 'users';
        $data = array(
            array('id' => 1, 'name' => 'john')
        );

        $model = $this->_getMock();

        $this->_dbAdapter->expects($this->once())
                         ->method('fetchData')
                         ->with($this->equalTo($tableName))
                         ->will($this->returnValue($data));

        $this->assertEquals($data, $model->generateConfigData());
    }
}

