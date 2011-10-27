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
 * @version  $Id: AbstractTableTest.php 42 2011-10-24 20:09:38Z maks.slesarenko@gmail.com $
 */

/**
 * DbSync_Table_DbAdapter_SfYamlTest
 *
 * @group    file
 * @category DbSync
 * @package  Tests
 * @version  $Id: AbstractTableTest.php 42 2011-10-24 20:09:38Z maks.slesarenko@gmail.com $
 */
class DbSync_Table_DbAdapter_SfYamlTest extends PHPUnit_Framework_TestCase
{
    protected $_path;
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        vfsStream::setup('exampleDir');
        $this->_path = vfsStream::url('exampleDir');
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
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMock($methods = null)
    {
        return $this->getMock('DbSync_Table_FileAdapter_SfYaml', $methods, array($this->_path));
    }

    /**
     * getFilePath
     */
    public function test_getFilePath()
    {
        $adapter = $this->_getMock();

        $tableName = 'users';
        $filename = 'schema';

        $result = join('/', array($this->_path, $tableName, $filename))
                . '.' . DbSync_Table_FileAdapter_SfYaml::FILE_EXTENSION;

        $this->assertEquals($result, $adapter->getFilePath($tableName, $filename));
    }

    /**
     * getFilePath
     */
    public function test_getFilePath_triggers()
    {
        $adapter = $this->_getMock();

        $tableName = 'users';
        $filename = 'mytrigger';

        $result = join('/', array($this->_path, $tableName, 'triggers', $filename))
                . '.' . DbSync_Table_FileAdapter_SfYaml::FILE_EXTENSION;

        $this->assertEquals($result, $adapter->getFilePath($tableName, $filename, true));
    }

    /**
     * getTableList
     */
    public function test_getTableList()
    {
        $filename = 'schema';
        $file = $filename . '.' . DbSync_Table_FileAdapter_SfYaml::FILE_EXTENSION;

        mkdir($this->_path . '/users/', 0777, true);
        mkdir($this->_path . '/setting/', 0777, true);

        fopen($this->_path . '/users/' . $file, "a");
        fopen($this->_path . '/setting/' . $file, "a");

        $adapter = $this->_getMock(array('getIterator'));
        $adapter->expects($this->once())
                ->method('getIterator')
                ->with($this->equalTo('*/' . $file))
                ->will($this->returnValue(
                    array(
                        new SplFileObject($this->_path . '/users/' . $file),
                        new SplFileObject($this->_path . '/setting/' . $file)
                    )
                ));


        $this->assertEquals(
            array('users', 'setting'),
            $adapter->getTableList($filename)
        );
    }


    /**
     * getTriggerList
     */
    public function test_getTriggerList()
    {
        mkdir($this->_path . '/users/triggers/', 0777, true);
        mkdir($this->_path . '/setting/triggers', 0777, true);

        fopen($this->_path . '/users/triggers/trigger1.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger2.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger3.yml', "a");

        $adapter = $this->_getMock(array('getIterator'));
        $adapter->expects($this->once())
                ->method('getIterator')
                ->with($this->equalTo('*/triggers/*.yml'))
                ->will($this->returnValue(
                    array(
                        new SplFileObject($this->_path . '/users/triggers/trigger1.yml'),
                        new SplFileObject($this->_path . '/setting/triggers/trigger2.yml'),
                        new SplFileObject($this->_path . '/setting/triggers/trigger3.yml')
                    )
                ));


        $this->assertEquals(
            array('trigger1', 'trigger2', 'trigger3'),
            $adapter->getTriggerList(array())
        );
    }

    /**
     * getTriggerList
     */
    public function test_getTriggerList_forTable()
    {
        $tableName = 'users';

        mkdir($this->_path . '/users/triggers/', 0777, true);
        mkdir($this->_path . '/setting/triggers', 0777, true);

        fopen($this->_path . '/users/triggers/trigger1.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger2.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger3.yml', "a");

        $adapter = $this->_getMock(array('getIterator'));
        $adapter->expects($this->once())
                ->method('getIterator')
                ->with($this->equalTo("{$tableName}/triggers/*.yml"))
                ->will($this->returnValue(
                    array(
                        new SplFileObject($this->_path . '/users/triggers/trigger1.yml'),
                    )
                ));


        $this->assertEquals(
            array('trigger1'),
            $adapter->getTriggerList(array($tableName))
        );
    }

    /**
     * getTableByTrigger
     */
    public function test_getTableByTrigger()
    {
        mkdir($this->_path . '/users/triggers/', 0777, true);
        mkdir($this->_path . '/setting/triggers', 0777, true);

        fopen($this->_path . '/users/triggers/trigger1.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger2.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger3.yml', "a");

        $adapter = $this->_getMock(array('getIterator'));
        $adapter->expects($this->once())
                ->method('getIterator')
                ->with($this->equalTo("*/triggers/trigger1.yml"))
                ->will($this->returnValue(
                    array(
                        new SplFileObject($this->_path . '/users/triggers/trigger1.yml'),
                    )
                ));


        $this->assertEquals(
            'users',
            $adapter->getTableByTrigger('trigger1')
        );
    }

    /**
     * getTableByTrigger
     */
    public function test_getTableByTrigger_notFound()
    {
        mkdir($this->_path . '/users/triggers/', 0777, true);
        mkdir($this->_path . '/setting/triggers', 0777, true);

        fopen($this->_path . '/users/triggers/trigger1.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger2.yml', "a");
        fopen($this->_path . '/setting/triggers/trigger3.yml', "a");

        $adapter = $this->_getMock(array('getIterator'));
        $adapter->expects($this->once())
                ->method('getIterator')
                ->will($this->returnValue(array()));


        $this->assertEquals('', $adapter->getTableByTrigger('trigger4'));
    }

    /**
     * getIterator
     */
    public function test_getIterator()
    {
        $adapter = $this->_getMock();
        $iterator = $adapter->getIterator('users/*');

        $this->assertInstanceOf('GlobIterator', $iterator);
    }

    /**
     * write
     */
    public function test_write()
    {
        $data = array(
            'name' => 'somename',
            'value' => 'somevalue'
        );

        $file = $this->_path . '/config.yml';
        fopen($file, 'a');

        $adapter = $this->_getMock();
        $adapter->write($file, $data);


        $this->assertEquals(
            "name: somename\nvalue: somevalue\n",
            file_get_contents($file)
        );
    }
}

