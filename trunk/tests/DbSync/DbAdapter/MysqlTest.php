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

require_once dirname(__FILE__) . '/PDO.php';

/**
 * DbSync_DbAdapter_MysqlTest
 *
 * @group    db
 * @category DbSync
 * @package  Tests
 * @version  $Id$
 */
class DbSync_DbAdapter_MysqlTest extends PHPUnit_Framework_TestCase
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
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMock($methods = null)
    {
        $adapter = $this->getMock('DbSync_DbAdapter_Mysql', $methods, array(array()), '', false);
        $adapter->setConnection($this->getMock('DbSync_DbAdapter_PDO', array()));

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
                ->with($this->equalTo("DELETE FROM {$tableName}"))
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
     * dropTrigger
     */
    public function test_dropTrigger()
    {
        $adapter = $this->_getMock(array('execute'));
        $triggerName = 'users';

        $adapter->expects($this->once())
                ->method('execute')
                ->with($this->equalTo("DROP TRIGGER IF EXISTS {$triggerName}"))
                ->will($this->returnValue(true));

        $adapter->dropTrigger($triggerName);
    }

    /**
     * getTriggerInfo
     */
    public function test_getTriggerInfo()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');
        $triggerName = 'trigger_name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetch')
             ->with($this->equalTo(PDO::FETCH_OBJ))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TRIGGERS WHERE `Trigger` = '{$triggerName}';"))
            ->will($this->returnValue($stmt));

        $this->assertEquals($result, $adapter->getTriggerInfo($triggerName));
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
     * hasTable
     */
    public function test_hasTable()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');
        $tableName = 'table_name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetch')
             ->with($this->equalTo(PDO::FETCH_NUM))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TABLES LIKE '{$tableName}'"))
            ->will($this->returnValue($stmt));

        $this->assertTrue($adapter->hasTable($tableName));
    }

    /**
     * hasTable
     */
    public function test_hasTable_false()
    {
        $adapter = $this->_getMock();
        $result = array();
        $tableName = 'table_name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetch')
             ->with($this->equalTo(PDO::FETCH_NUM))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TABLES LIKE '{$tableName}'"))
            ->will($this->returnValue($stmt));

        $this->assertFalse($adapter->hasTable($tableName));
    }

    /**
     * hasTrigger
     */
    public function test_hasTrigger()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');
        $triggerName = 'name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetch')
             ->with($this->equalTo(PDO::FETCH_NUM))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TRIGGERS WHERE `Trigger` = '{$triggerName}';"))
            ->will($this->returnValue($stmt));

        $this->assertTrue($adapter->hasTrigger($triggerName));
    }

    /**
     * hasTrigger
     */
    public function test_hasTrigger_false()
    {
        $adapter = $this->_getMock();
        $result = array();
        $triggerName = 'name';

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetch')
             ->with($this->equalTo(PDO::FETCH_NUM))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TRIGGERS WHERE `Trigger` = '{$triggerName}';"))
            ->will($this->returnValue($stmt));

        $this->assertFalse($adapter->hasTrigger($triggerName));
    }

    /**
     * getTableList
     */
    public function test_getTableList()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->with($this->equalTo(PDO::FETCH_COLUMN))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TABLES"))
            ->will($this->returnValue($stmt));

        $this->assertEquals($result, $adapter->getTableList());
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

    /**
     * getTriggerList
     */
    public function test_getTriggerList()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');
        $tables = array('users', 'articles');

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->with($this->equalTo(PDO::FETCH_COLUMN))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TRIGGERS WHERE `Table` = 'users' OR `Table` = 'articles'"))
            ->will($this->returnValue($stmt));

        $this->assertEquals($result, $adapter->getTriggerList($tables));
    }

    /**
     * getTriggerList
     */
    public function test_getTriggerList_all()
    {
        $adapter = $this->_getMock();
        $result = array('someresult');
        $tables = array();

        $stmt = $this->getMock('PDOStatement');
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->with($this->equalTo(PDO::FETCH_COLUMN))
             ->will($this->returnValue($result));

        $pdo = $adapter->getConnection();

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SHOW TRIGGERS "))
            ->will($this->returnValue($stmt));

        $this->assertEquals($result, $adapter->getTriggerList($tables));
    }

    /**
     * parseTrigger
     */
    public function test_parseTrigger()
    {
        $adapter = $this->_getMock(array('getTriggerInfo'));
        $result = array(
            'name' => 'somename',
            'table' => 'sometable',
            'event' => 'someevent',
            'timing' => 'sometiming',
            'definer' => 'somedefiner',
            'statement' => 'somestatement'
        );

        $trigger = new stdClass();
        $trigger->Trigger = $result['name'];
        $trigger->Table = $result['table'];
        $trigger->Event = $result['event'];
        $trigger->Timing = $result['timing'];
        $trigger->Definer = $result['definer'];
        $trigger->Statement = $result['statement'];

        $triggerName = 'trigger_name';

        $adapter->expects($this->once())
                ->method('getTriggerInfo')
                ->with($this->equalTo($triggerName))
                ->will($this->returnValue($trigger));

        $this->assertEquals($result, $adapter->parseTrigger($triggerName));
    }

    /**
     * createTriggerSql
     */
    public function test_createTriggerSql()
    {
        $adapter = $this->_getMock();
        $config = array(
            'name' => 'somename',
            'table' => 'sometable',
            'event' => 'someevent',
            'timing' => 'sometiming',
            'definer' => 'somedefiner',
            'statement' => 'somestatement'
        );

        $sql = "DELIMITER $$
DROP TRIGGER IF EXISTS `somename`$$
CREATE DEFINER = 'somedefiner'
TRIGGER `somename` sometiming someevent
ON `sometable`
FOR EACH ROW
somestatement
$$
DELIMITER ;";
        $this->assertEquals($sql, $adapter->createTriggerSql($config));
    }

    /**
     * getTableByTrigger
     */
    public function test_getTableByTrigger()
    {
        $adapter = $this->_getMock(array('getTriggerInfo'));

        $trigger = new stdClass();
        $trigger->Table = 'sometable';

        $triggerName = 'trigger_name';

        $adapter->expects($this->once())
                ->method('getTriggerInfo')
                ->with($this->equalTo($triggerName))
                ->will($this->returnValue($trigger));

        $this->assertEquals($trigger->Table, $adapter->getTableByTrigger($triggerName));
    }

    /**
     * getTableByTrigger
     */
    public function test_getTableByTrigger_null()
    {
        $adapter = $this->_getMock(array('getTriggerInfo'));

        $trigger = new stdClass();

        $triggerName = 'trigger_name';

        $adapter->expects($this->once())
                ->method('getTriggerInfo')
                ->with($this->equalTo($triggerName))
                ->will($this->returnValue($trigger));

        $this->assertNull($adapter->getTableByTrigger($triggerName));
    }
}

