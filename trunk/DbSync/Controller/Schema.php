<?php

class DbSync_Controller_Schema extends DbSync_Controller
{
    /**
     * Push
     *
     * @param string $tableName
     * @param array $options
     */
    public function push($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($schema->getSchemaTableList() as $tableName) {
                $this->push($tableName);
            }
            return;
        }

        if ($schema->hasFileSchema()) {
            if ($this->_console->hasOption('show')) {
                echo $schema->generateAlter();
            } else {
                 $schema->push();

                 echo "{$tableName} - updated";
            }
        } else {
            echo "Schema for '{$tableName}' not found";
        }
        echo PHP_EOL;
    }

    /**
     * Status
     *
     * @param string $tableName
     */
    public function status($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($schema->getTableList() as $tableName) {
                $this->status($tableName);
            }
            return;
        }

        if ($schema->hasDbTable() && $schema->hasFileSchema()) {
            if ($schema->getStatus()) {
                echo "'{$tableName}' - OK";
            } else {
                echo "'{$tableName}' - Unsyncronized";
            }
        } else {
            if (!$schema->hasDbTable()) {
                echo "Table '{$tableName}' not exists";
            } else {
                echo "Schema for '{$tableName}' not found";
            }
        }
        echo PHP_EOL;
    }

    /**
     * Init
     *
     * @param string $tableName
     */
    public function init($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($schema->getDbTableList() as $tableName) {
                $this->init($tableName);
            }
            return;
        }

        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);
        if ($schema->hasDbTable()) {
            if ($schema->hasFileSchema()) {
                echo "Table '{$tableName}' already has schema";
            } else {
                if ($schema->isWriteable()) {
                    $schema->init();
                    echo "'{$tableName}' - OK";
                } else {
                    echo "Schema path for '{$tableName}' is not writeable";
                }
            }
        } else {
            echo "Table '{$tableName}' not found";
        }
        echo PHP_EOL;
    }

    /**
     * Pull
     *
     * @param string $tableName
     */
    public function pull($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($schema->getDbTableList() as $tableName) {
                $this->pull($tableName);
            }
            return;
        }

        if ($schema->hasDbTable()) {
            if ($schema->isWriteable()) {
                $schema->pull();
                echo "'{$tableName}' - OK";
            } else {
                echo "Schema path for '{$tableName}' is not writeable";
            }
        } else {
            echo "Table '{$tableName}' not found";
        }
        echo PHP_EOL;
    }

    /**
     * Diff
     *
     * @param string $tableName
     */
    public function diff($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($schema->getTableList() as $tableName) {
                $this->diff($tableName);
            }
            return;
        }

        if ($schema->hasDbTable() && $schema->hasFileSchema()) {
            if (!$schema->getStatus()) {
                echo join(PHP_EOL, $schema->diff()), PHP_EOL;
            }
        } else {
            if (!$schema->hasDbTable()) {
                echo "Table '{$tableName}' not exists";
            } else {
                echo "Schema for '{$tableName}' not found";
            }
            echo PHP_EOL;
        }
    }

    /**
     * Help
     *
     * @see DbSync_Controller::help()
     */
    public function help()
    {
        echo 'help';
        echo PHP_EOL;
    }
}