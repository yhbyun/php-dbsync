<?php
/**
 * DbSync_Controller_DataController
 *
 * @version $Id$
 */
class DbSync_Controller_DataController extends DbSync_Controller_AbstractController
{
    /**
     * @var string
     */
    protected $_modelClass = 'DbSync_Table_DataTable';

    /**
     * Merge
     *
     * @param array $tables
     */
    public function mergeAction($tables = null)
    {
        if (!$tables) {
            $tables = $this->_model->getFileTableList();
        }
        foreach ($tables as $tableName) {
            $this->_model->setTableName($tableName);
            $this->merge();
        }
    }

    /**
     * Help
     *
     */
    public function helpAction()
    {
        echo "Usage {$this->_console->getProgname()} [action] [ [tableName] ... ] [--option]", PHP_EOL;

        echo PHP_EOL;

        echo $this->colorize("if tableName not specified action applied to all tables/configs"), PHP_EOL;

        echo PHP_EOL;

        echo "Actions:", PHP_EOL;

        echo $this->colorize("init", 'green');
        echo "     Create database data config in specified path", PHP_EOL;

        echo $this->colorize("status", 'green');
        echo "   Check data status (Ok/Unsyncronized)", PHP_EOL;

        echo $this->colorize("diff", 'green');
        echo "     Show diff between database table data and data config file", PHP_EOL;

        echo $this->colorize("pull", 'green');
        echo "     Override current data config file by new created from database data", PHP_EOL;

        echo $this->colorize("push", 'green');
        echo "     Override database data by current data config file", PHP_EOL;
        echo "         Use {$this->colorize('--force')} to truncate table first", PHP_EOL;

        echo $this->colorize("help", 'green');
        echo "     help message", PHP_EOL;
    }


    /**
     * Push
     *
     */
    public function push()
    {
        $tableName = $this->_model->getTableName();

        if ($this->_model->hasFile()) {
            $force = $this->_console->hasOption('force');
            if (!$force && !$this->_model->isEmptyTable()) {
                echo $tableName . $this->colorize(" - is dirty use --force for cleanup or try merge instead of push");
            } else {
                if ($this->_model->push($force)) {
                    echo $tableName . $this->colorize(" - Updated", 'green');
                } else {
                    echo $tableName . $this->colorize(' - Error occured');
                }
            }
        } else {
            echo $tableName . $this->colorize(" - Data not found", 'red');
        }
        echo PHP_EOL;
    }

    /**
     * Merge
     *
     */
    public function merge()
    {
        $tableName = $this->_model->getTableName();

        if ($this->_model->hasFile()) {
            if ($this->_model->isEmptyTable()) {
                echo $tableName . $this->colorize(' - is empty use push instead', 'red');
            } else {
                $this->_model->merge();
                echo $tableName . $this->colorize(" - Updated", 'green');
            }
        } else {
            echo $tableName . $this->colorize(" - Data not found", 'red');
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

        if ($this->_model->hasDbTable() && $this->_model->hasFile()) {
            if ($this->_model->getStatus()) {
                echo $tableName . $this->colorize(" - OK", 'green');
            } else {
                echo $tableName . $this->colorize(' - Unsyncronized');
            }
        } else {
            if (!$this->_model->hasDbTable()) {
                echo $tableName . $this->colorize(" - Table not exists", 'red');
            } else {
                echo $tableName . $this->colorize(" - Data not found", 'red');
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

        if ($this->_model->hasDbTable()) {
            if ($this->_model->hasFile()) {
                echo $tableName . $this->colorize(' - Table already has data', 'red');
            } else {
                if ($this->_model->isWriteable()) {
                    $this->_model->init();
                    echo $tableName . $this->colorize(" - OK", 'green');
                } else {
                    echo $tableName . $this->colorize(" - Path is not writeable", 'green');
                }
            }
        } else {
            echo $tableName . $this->colorize(" - Table not exists", 'red');
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

        if ($this->_model->hasDbTable()) {
            if ($this->_model->isWriteable()) {
                $this->_model->pull();
                echo $tableName . $this->colorize(" - OK", 'green');
            } else {
                echo $tableName . $this->colorize(" - Path is not writeable", 'green');
            }
        } else {
            echo $tableName . $this->colorize(" - Table not exists", 'red');
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

        if ($this->_model->hasDbTable() && $this->_model->hasFile()) {
            if ($this->_model->getStatus()) {
                echo $tableName . $this->colorize(" - OK", 'green');
            } else {
                echo join(PHP_EOL, $this->_model->diff());
            }
        } else {
            if (!$this->_model->hasDbTable()) {
                echo $tableName . $this->colorize(" - Table not exists", 'red');
            } else {
                echo $tableName . $this->colorize(" - Data not found", 'red');
            }
        }
        echo PHP_EOL;
    }
}