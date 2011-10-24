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
 * @subpackage DbAdapter
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_Table_DbAdapter_Mysql
 *
 * @category   DbSync
 * @package    DbSync_Table
 * @subpackage DbAdapter
 * @version    $Id$
 */
class DbSync_Table_DbAdapter_Mysql
    implements DbSync_Table_DbAdapter_AdapterInterface
{
    /**
     * @var PDO
     */
    protected $_db;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $connection = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']}",
            $config['username'],
            $config['password'],
            $config['options']
        );

        $this->setConnection($connection);
    }

    /**
     * Get connection
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->_db;
    }

    /**
     * Set connection
     *
     * @param PDO $connection
     * @return DbSync_Table_DbAdapter_Mysql
     */
    public function setConnection(PDO $connection)
    {
        $this->_db = $connection;
        return $this;
    }

    /**
     * Parse schema
     *
     * @return array
     */
    public function parseSchema($tableName)
    {
        $config = array(
            'name' => $tableName,
            'charset' => 'utf8',
            'engine' => 'InnoDb',
            'columns' => array()
        );

        $result = $this->_db->query("SHOW CREATE TABLE {$tableName}");

        $rows = $result->fetch(PDO::FETCH_NUM);

        if (isset($rows['1'])) {
            $info = explode(PHP_EOL, $rows['1']);
            unset($info['0']);
            $info = array_map('trim', $info);

            $tableInfo = array_pop($info);

            if (preg_match('|ENGINE=(\w+)|i', $tableInfo, $matches)) {
                $config['engine'] = $matches['1'];
            }
            if (preg_match('|CHARSET=(\w+)|i', $tableInfo, $matches)) {
                $config['charset'] = $matches['1'];
            }
            foreach ($info as $row) {
                $row = trim($row, ",");
                if (stripos($row, 'PRIMARY KEY') !== false) {
                    preg_match_all('|\w+|', substr($row, 14), $matches);
                    $config['primary'] = $matches['0'];
                }
                if (stripos($row, 'UNIQUE KEY') !== false) {
                    preg_match_all('|\w+|', substr($row, 10), $matches);
                    $config['unique'][$matches['0']['0']] = $matches['0']['1'];
                }
                if (stripos($row, 'UNIQUE KEY') !== false) {
                    preg_match_all('|\w+|', substr($row, 10), $matches);
                    $config['unique'][$matches['0']['0']] = $matches['0']['1'];
                }
                if (stripos($row, 'CONSTRAINT') !== false) {
                    preg_match('|CONSTRAINT ?`(\w+)|i', $row, $matches);

                    $keyName = $matches['1'];
                    $key = array();

                    if (preg_match('|FOREIGN KEY \(?`(\w+)|i', $row, $matches)) {
                        $key['foreign'] = $matches['1'];
                    }
                    if (preg_match('|REFERENCES ?`(\w+)|i', $row, $matches)) {
                        $key['references'] = $matches['1'];
                    }
                    if (preg_match('|ON DELETE (\w+)|i', $row, $matches)) {
                        $key['delete'] = $matches['1'];
                    }
                    if (preg_match('|ON UPDATE (\w+)|i', $row, $matches)) {
                        $key['update'] = $matches['1'];
                    }

                    $config['foreign'][$keyName] = $key;
                } elseif (stripos($row, '`') !== false) {
                    $key = array(
                        'type'     => null,
                        'default'  => null,
                        'nullable' => null,
                        'unsigned' => null,
                        'comment'  => null,
                    );
                    if (preg_match('|DEFAULT (\w+)|i', $row, $matches)) {
                        $key['default'] = $matches['1'];
                    }
                    if (preg_match('|COMMENT \'([^\']+)\'|i', $row, $matches)) {
                        $key['comment'] = $matches['1'];
                    }
                    if (preg_match('|NOT NULL|i', $row, $matches)) {
                        $key['nullable'] = false;
                    } else {
                        $key['nullable'] = true;
                    }
                    if (preg_match('|AUTO_INCREMENT|i', $row, $matches)) {
                        $key['autoincrement'] = true;
                    }
                    if (preg_match('|^`(\w+)` (\S+)|i', $row, $matches)) {
                        $key['type'] = $matches['2'];
                        $keyName = $matches['1'];
                        $config['columns'][$keyName] = $key;
                    }
                }
            }
        }
        return $config;
    }

    /**
     * Generate Alter Table
     *
     * @param array  $config
     * @param string $tableName
     * @return string
     */
    public function createAlter($config, $tableName)
    {
        $query = array();
        if (!$this->hasTable($tableName)) {
            foreach ($config['columns'] as $columnName => $columnConfig) {
                $query[] = $this->_getColumnSql($columnName, $columnConfig);
            }
            if ($config['primary']) {
                $query[] = "PRIMARY KEY (`" . join('`, `', $config['primary'])  . "`)";
            }
            if (!empty($config['unique'])) {
                foreach ($config['unique'] as $keyName => $columnName) {
                    $query[] = "UNIQUE KEY `{$keyName}` (`{$columnName}`)";
                }
            }
            $query = "CREATE TABLE `{$tableName}` (" . PHP_EOL
                   . join(',' . PHP_EOL, $query) . PHP_EOL
                   . ") ENGINE={$config['engine']} CHARSET={$config['charset']}";

        } else {
            $result = $this->_db->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = $result->fetchAll(PDO::FETCH_ASSOC);

            $query[]= "ALTER TABLE `{$tableName}` ENGINE={$config['engine']}, CHARSET={$config['charset']}";

            $after = null;
            foreach ($config['columns'] as $columnName => $columnConfig) {
                foreach ($columns as $i => $columnDesc) {
                    $exists = false;
                    if ($columnDesc['Field'] == $columnName) {
                        $exists = true;
                        unset($columns[$i]);
                        break;
                    }
                }
                $action = "ADD";
                if ($exists) {
                    $action = "MODIFY";
                }

                $query[] = "{$action} COLUMN " . $this->_getColumnSql($columnName, $columnConfig, $after);
                $after = $columnDesc['Field'];
            }
            foreach ($columns as $columnDesc) {
                $query[] = "DROP COLUMN `{$columnDesc['Field']}`";
            }

            if ($this->_hasPrimaryKey($tableName)) {
                $query[] = "DROP PRIMARY KEY";
            }
            foreach ($this->_getIndexes($tableName) as $row) {
                $query[] = "DROP INDEX `{$row['Key_name']}`";
            }
            if (!empty($config['primary'])) {
                $query[] = "ADD PRIMARY KEY (" . join(', ', $config['primary'])  . ")";
            }
            if (!empty($config['unique'])) {
                foreach ($config['unique'] as $keyName => $columnName) {
                    $query[] = "ADD UNIQUE KEY `{$keyName}` (`{$columnName}`)";
                }
            }
            $query = join(',' . PHP_EOL, $query);
        }

        return $query;
    }

    /**
     * Fetch db triggers
     *
     * @return string
     */
    public function parseTrigger($triggerName)
    {
        $row = $this->getTriggerInfo($triggerName);

        $config = array();
        if ($row) {
            $config['name'] = $row->Trigger;
            $config['table'] = $row->Table;
            $config['event'] = $row->Event;
            $config['timing'] = $row->Timing;
            $config['definer'] = $row->Definer;
            $config['statement'] = $row->Statement;
        }
        return $config;
    }

    /**
     * Generate trigger sql
     *
     * @param array $config
     * @return string
     */
    public function createTriggerSql($config)
    {
        $sql = array('DELIMITER $$');

        if ($config) {
            $sql[] = "DROP TRIGGER IF EXISTS `{$config['name']}`$$";
            $sql[] = "CREATE DEFINER = '{$config['definer']}'";
            $sql[] = "TRIGGER `{$config['name']}` {$config['timing']} {$config['event']}";
            $sql[] = "ON `{$config['table']}`";
            $sql[] = "FOR EACH ROW";
            $sql[] = $config['statement'];
        }
        $sql[] = '$$';
        $sql[] = 'DELIMITER ;';

        return join(PHP_EOL, $sql);
    }

    /**
     * Execute sql query
     *
     * @param string $sql
     * @return integer
     */
    public function execute($sql)
    {
        return $this->_db->exec($sql);
    }

    /**
     * Get triggers list
     *
     * @param array $tables
     * @return array
     */
    public function getTriggerList($tables = array())
    {
        $where = '';
        if ($tables) {
            $cond = array();
            foreach ($tables as $tableName) {
                 $cond[] = "`Table` = '{$tableName}'";
            }
            $where = 'WHERE ' . join(' OR ', $cond);
        }

        $result = $this->_db->query("SHOW TRIGGERS {$where}");
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get trigger info
     *
     * @param string $triggerName
     * @return object
     */
    public function getTriggerInfo($triggerName)
    {
        $result = $this->_db->query("SHOW TRIGGERS WHERE `Trigger` = '{$triggerName}';");
        return $result->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get tables list
     *
     * @return array
     */
    public function getTableList()
    {
        $result = $this->_db->query("SHOW TABLES");
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Is db table exists
     *
     * @return boolen
     */
    public function hasTable($tableName)
    {
        $result = $this->_db->query("SHOW TABLES LIKE '{$tableName}'");
        return (bool) $result->fetch(PDO::FETCH_NUM);
    }

    /**
     * Is db trigger exists
     *
     * @return boolen
     */
    public function hasTrigger($triggerName)
    {
        $result = $this->_db->query("SHOW TRIGGERS WHERE `Trigger` = '{$triggerName}';");
        return (bool) $result->fetch(PDO::FETCH_NUM);
    }

    /**
     * Fetch all data from table
     *
     * @return array
     */
    public function fetchData($tableName)
    {
        $result = $this->_db->query("SELECT * FROM {$tableName}");

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Push data to db table
     *
     * @param boolen $force
     * @return boolen
     * @throws Exception
     */
    public function insert($data, $tableName)
    {
        $row = current($data);
        $columns = array_keys($row);

        $values = '(' . join(', ', array_fill(0, count($columns), '?')) . ')';

        $values = join(', ', array_fill(0, count($data), $values));
        $sql = "INSERT INTO {$tableName} (" .join(', ', $columns) . ") VALUES {$values}";

        $this->_db->beginTransaction();

        $stmt = $this->_db->prepare($sql);

        $i = 0;
        foreach ($data as $row) {
            foreach ($row as $columnValue) {
                $stmt->bindValue(++$i, $columnValue);
            }
        }

        try {
            if ($stmt->execute()) {
                return $this->_db->commit();
            }
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }
        return false;
    }

    /**
     * Merge data to db table
     *
     * @throws Exception
     * @return boolean
     */
    public function merge($data, $tableName)
    {
        $row = current($data);
        $columns = array_keys($row);

        $values = '(' . join(', ', array_fill(0, count($columns), '?')) . ')';

        $sql = "INSERT INTO {$tableName} (" .join(', ', $columns) . ") VALUES {$values}";

        $this->_db->beginTransaction();

        try {
            foreach ($data as $row) {
                $stmt = $this->_db->prepare($sql);
                $stmt->execute(array_values($row));
            }
        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }

        return $this->_db->commit();
    }

    /**
     * Truncate table
     *
     * @param string $tableName
     * @return number
     */
    public function truncate($tableName)
    {
        return $this->_db->exec("DELETE FROM {$tableName}");
    }

    /**
     * Drop table
     *
     * @param string $tableName
     * @return number
     */
    public function dropTable($tableName)
    {
        return $this->_db->exec("DROP TABLE IF EXISTS {$tableName}");
    }

    /**
     * Drop trigger
     *
     * @param string $triggerName
     * @return number
     */
    public function dropTrigger($triggerName)
    {
        return $this->_db->exec("DROP TRIGGER IF EXISTS {$triggerName}");
    }

    /**
     * Is db table empty
     *
     * @param string $tableName
     * @return boolean
     */
    public function isEmpty($tableName)
    {
        $result = $this->_db->query("SELECT COUNT(*) FROM `{$tableName}`");
        return false == $result->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Has table a primary key
     *
     * @param string $tableName
     * @return boolen
     */
    protected function _hasPrimaryKey($tableName)
    {
        $result = $this->_db->query("SHOW INDEXES FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");
        return $result->rowCount() > 0;
    }

    /**
     * Get table indexes
     *
     * @param string $tableName
     * @return array
     */
    protected function _getIndexes($tableName)
    {
        $result = $this->_db->query("SHOW INDEXES FROM `{$tableName}` WHERE Key_name != 'PRIMARY'");
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get column sql
     *
     * @param string $name
     * @param array $config
     * @param string $after
     * @return string
     */
    protected function _getColumnSql($name, $config, $after = null)
    {
        $query = "`{$name}` {$config['type']}";
        if (empty($config['nullable'])) {
            $query .= " NOT NULL";
            if (!empty($config['default'])) {
                $query .= " DEFAULT {$config['default']}";
            }
        } else {
            $query .= " DEFAULT NULL";
        }
        if (!empty($config['comment'])) {
            $query .= " COMMENT {$config['comment']}";
        }

        if (!empty($config['unsigned'])) {
            $query .= " UNSIGNED";
        }

        if (!empty($config['autoincrement'])) {
            $query .= " AUTO_INCREMENT";
        }

        if ($after) {
            $query .= " AFTER " . $after;
        }

        return $query;
    }
}