<?php

class DbSync_Controller_Schema extends DbSync_Controller
{
    /**
     * Push
     *
     * @param string $tableName
     * @param array $options
     */
    public function pushAction($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($schema->getSchemaTableList() as $tableName) {
                $this->pushAction($tableName);
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
    public function statusAction($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($schema->getTableList() as $tableName) {
                $this->statusAction($tableName);
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
    public function initAction($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($schema->getDbTableList() as $tableName) {
                $this->initAction($tableName);
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
    public function pullAction($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($schema->getDbTableList() as $tableName) {
                $this->pullAction($tableName);
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
    public function diffAction($tableName = null)
    {
        $schema = new DbSync_Table_Schema($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($schema->getTableList() as $tableName) {
                $this->diffAction($tableName);
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
    public function helpAction()
    {
        echo 'help';
        echo PHP_EOL;
    }
}