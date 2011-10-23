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
 * DbSync_Controller_DataController
 *
 * @category DbSync
 * @package  DbSync_Controller
 * @version  $Id$
 */
class DbSync_Controller_DataController extends DbSync_Controller_AbstractController
{
    /**
     * @var string
     */
    protected $_modelClass = 'DbSync_Table_Data';

    /**
     * Push
     *
     * @return Override database data by current data config file
     * @return Use {--force|yellow} to truncate table first
     */
    public function pushAction()
    {
        $tableName = $this->_model->getTableName();

        if ($this->_model->hasFile()) {
            $force = $this->_console->hasOption('force');
            if (!$force && !$this->_model->isEmptyTable()) {
                echo $tableName . $this->colorize(" - is dirty use --force for cleanup or try merge instead of push");
            } else {
                if (!$this->_model->push($force)) {
                    echo $tableName . $this->colorize(" - Not updated", 'red');
                } else {
                    echo $tableName . $this->colorize(" - Updated", 'green');
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
     * @return Merge data rows from config file to database table
     */
    public function mergeAction()
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
}