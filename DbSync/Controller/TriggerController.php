<?php
/**
 * DbSync_Controller_TriggerController
 *
 * @version $Id$
 */
class DbSync_Controller_TriggerController extends DbSync_Controller_AbstractController
{
    /**
    * @var string
    */
    protected $_modelClass = 'DbSync_Table_Trigger';

    /**
     * @var DbSync_Table_Trigger
     */
    protected $_model;

    /**
     * Push
     *
     * @param array $triggers
     */
    public function pushAction($triggers = null)
    {
        if (!$triggers) {
            $triggers = $this->_model->getFileTriggerList();
        }
        foreach ($triggers as $triggerName) {
            $this->_model->setTableName(null);
            $this->_model->setTriggerName($triggerName);
            $this->push();
        }
    }

    /**
     * Status
     *
     * @param array $triggers
     */
    public function statusAction($triggers = null)
    {
        if (!$triggers) {
            $triggers = $this->_model->getTriggerList();
        }
        foreach ($triggers as $triggerName) {
            $this->_model->setTableName(null);
            $this->_model->setTriggerName($triggerName);
            $this->status();
        }
    }

    /**
     * Init
     *
     * @param array $triggers
     */
    public function initAction($triggers = null)
    {
        if (!$triggers) {
            $triggers = $this->_model->getDbTriggerList();
        }
        foreach ($triggers as $triggerName) {
            $this->_model->setTableName(null);
            $this->_model->setTriggerName($triggerName);
            $this->init();
        }
    }

    /**
     * Pull
     *
     * @param array $triggers
     */
    public function pullAction($triggers = null)
    {
        if (!$triggers) {
            $triggers = $this->_model->getDbTriggerList();
        }
        foreach ($triggers as $triggerName) {
            $this->_model->setTableName(null);
            $this->_model->setTriggerName($triggerName);
            $this->pull();
        }
    }

    /**
     * Diff
     *
     * @param array $triggers
     */
    public function diffAction($triggers = null)
    {
        if (!$triggers) {
            $triggers = $this->_model->getTriggerList();
        }
        foreach ($triggers as $triggerName) {
            $this->_model->setTableName(null);
            $this->_model->setTriggerName($triggerName);
            $this->diff();
        }
    }

    /**
     * Delete
     *
     * @param array $triggers
     */
    public function deleteAction($triggers = null)
    {
        if (!$triggers) {
            $triggers = $this->_model->getTableList();
        }
        foreach ($triggers as $triggerName) {
            $this->_model->setTableName(null);
            $this->_model->setTriggerName($triggerName);
            $this->delete();
        }
    }

    /**
     * Help
     *
     * @see DbSync_Controller::help()
     */
    public function helpAction()
    {
        echo "Usage {$this->_console->getProgname()} [action] [ [triggerName] ... ] [--option]", PHP_EOL;

        echo PHP_EOL;

        echo $this->colorize("if trigger not specified action applied to all triggers/configs"), PHP_EOL;

        echo PHP_EOL;

        echo "Actions:", PHP_EOL;

        echo $this->colorize("init", 'green');
        echo "     Create database trigger config in specified path", PHP_EOL;

        echo $this->colorize("status", 'green');
        echo "   Check triggers status (Ok/Unsyncronized)", PHP_EOL;

        echo $this->colorize("diff", 'green');
        echo "     Show diff between database trigger and config file", PHP_EOL;

        echo $this->colorize("pull", 'green');
        echo "     Override current trigger config file by new created from database.", PHP_EOL;

        echo $this->colorize("push", 'green');
        echo "     Override database trigger by current config file", PHP_EOL;
        echo "         Use {$this->colorize('--show')} to only display sql code", PHP_EOL;

        echo $this->colorize("help", 'green');
        echo "     help message", PHP_EOL;

        echo PHP_EOL;
    }

    /**
     * Delete
     *
     */
    public function delete()
    {
        $tableName = $this->_model->getTableName();
        $triggerName = $this->_model->getTriggerName();

        if ($this->_model->hasFile() && !$this->_console->hasOption('db')) {
            if ($this->_model->isWriteable()) {
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
     * Push
     *
     */
    public function push()
    {
        $tableName = $this->_model->getTableName();
        $triggerName = $this->_model->getTriggerName();

        if ($this->_model->hasFile()) {
            if ($this->_console->hasOption('show')) {
                echo $this->_model->createSql();
            } else {
                 echo $triggerName . $this->colorize(" - Updated", 'green');
            }
        } else {
            echo $triggerName . $this->colorize(" - Config not found", 'red');
        }
        echo PHP_EOL;
    }

    /**
     * Status
     *
     */
    public function status()
    {
        $tableName = $this->_model->getTableName();
        $triggerName = $this->_model->getTriggerName();

        if ($this->_model->hasDbTrigger() && $this->_model->hasFile()) {
            if ($this->_model->getStatus()) {
                echo $triggerName . $this->colorize(" - Ok", 'green');
            } else {
                echo $triggerName . $this->colorize(" - Unsyncronized", 'red');
            }
        } else {
            if (!$this->_model->hasDbTrigger()) {
                echo $triggerName . $this->colorize(" - Trigger not found", 'red');
            } else {
                echo $triggerName . $this->colorize(" - Config not found", 'red');
            }
        }
        echo PHP_EOL;
    }

    /**
     * Init
     *
     */
    public function init()
    {
        $tableName = $this->_model->getTableName();
        $triggerName = $this->_model->getTriggerName();

        if ($this->_model->hasDbTrigger()) {
            if ($this->_model->hasFile()) {
                echo $triggerName . $this->colorize(" - Already has data", 'red');
            } else {
                if ($this->_model->isWriteable()) {
                    $this->_model->init();
                    echo $triggerName . $this->colorize(" - Ok", 'green');
                } else {
                    echo $triggerName . $this->colorize(" - Path is not writeable", 'red');
                }
            }
        } else {
            echo $triggerName . $this->colorize(" - Trigger not found", 'red');
        }
        echo PHP_EOL;
    }

    /**
     * Pull
     *
     */
    public function pull()
    {
        $tableName = $this->_model->getTableName();
        $triggerName = $this->_model->getTriggerName();

        if ($this->_model->hasDbTrigger()) {
            if ($this->_model->isWriteable()) {
                $this->_model->pull();
                echo $triggerName . $this->colorize(" - Ok", 'green');
            } else {
                echo $triggerName . $this->colorize(" - Path is not writeable", 'red');
            }
        } else {
            echo $triggerName . $this->colorize(" - Trigger not found", 'red');
        }
        echo PHP_EOL;
    }

    /**
     * Diff
     *
     */
    public function diff()
    {
        $tableName = $this->_model->getTableName();
        $triggerName = $this->_model->getTriggerName();

        if ($this->_model->hasDbTrigger() && $this->_model->hasFile()) {
            if (!$this->_model->getStatus()) {
                echo join(PHP_EOL, $this->_model->diff()), PHP_EOL;
            }
        } else {
            if (!$this->_model->hasDbTrigger()) {
                echo $triggerName . $this->colorize(" - Trigger not found", 'red');
            } else {
                echo $triggerName . $this->colorize(" - Config not found", 'red');
            }
            echo PHP_EOL;
        }
    }
}