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
class DbSync_Model_AbstractModelTest extends PHPUnit_Framework_TestCase
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
     * save
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Path 'tablespath' is not writable
     */
    public function test_save_notWriteable()
    {
        $path = 'tablespath';
        $model = $this->_getMock(array('isWritable'));

        $model->expects($this->atLeastOnce())
              ->method('isWritable')
              ->will($this->returnValue(false));

        $model->save($path);
    }

    /**
     * save
     *
     */
    public function test_save()
    {
        $path = 'tablespath';
        $data = array('somedata');
        $model = $this->_getMock(array('isWritable', 'generateConfigData'));

        $model->expects($this->atLeastOnce())
              ->method('isWritable')
              ->will($this->returnValue(true));

        $model->expects($this->once())
              ->method('generateConfigData')
              ->will($this->returnValue($data));

        $this->_fileAdapter->expects($this->once())
                           ->method('write')
                           ->with($this->equalTo($path), $this->equalTo($data));

        $model->save($path);
    }

    /**
     * hasFile
     *
     */
    public function test_hasFile()
    {
        $filepath = 'somepath';

        $model = $this->_getMock(array('getFilePath'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $this->assertTrue($model->hasFile($filepath));
    }

    /**
     * hasFile
     *
     */
    public function test_hasFile_false()
    {
        $filepath = null;

        $model = $this->_getMock(array('getFilePath'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $this->assertFalse($model->hasFile($filepath));
    }

    /**
     * getStatus
     *
     */
    public function test_getStatus_false()
    {
        $diff = array('some diff data');

        $model = $this->_getMock(array('diff'));

        $model->expects($this->once())
              ->method('diff')
              ->will($this->returnValue($diff));

        $this->assertFalse($model->getStatus());
    }

    /**
     * getStatus
     *
     */
    public function test_getStatus()
    {
        $diff = array();

        $model = $this->_getMock(array('diff'));

        $model->expects($this->once())
              ->method('diff')
              ->will($this->returnValue($diff));

        $this->assertTrue($model->getStatus());
    }

    /**
     * pull
     *
     */
    public function test_pull()
    {
        $diff = array();

        $model = $this->_getMock(array('init'));

        $model->expects($this->once())
              ->method('init')
              ->with($this->equalTo(true));

        $model->pull();
    }

    /**
     * getFilePath
     *
     */
    public function test_getFilePath_notReal()
    {
        $path = 'some path';

        $model = $this->_getMock(null);

        $this->_fileAdapter->expects($this->once())
                           ->method('getFilePath')
                           ->with($this->equalTo($model))
                           ->will($this->returnValue($path));

        $this->assertEquals($path, $model->getFilePath(false));
    }

    /**
     * getFilePath
     *
     */
    public function test_getFilePath()
    {
        $path = '.';
        $model = $this->_getMock(null);


        $this->_fileAdapter->expects($this->once())
                           ->method('getFilePath')
                           ->with($this->equalTo($model))
                           ->will($this->returnValue($path));

        $this->assertNotNull($model->getFilePath(true));
    }

    /**
     * deleteFile
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Config file not found
     */
    public function test_deleteFile_noFile()
    {
        $model = $this->_getMock(array('getFilePath'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue(false));

        $model->deleteFile();
    }

    /**
     * deleteFile
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Config file 'somepath' is not writable
     */
    public function test_deleteFile_notWriteable()
    {
        $filepath = 'somepath';

        $model = $this->_getMock(array('getFilePath', 'isWritable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isWritable')
              ->will($this->returnValue(false));

        $model->deleteFile();
    }

    /**
     * deleteFile
     *
     */
    public function test_deleteFile()
    {
        vfsStream::setup('exampleDir');

        $tableName = 'users';
        $filepath = vfsStream::url('exampleDir/asdfasdf.xml');
        fopen($filepath, 'a');

        $model = $this->_getMock(array('getFilePath', 'isWritable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isWritable')
              ->will($this->returnValue(true));

        $this->assertTrue(file_exists($filepath));

        $this->assertTrue($model->deleteFile());

        $this->assertFalse(file_exists($filepath));
    }

    /**
     * isWritable
     *
     */
    public function test_isWritable()
    {
        vfsStream::setup('exampleDir');

        $filepath = vfsStream::url('exampleDir/config.xml');
        fopen($filepath, 'a');

        $model = $this->_getMock(array('getFilePath'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $this->assertTrue($model->isWritable());
    }

    /**
     * isWritable
     *
     * @depends test_getFilePath_notReal
     * @depends test_getFilePath_notReal
     * @depends test_deleteFile_noFile
     */
    public function test_isWritable_false()
    {
        vfsStream::setup('exampleDir');
        $filepath = vfsStream::url('exampleDir/tables/config.xml');

        $tableName = 'users';

        $model = $this->_getMock(array('getTableName'));

        $this->_fileAdapter->expects($this->exactly(2))
                           ->method('getFilePath')
                           ->will($this->returnValue($filepath));

        $this->assertTrue($model->isWritable());
    }

    /**
     * init
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Path 'somepath' is not writable
     */
    public function test_init_notWritable()
    {
        $filepath = 'somepath';

        $model = $this->_getMock(array('getFilePath', 'isWritable'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isWritable')
              ->will($this->returnValue(false));

        $model->init(true);
    }

    /**
     * init
     *
     */
    public function test_init()
    {
        $filepath = 'somepath';

        $model = $this->_getMock(array('getFilePath', 'isWritable', 'save'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->atLeastOnce())
              ->method('isWritable')
              ->will($this->returnValue(true));

        $model->expects($this->once())
              ->method('save')
              ->with($this->equalTo($filepath));

        $this->assertTrue($model->init(true));
    }

    /**
     * init
     *
     */
    public function test_init_false()
    {
        $filepath = 'somepath';
        $model = $this->_getMock(array('getFilePath', 'save'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->never())
              ->method('save');

        $this->assertFalse($model->init());
    }

    /**
     * diff
     *
     * @expectedException        DbSync_Exception
     * @expectedExceptionMessage Config file not found
     */
    public function test_diff_noFile()
    {
        $model = $this->_getMock(array('getFilePath'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue(false));

        $model->diff();
    }

    /**
     * diff
     *
     */
    public function test_diff()
    {
        vfsStream::setup('exampleDir');

        $filepath = vfsStream::url('exampleDir/config.yml');
        $filepathTmp = vfsStream::url('exampleDir/config.yml.tmp');
        fopen($filepath, 'a');
        fopen($filepathTmp, 'a');

        $model = $this->_getMock(array('getFilePath', 'save'));

        $model->expects($this->once())
              ->method('getFilePath')
              ->will($this->returnValue($filepath));

        $model->expects($this->once())
              ->method('save')
              ->with($this->equalTo($filepath . '.tmp'));

        $this->assertEmpty($model->diff());

        $this->assertFalse(file_exists($filepathTmp));
    }

    /**
     * push
     *
     */
    public function test_push_true()
    {
        $sql = 'some sql';

        $model = $this->_getMock(array('generateSql'));

        $model->expects($this->once())
              ->method('generateSql')
              ->will($this->returnValue($sql));

        $this->_dbAdapter->expects($this->once())
                         ->method('execute')
                         ->with($this->equalTo($sql))
                         ->will($this->returnValue(0));

        $this->assertTrue($model->push());
    }

    /**
     * push
     *
     */
    public function test_push_false()
    {
        $sql = 'some sql';

        $model = $this->_getMock(array('generateSql'));

        $model->expects($this->once())
              ->method('generateSql')
              ->will($this->returnValue($sql));

        $this->_dbAdapter->expects($this->once())
                         ->method('execute')
                         ->with($this->equalTo($sql))
                         ->will($this->returnValue(false));

        $this->assertFalse($model->push());
    }
}

