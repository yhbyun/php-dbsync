<?php
/**
 * DbSync_Controller_Data
 *
 * @version $Id$
 */
class DbSync_Controller_Data extends DbSync_Controller
{
    /**
     * Push
     *
     * @param string $tableName
     */
    public function pushAction($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($data->getFileTableList() as $tableName) {
                $this->pushAction($tableName);
            }
            return;
        }

        if ($data->hasFile()) {
            $force = $this->_console->hasOption('force');
            if (!$force && !$data->isEmptyTable()) {
                echo $tableName . $this->colorize("is dirty use --force for cleanup or try merge instead of push");
            } else {
                if ($data->push($force)) {
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
     * @param string $tableName
     */
    public function mergeAction($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($data->getFileTableList() as $tableName) {
                $this->mergeAction($tableName, $options);
            }
            return;
        }
        if ($data->hasFile()) {
            if ($data->isEmptyTable()) {
                echo $tableName . $this->colorize(' - is empty use push instead', 'red');
            } else {
                $data->merge();
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
     * @param string $tableName
     */
    public function statusAction($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getTableList() as $tableName) {
                $this->statusAction($tableName);
            }
            return;
        }

        if ($data->hasDbTable() && $data->hasFile()) {
            if ($data->getStatus()) {
                echo $tableName . $this->colorize(" - OK", 'green');
            } else {
                echo $tableName . $this->colorize(' - Unsyncronized');
            }
        } else {
            if (!$data->hasDbTable()) {
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
     * @param string $tableName
     */
    public function initAction($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getDbTableList() as $tableName) {
                $this->initAction($tableName);
            }
            return;
        }

        if ($data->hasDbTable()) {
            if ($data->hasFile()) {
                echo $tableName . $this->colorize(' - Table already has data', 'red');
            } else {
                if ($data->isWriteable()) {
                    $data->init();
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
     * @param string $tableName
     */
    public function pullAction($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getDbTableList() as $tableName) {
                $this->pullAction($tableName);
            }
            return;
        }

        if ($data->hasDbTable()) {
            if ($data->isWriteable()) {
                $data->pull();
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
     * @param string $tableName
     */
    public function diffAction($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getTableList() as $tableName) {
                $this->diffAction($tableName);
            }
            return;
        }

        if ($data->hasDbTable() && $data->hasFile()) {
            if ($data->getStatus()) {
                echo $tableName . $this->colorize(" - OK", 'green');
            } else {
                echo join(PHP_EOL, $data->diff());
            }
        } else {
            if (!$data->hasDbTable()) {
                echo $tableName . $this->colorize(" - Table not exists", 'red');
            } else {
                echo $tableName . $this->colorize(" - Data not found", 'red');
            }
        }
        echo PHP_EOL;
    }

    /**
     * Help
     *
     */
    public function helpAction()
    {
        echo "Usage {$this->_console->getProgname()} [action] [tableName] ", PHP_EOL;

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

        echo $this->colorize("help", 'green');
        echo "     help message", PHP_EOL;
    }
}