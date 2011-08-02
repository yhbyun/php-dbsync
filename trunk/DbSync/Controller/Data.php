<?php

class DbSync_Controller_Data extends DbSync_Controller
{
    public function push($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($data->getDataTableList() as $tableName) {
                $this->push($tableName);
            }
            return;
        }

        if ($data->hasFileData()) {
            if ($this->_console->hasOption('show')) {
                //echo $data->generateAlter();
            } else {
                $force = $this->_console->hasOption('force');
                 if (!$force && $data->isDirtyDb()) {
                     echo "{$tableName} - is dirty use --force for cleanup or try merge instead of push";
                 } else {
                     if ($data->push($force)) {
                         echo "{$tableName} - updated";
                     } else {
                         echo "{$tableName} - error occured";
                     }
                 }
            }
        } else {
            echo "Data for '{$tableName}' not found";
        }
        echo PHP_EOL;
    }

    public function merge($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);
        if (!$tableName) {
            foreach ($data->getDataTableList() as $tableName) {
                $this->merge($tableName, $options);
            }
            return;
        }
        if ($data->hasFileData()) {
             if (!$data->isDirtyDb()) {
                 echo "{$tableName} - is not dirty use push instead";
             } else {
                 $data->merge();
                 echo "{$tableName} - updated";
             }
        } else {
            echo "Data for '{$tableName}' not found";
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
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getTableList() as $tableName) {
                $this->status($tableName);
            }
            return;
        }

        if ($data->hasDbTable() && $data->hasFileData()) {
            if ($data->getStatus()) {
                echo "'{$tableName}' - OK";
            } else {
                echo "'{$tableName}' - Unsyncronized";
            }
        } else {
            if (!$data->hasDbTable()) {
                echo "Table '{$tableName}' not exists";
            } else {
                echo "Data for '{$tableName}' not found";
            }
        }
        echo PHP_EOL;
    }

    public function init($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getDbTableList() as $tableName) {
                $this->init($tableName);
            }
            return;
        }

        if ($data->hasDbTable()) {
            if ($data->hasFileData()) {
                echo "Table '{$tableName}' already has data";
            } else {
                if ($data->isWriteable()) {
                    $data->init();
                    echo "'{$tableName}' - OK";
                } else {
                    echo "Data path for '{$tableName}' is not writeable";
                }
            }
        } else {
            echo "Table '{$tableName}' not found";
        }
        echo PHP_EOL;
    }

    public function pull($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getDbTableList() as $tableName) {
                $this->pull($tableName);
            }
            return;
        }

        if ($data->hasDbTable()) {
            if ($data->isWriteable()) {
                $data->pull();
                echo "'{$tableName}' - OK";
            } else {
                echo "Data path for '{$tableName}' is not writeable";
            }
        } else {
            echo "Table '{$tableName}' not found";
        }
        echo PHP_EOL;
    }

    public function diff($tableName = null)
    {
        $data = new DbSync_Table_Data($this->_adapter, $this->_path, $tableName);

        if (!$tableName) {
            foreach ($data->getTableList() as $tableName) {
                $this->diff($tableName);
            }
            return;
        }

        if ($data->hasDbTable() && $data->hasFileData()) {
            if ($data->getStatus()) {
                echo "'{$tableName}' - OK";
            } else {
                echo join(PHP_EOL, $data->diff());
            }
        } else {
            if (!$data->hasDbTable()) {
                echo "Table '{$tableName}' not exists";
            } else {
                echo "Data for '{$tableName}' not found";
            }
        }
        echo PHP_EOL;
    }

    public function help()
    {
        echo 'help';
    }
}