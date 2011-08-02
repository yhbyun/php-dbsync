<?php
/**
 * DbSync_Table_Adapter_Mysql
 *
 * @version $Id$
 */
class DbSync_Table_Adapter_Mysql
    implements DbSync_Table_Adapter_AdapterInterface
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
        $this->_db = new PDO(
            $config['dns'],
            $config['username'],
            $config['password'],
            $config['options']
        );
    }

    /**
     * Generate schema
     *
     * @return array
     */
    public function generateSchema($tableName)
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
    public function generateAlter($config, $tableName)
    {
        $query = array();
        if (!$this->hasDbTable($tableName)) {
            foreach ($config['columns'] as $columnName => $columnConfig) {
                $query[] = $this->_getColumnSql($columnName, $columnConfig);
            }
            $query = "CREATE TABLE `{$tableName}` (" . PHP_EOL
                   . join(',' . PHP_EOL, $query) . PHP_EOL
                   . ") ENGINE={$config['engine']} CHARSET={$config['charset']}";

        } else {
            $result = $this->_db->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = $result->fetchAll(PDO::FETCH_ASSOC);

            $query[]= "ALTER TABLE `{$tableName}` ENGINE={$config['engine']}, CHARSET={$config['charset']}";
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

                $query[] = "{$action} COLUMN " . $this->_getColumnSql($columnName, $columnConfig);
            }
            foreach ($columns as $columnDesc) {
                $query[] = "DROP COLUMN {$columnDesc['Field']}";
            }
            $query = join(',' . PHP_EOL, $query);
        }

        return $query;
    }

    /**
     * Alter db table
     *
     * @param string $sql
     * @return integer
     */
    public function execute($sql)
    {
        return $this->_db->exec($sql);
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
     * Get column sql
     *
     * @param string $name
     * @param Zend_Config $config
     * @return string
     */
    protected function _getColumnSql($name, $config)
    {
        $query = "`{$name}` {$config->type}";
        if (!$config->nullable) {
            $query .= " NOT NULL";
            if ($config->default) {
                $query .= " DEFAULT {$config->default}";
            }
        } else {
            $query .= " DEFAULT NULL";
        }
        if ($config->comment) {
            $query .= " COMMENT {$config->comment}";
        }

        if ($config->unsigned) {
            $query .= " UNSIGNED";
        }

        if ($config->autoincrement) {
            $query .= " AUTO_INCREMENT";
        }

        return $query;
    }

    /**
     * Is db table exists
     *
     * @return boolen
     */
    public function hasDbTable($tableName)
    {
        $result = $this->_db->query("SHOW TABLES LIKE '{$tableName}'");
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
     * Is db table dirty
     *
     * @param string $tableName
     * @return boolean
     */
    public function isDirtyDbTable($tableName)
    {
        $result = $this->_db->query("SELECT COUNT(*) FROM {$tableName}");
        return (bool) $result->fetch(PDO::FETCH_COLUMN);
    }
}