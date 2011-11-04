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
 * @package    DbSync_DbAdapter
 * @license    http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * DbSync_DbAdapter_Mysql
 *
 * @category   DbSync
 * @package    DbSync_DbAdapter
 * @version    $Id$
 */
abstract class DbSync_DbAdapter_Pdo_AbstractAdapter
    implements DbSync_DbAdapter_AdapterInterface
{
    /**
     * @var PDO
     */
    protected $_db;

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
     * @return DbSync_DbAdapter_Pdo_AbstractAdapter
     */
    public function setConnection(PDO $connection)
    {
        $this->_db = $connection;
        return $this;
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
     * Fetch all data from table
     *
     * @param string $tableName
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
     * @param array  $data
     * @param string $tableName
     * @return boolen
     */
    public function insert(array $data, $tableName)
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
     * @param array  $data
     * @param string $tableName
     * @return boolean
     */
    public function merge(array $data, $tableName)
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
        return $this->execute("TRUNCATE TABLE {$tableName}");
    }

    /**
     * Drop table
     *
     * @param string $tableName
     * @return number
     */
    public function dropTable($tableName)
    {
        return $this->execute("DROP TABLE IF EXISTS {$tableName}");
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
}