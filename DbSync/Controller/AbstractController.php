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
 * @package  DbSync_Controller
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Controller_AbstractController
 *
 * @category DbSync
 * @package  DbSync_Controller
 * @version  $Id$
 */
abstract class DbSync_Controller_AbstractController
{
    /**
     * @var string
     */
    protected $_modelClass;

    /**
     * @var DbSync_Table_AbstractTable
     */
    protected $_model;

    /**
     * @var DbSync_Console
     */
    protected $_console;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (empty($config['dbAdapter'])) {
            throw new Exception('Db adapter not set');
        }

        $db = new $config['dbAdapter']($config['dbParams']);
        $file = new $config['fileAdapter']($config['path']);

        $this->_model = new $this->_modelClass(
            $db,
            $file,
            null,
            $config['diffprog']
        );

        echo PHP_EOL;
    }

    /**
     * Dispatch
     *
     * @param DbSync_Console $console
     * @return mixed
     */
    public function dispatch(DbSync_Console $console)
    {
        $this->_console = $console;

        $action = $this->_console->getAction() . 'Action';

        if (!method_exists($this, $action)) {
            $action = 'helpAction';
        }

        $actions = $console->getActions();
        unset($actions['0']);

        return $this->{$action}($actions);
    }

    /**
     * Descructor
     *
     */
    public function __destruct()
    {
        echo PHP_EOL;
    }

    /**
     * Help action
     */
    abstract function helpAction();


    /**
     * Push
     *
     * @param array $tables
     */
    public function pushAction($tables = null)
    {
        if (!$tables) {
            $tables = $this->_model->getFileTableList();
        }
        foreach ($tables as $tableName) {
            $this->_model->setTableName($tableName);
            $this->push();
        }

        if (!$tables) {
            echo $this->colorize("No configs found", 'red');
        }

        $updated = true;
        $stop = false;

         while ($tables && !$stop) {
            $stop = !$updated;

            $updated = false;

            foreach ($tables as $i => $tableName) {
                $this->_model->setTableName($tableName);

                try {
                    $this->push();
                    unset($tables[$i]);
                    $updated = true;
                } catch (Exception $e) {
                    if ($stop) {
                        echo $tableName . $this->colorize(" - " . $e->getMessage(), 'red');
                    }
                }
            }
        }
    }

    /**
     * Status
     *
     * @param array $tables
     */
    public function statusAction($tables = null)
    {
        if (!$tables) {
            $tables = $this->_model->getTableList();
        }
        foreach ($tables as $tableName) {
            $this->_model->setTableName($tableName);
            $this->status();
        }
    }

    /**
     * Init
     *
     * @param array $tables
     */
    public function initAction($tables = null)
    {
        if (!$tables) {
            $tables = $this->_model->getDbTableList();
        }
        foreach ($tables as $tableName) {
            $this->_model->setTableName($tableName);
            $this->init();
        }
    }

    /**
     * Pull
     *
     * @param array $tables
     */
    public function pullAction($tables = null)
    {
        if (!$tables) {
            $tables = $this->_model->getDbTableList();
        }
        foreach ($tables as $tableName) {
            $this->_model->setTableName($tableName);
            $this->pull();
        }
    }

    /**
     * Diff
     *
     * @param array $tables
     */
    public function diffAction($tables = null)
    {
        if (!$tables) {
            $tables = $this->_model->getTableList();
        }
        foreach ($tables as $tableName) {
            $this->_model->setTableName($tableName);
            $this->diff();
        }
    }

    /**
     * Colorize
     *
     * @param string $text
     * @param string $color
     * @return string
     */
    public function colorize($text, $color = 'yellow')
    {
        switch ($color) {
            case 'red':
                $color = "1;31m";
                break;
            case 'green':
                $color = "1;32m";
                break;
            case 'blue':
                $color = "1;34m";
                break;
            case 'white':
                $color = "1;37m";
                break;
            default:
                $color = "1;33m";
                break;
        }
        return "\033[" . $color . $text . "\033[m";
    }
}