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

require_once dirname(__FILE__) . '/PDO.php';

/**
 * DbSync_DbAdapter_MysqlTest
 *
 * @group    db
 * @category DbSync
 * @package  Tests
 * @version  $Id$
 */
class DbSync_DbAdapter_Pdo_AbstractAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
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
    protected function _getMock($methods = array())
    {
        $adapter = $this->getMockForAbstractClass(
            'DbSync_DbAdapter_Pdo_AbstractAdapter',
            array(),
            '',
            false,
            true,
            true,
            $methods
        );
        $adapter->setConnection($this->getMock('Stub_PDO', array()));

        return $adapter;
    }

    /**
     * getConnection
     *
     */
    public function test_getConnection()
    {
        $adapter = $this->_getMock();

        $this->assertInstanceOf('PDO', $adapter->getConnection());
    }

    /**
     * execute
     */
    public function test_execute()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');
        $input = 'Select * from users';

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('exec')
            ->with($this->equalTo($input))
            ->will($this->returnValue($result));

        $this->assertEquals($result, $adapter->execute($input));
    }

    /**
     * truncate
     */
    public function test_truncate()
    {
        $adapter = $this->_getMock(array('execute'));
        $tableName = 'users';

        $adapter->expects($this->once())
                ->method('execute')
                ->with($this->equalTo("TRUNCATE TABLE {$tableName}"))
                ->will($this->returnValue(true));

        $adapter->truncate($tableName);
    }

    /**
     * dropTable
     */
    public function test_dropTable()
    {
        $adapter = $this->_getMock(array('execute'));
        $tableName = 'users';

        $adapter->expects($this->once())
                ->method('execute')
                ->with($this->equalTo("DROP TABLE IF EXISTS {$tableName}"))
                ->will($this->returnValue(true));

        $adapter->dropTable($tableName);
    }

    /**
     * isEmpty
     */
    public function test_isEmpty()
    {
        $adapter = $this->_getMock();
        $result = 21;
        $tableName = 'table_name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetch')
             ->with($this->equalTo(PDO::FETCH_COLUMN))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SELECT COUNT(*) FROM `{$tableName}`"))
            ->will($this->returnValue($stmt));

        $this->assertFalse($adapter->isEmpty($tableName));
    }

    /**
     * isEmpty
     */
    public function test_isEmpty_true()
    {
        $adapter = $this->_getMock();
        $result = null;
        $tableName = 'table_name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetch')
             ->with($this->equalTo(PDO::FETCH_COLUMN))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SELECT COUNT(*) FROM `{$tableName}`"))
            ->will($this->returnValue($stmt));

        $this->assertTrue($adapter->isEmpty($tableName));
    }

    /**
     * fetchData
     */
    public function test_fetchData()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');
        $tableName = 'name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->with($this->equalTo(PDO::FETCH_ASSOC))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SELECT * FROM {$tableName}"))
            ->will($this->returnValue($stmt));

        $this->assertEquals($result, $adapter->fetchData($tableName));
    }
}

