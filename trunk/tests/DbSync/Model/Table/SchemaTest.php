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
 * @category DbSync
 * @package  Tests
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Model_Table_SchemaTest
 *
 * @group    table
 * @category DbSync
 * @package  Tests
 * @version  $Id$
 */
class DbSync_Model_Table_SchemaTest extends PHPUnit_Framework_TestCase
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
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMock($methods)
    {
        return $this->getMock(
            'DbSync_Model_Table_Schema',
            $methods,
            array($this->_dbAdapter, $this->_fileAdapter, 'diff')
        );
    }

    /**
     * @test
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Config for 'users' not found
     */
    public function generateSql_configNotFound()
    {
        $tableName = 'users';

        $model = $this->_getMock(array('getFilePath', 'getTableName'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue(false));

        $model->expects($this->atLeastOnce())
              ->method('getTableName')
              ->will($this->returnValue($tableName));

        $this->_fileAdapter->expects($this->never())
                           ->method('load');

        $this->_dbAdapter->expects($this->never())
                         ->method('createAlter');
        $model->push();
    }

    /**
     * @test
     *
     */
    public function generateSql()
    {
        $tableName = 'users';
        $filename = 'table/users';
        $sql = 'Alter some table';
        $data = array(
            'name' => $tableName,
            'charset' => 'UTF-8',
            'engine' => 'MyISAM',
            'columns' => array(
                'name' => array('type' => 'varchar(255)', 'default' => null)
            )
        );

        $model = $this->_getMock(array('getFilePath', 'getTableName'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filename));

        $model->expects($this->atLeastOnce())
              ->method('getTableName')
              ->will($this->returnValue($tableName));

        $this->_fileAdapter->expects($this->once())
                           ->method('load')
                           ->with($this->equalTo($filename))
                           ->will($this->returnValue($data));

        $this->_dbAdapter->expects($this->once())
                         ->method('createAlter')
                         ->with($this->equalTo($data), $this->equalTo($tableName))
                         ->will($this->returnValue($sql));

        $this->assertEquals($sql, $model->generateSql());
    }

    /**
     * @test
     */
    public function generateConfigData()
    {
        $tableName = 'users';
        $data = array(
            'name' => $tableName,
            'charset' => 'UTF-8',
            'engine' => 'MyISAM',
            'columns' => array(
                'name' => array('type' => 'varchar(255)', 'default' => null)
            )
        );

        $model = $this->_getMock(array('getTableName'));

        $model->expects($this->atLeastOnce())
              ->method('getTableName')
              ->will($this->returnValue($tableName));

        $this->_dbAdapter->expects($this->once())
                         ->method('parseSchema')
                         ->with($this->equalTo($tableName))
                         ->will($this->returnValue($data));

        $this->assertEquals($data, $model->generateConfigData());
    }

    /**
     * @test
     */
    public function dropDbTable()
    {
        $tableName = 'users';
        $data = array(
            'name' => $tableName,
            'charset' => 'UTF-8',
            'engine' => 'MyISAM',
            'columns' => array(
                'name' => array('type' => 'varchar(255)', 'default' => null)
            )
        );

        $model = $this->_getMock(array('getTableName'));

        $model->expects($this->atLeastOnce())
              ->method('getTableName')
              ->will($this->returnValue($tableName));

        $this->_dbAdapter->expects($this->once())
                         ->method('dropTable')
                         ->with($this->equalTo($tableName))
                         ->will($this->returnValue(true));

        $this->assertTrue($model->dropDbTable());
    }
}

