<?php
/**
 * DbSync_Controller_SchemaController
 *
 * @version $Id$
 */
class DbSync_Controller_SchemaController extends DbSync_Controller_AbstractController
{
    /**
    * @var string
    */
    protected $_modelClass = 'DbSync_Table_SchemaTable';

    /**
     * Delete
     *
     * @param array $tables
     */
    public function deleteAction($tables = null)
    {
        if (!$tables) {
            $tables = $this->_model->getTableList();
        }
        foreach ($tables as $tableName) {
            $this->_model->setTableName($tableName);
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
        echo "Usage {$this->_console->getProgname()} [action] [ [tableName] ... ] [--option]", PHP_EOL;

        echo PHP_EOL;

        echo $this->colorize("if tableName not specified action applied to all tables/configs"), PHP_EOL;

        echo PHP_EOL;

        echo "Actions:", PHP_EOL;

        echo $this->colorize("init", 'green');
        echo "     Create database schema config in specified path", PHP_EOL;

        echo $this->colorize("status", 'green');
        echo "   Check schema status (Ok/Unsyncronized)", PHP_EOL;

        echo $this->colorize("diff", 'green');
        echo "     Show diff between database table schema and schema config file", PHP_EOL;

        echo $this->colorize("pull", 'green');
        echo "     Override current schema config file by new created from database.", PHP_EOL;
        echo "         Use {$this->colorize('--show')} to only display alter code", PHP_EOL;

        echo $this->colorize("push", 'green');
        echo "     Override database schema by current schema config file", PHP_EOL;

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

        if ($this->_model->hasFile() && !$this->_console->hasOption('table')) {
            if ($this->_model->isWriteable()) {
                $this->_model->deleteFile();
                echo $tableName . $this->colorize(" - File deleted", 'green');
            } else {
                echo $tableName . $this->colorize(" - Path is not writeable", 'red');
            }
        }
        if ($this->_model->hasDbTable() && !$this->_console->hasOption('file')) {
            $this->_model->deleteDbTable();
            echo $tableName . $this->colorize(" - Database table deleted", 'green');
        }
        echo PHP_EOL;
    }


    /**
     * Push
     *
     */
    public function push()
    {
        $tableName = $this->_model->getTableName();

        if ($this->_model->hasFile()) {
            if ($this->_console->hasOption('show')) {
                echo $this->_model->createAlter();
            } else {
                 if (!$this->_model->push()) {
                     throw new Exception('Table not updated');
                 }

                 echo $tableName . $this->colorize(" - Updated", 'green');
            }
        } else {
            echo $tableName . $this->colorize(" - Schema not found", 'red');
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
                echo $tableName . $this->colorize(" - Ok", 'green');
            } else {
                echo $tableName . $this->colorize(" - Unsyncronized", 'red');
            }
        } else {
            if (!$this->_model->hasDbTable()) {
                echo $tableName . $this->colorize(" - Table not found", 'red');
            } else {
                echo $tableName . $this->colorize(" - Schema not found", 'red');
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
                echo $tableName . $this->colorize(" - Already has data", 'red');
            } else {
                if ($this->_model->isWriteable()) {
                    $this->_model->init();
                    echo $tableName . $this->colorize(" - Ok", 'green');
                } else {
                    echo $tableName . $this->colorize(" - Path is not writeable", 'red');
                }
            }
        } else {
            echo $tableName . $this->colorize(" - Table not found", 'red');
        }
        echo PHP_EOL;
    }

    /**
     * Pull
     *
     */
    public function pull($tableName = null)
    {
        $tableName = $this->_model->getTableName();

        if ($this->_model->hasDbTable()) {
            if ($this->_model->isWriteable()) {
                $this->_model->pull();
                echo $tableName . $this->colorize(" - Ok", 'green');
            } else {
                echo $tableName . $this->colorize(" - Path is not writeable", 'red');
            }
        } else {
            echo $tableName . $this->colorize(" - Table not found", 'red');
        }
        echo PHP_EOL;
    }

    /**
     * Diff
     *
     */
    public function diff($tableName = null)
    {
        $tableName = $this->_model->getTableName();

        if ($this->_model->hasDbTable() && $this->_model->hasFile()) {
            if (!$this->_model->getStatus()) {
                echo join(PHP_EOL, $this->_model->diff()), PHP_EOL;
            }
        } else {
            if (!$this->_model->hasDbTable()) {
                echo $tableName . $this->colorize(" - Table not found", 'red');
            } else {
                echo $tableName . $this->colorize(" - Schema not found", 'red');
            }
            echo PHP_EOL;
        }
    }
}