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
 * @package  DbSync_Controller
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Controller_TriggerController
 *
 * @category DbSync
 * @package  DbSync_Controller
 * @version  $Id$
 */
class DbSync_Controller_TriggerController extends DbSync_Controller_AbstractController
{
    /**
    * @var string
    */
    protected $_modelClass = 'DbSync_Model_Table_Trigger';

    /**
     * @var DbSync_Model_Table_Trigger
     */
    protected $_model;

    /**
     * Run action
     *
     * @param string $action
     * @param string $name
     */
    protected function _run($action, $name)
    {
        $this->_model->setTriggerName($name);
        $this->{$action}();
    }

   /**
     * Get items list
     *
     * @param string $action
     * @return array
     */
    public function getItemsList($action)
    {
        $tables = $this->_console->getOption('table');
        if (!$tables) {
            $tables = array();
        }

        $tables = (array) $tables;

        switch ($action) {
            case 'pushAction':
            case 'mergeAction':
                $items = $this->_model->getListConfig($tables);
                break;
            case 'initAction':
            case 'pullAction':
                $items = $this->_model->getListDb($tables);
                break;
            case 'diffAction':
            case 'deleteAction':
            case 'statusAction':
                $items = $this->_model->getList($tables);
                break;
            default:
                $items = array();
        }
        return $items;
    }

    /**
     * Help
     *
     * @return Help message
     * @see DbSync_Controller::help()
     */
    public function helpAction()
    {
        echo "Usage {$this->_console->getProgname()} [action] [ [triggerName] ... ] [--option]", PHP_EOL;

        echo PHP_EOL;

        echo $this->colorize("if trigger not specified action applied to all triggers/configs"), PHP_EOL;

        echo PHP_EOL;

        $this->showUsage();
    }

    /**
     * Delete
     *
     * @alias de
     *
     * @return Delete trigger and config
     * @return Use {--db|yellow} to delete only from database
     * @return Use {--file|yellow} to delete only config file
     */
    public function deleteAction()
    {
        $triggerName = $this->_model->getTriggerName();

        if ($this->_model->hasFile() && !$this->_console->hasOption('db')) {
            if ($this->_model->isWritable()) {
                $this->_model->deleteFile();
                echo $triggerName . $this->colorize(" - File deleted", 'green');
            } else {
                echo $triggerName . $this->colorize(" - Path is not writeable", 'red');
            }
            echo PHP_EOL;
        }
        if ($this->_model->hasDbTrigger() && !$this->_console->hasOption('file')) {
            $this->_model->dropTrigger();
            echo $triggerName . $this->colorize(" - Database trigger deleted", 'green');

            echo PHP_EOL;
        }
    }

    /**
     * Status
     *
     * @alias st
     *
     * @return Check triggers status (Ok/Unsynchronized)
     * @return Use {--table [[tableName] ... ]|yellow} to display triggers for certain table(s)
     */
    public function statusAction()
    {
        parent::statusAction();
    }
}