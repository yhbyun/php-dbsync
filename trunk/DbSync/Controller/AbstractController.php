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
     * @var DbSync_Model_AbstractModel
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
            throw new DbSync_Exception('Db adapter not set');
        }

        if (empty($config['fileAdapter'])) {
            throw new DbSync_Exception('File adapter not set');
        }

        $db = new $config['dbAdapter']($config['dbParams']);
        $file = new $config['fileAdapter']($config['path']);

        $this->_model = new $this->_modelClass($db, $file, $config['diffprog']);

        echo PHP_EOL;
    }

    /**
     * Get action method name
     *
     * @param string $action
     * @return string
     */
    public function getActionMethod($action)
    {
        if (method_exists($this, $action . 'Action')) {
            return $action . 'Action';
        }
        foreach (get_class_methods($this) as $methodName) {
            if ('Action' == substr($methodName, -6)) {
                $method = new ReflectionMethod($this, $methodName);

                preg_match_all(
                    "%\@alias\s(.*)(\r\n|\r|\n)%um",
                    $method->getDocComment(),
                    $matches
                );

                if ($matches['1']) {
                    foreach ($matches['1'] as $match) {
                        if ($action == $match) {
                            return $methodName;
                        }
                    }
                }
            }
        }
        return 'helpAction';
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

        $items = $console->getArguments();

        $action = $this->getActionMethod(array_shift($items));

        if ('helpAction' == $action) {
            return $this->helpAction();
        }

        if (!$items) {
            $items = $this->getItemsList($action);
        }

        if (!$items) {
            echo $this->colorize("Nothing to sync", 'red');
            return;
        }

        $updated = true;
        $stop = false;

        while ($items && !$stop) {
            $stop = !$updated;

            $updated = false;

            foreach ($items as $i => $name) {
                try {
                    $this->_run($action, $name);

                    unset($items[$i]);
                    $updated = true;
                    echo PHP_EOL;
                } catch (DbSync_Exception $e) {
                    if ($stop) {
                        echo $name . $this->colorize(" - " . $e->getMessage(), 'red');
                        echo PHP_EOL;
                    }
                }
            }
        }
    }

    /**
     * Get items list
     *
     * @param string $action
     * @return array
     */
    public function getItemsList($action)
    {
        switch ($action) {
            case 'pushAction':
            case 'mergeAction':
                $items = $this->_model->getListConfig();
                break;
            case 'initAction':
            case 'pullAction':
                $items = $this->_model->getListDb();
                break;
            case 'diffAction':
            case 'deleteAction':
            case 'statusAction':
                $items = $this->_model->getList();
                break;
            default:
                $items = array();
        }
        return $items;
    }

    /**
     * Run action
     *
     * @param string $action
     * @param string $name
     */
    protected function _run($action, $name)
    {
        $this->_model->setTableName($name);
        $this->{$action}();
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
     *
     * @return Help message
     */
    public function helpAction()
    {
        echo "Usage {$this->_console->getProgname()} [action] [ [tableName] ... ] [--option]";

        echo PHP_EOL . PHP_EOL;

        echo $this->colorize("if tableName not specified action applied to all tables/configs");

        echo PHP_EOL . PHP_EOL;

        $this->showUsage();
    }

    /**
     * Output actions usage message
     *
     */
    public function showUsage()
    {
        echo "Actions:", PHP_EOL;

        $methods = get_class_methods($this);
        sort($methods);
        foreach ($methods as $methodName) {
            if ('Action' == substr($methodName, -6)) {
                echo $this->colorize(substr($methodName, 0, -6), 'green');

                $method = new ReflectionMethod($this, $methodName);

                preg_match_all(
                    "%\@return\s(.*)(\r\n|\r|\n)%um",
                    $method->getDocComment(),
                    $matches
                );

                if ($matches['1']) {
                    foreach ($matches['1'] as $match) {
                        $match = preg_replace(
                            '/\{(.+)\|(\w+)\}/i',
                            $this->colorize('$1', '$2'),
                            $match
                        );
                        echo "\t" . $match . PHP_EOL;
                    }
                } else {
                    echo "\t" . $this->colorize('No description', 'red') . PHP_EOL;
                }
            }
        }
    }

    /**
     * Status
     *
     * @alias st
     *
     * @return Check sync status (Ok/Unsyncronized)
     */
    public function statusAction()
    {
        $name = $this->_model->getName();

        if ($this->_model->getStatus()) {
            echo $name . $this->colorize(" - Ok", 'green');
        } else {
            echo $name . $this->colorize(" - Unsyncronized", 'red');
        }
    }

    /**
     * Init
     *
     * @return Create config file(s)
     */
    public function initAction()
    {
        $name = $this->_model->getName();

        if ($this->_model->init()) {
            echo $name . $this->colorize(" - Ok", 'green');
        } else {
            echo $name . $this->colorize(" - Already has config", 'red');
        }
    }

    /**
     * Pull
     *
     * @return Override current config(s) file by new created from database
     */
    public function pullAction()
    {
        $name = $this->_model->getName();

        $this->_model->pull();
        echo $name . $this->colorize(" - Ok", 'green');
    }

    /**
     * Diff
     *
     * @alias di
     *
     * @return Show diff between database entity and config file
     */
    public function diffAction()
    {
        $name = $this->_model->getName();

        if ($this->_model->getStatus()) {
            echo $name . $this->colorize(" - OK", 'green');
        } else {
            echo $name . $this->colorize(" - Unsyncronized", 'red');
            echo PHP_EOL, join(PHP_EOL, $this->_model->diff());
        }
    }

    /**
     * Push
     *
     * @return Override database entity by current config file entity
     * @return Use {--show|yellow} to only display alter code
     */
    public function pushAction()
    {
        $name = $this->_model->getName();

        if ($this->_console->hasOption('show')) {
            echo $this->_model->generateSql();
        } else {
             if (!$this->_model->push()) {
                 echo $name . $this->colorize(" - Not updated", 'red');
             } else {
                 echo $name . $this->colorize(" - Updated", 'green');
             }
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