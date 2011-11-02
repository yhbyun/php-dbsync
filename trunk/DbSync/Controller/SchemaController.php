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
 * DbSync_Controller_SchemaController
 *
 * @category DbSync
 * @package  DbSync_Controller
 * @version  $Id$
 */
class DbSync_Controller_SchemaController extends DbSync_Controller_AbstractController
{
    /**
    * @var string
    */
    protected $_modelClass = 'DbSync_Model_Table_Schema';

    /**
     * Delete
     *
     * @alias de
     *
     * @return Delete table and config
     * @return Use {--db|yellow} to delete only form database
     * @return Use {--file|yellow} to delete only config file
     */
    public function deleteAction()
    {
        $tableName = $this->_model->getTableName();

        if ($this->_model->hasFile() && !$this->_console->hasOption('db')) {
            if ($this->_model->isWriteable()) {
                $this->_model->deleteFile();
                echo $tableName . $this->colorize(" - File deleted", 'green');
            } else {
                echo $tableName . $this->colorize(" - Path is not writeable", 'red');
            }
        }
        if ($this->_model->hasDbTable() && !$this->_console->hasOption('file')) {
            $this->_model->dropDbTable();
            echo $tableName . $this->colorize(" - Database table deleted", 'green');
        }
    }
}