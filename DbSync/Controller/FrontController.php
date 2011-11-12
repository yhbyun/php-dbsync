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
 * DbSync_Controller_FrontController
 *
 * @category DbSync
 * @package  DbSync_Controller
 * @version  $Id$
 */
class DbSync_Controller_FrontController
{
    const CONFIG_FILE = 'phpdbsync.ini';
    const CONFIG_FILE_EXAMPLE = 'phpdbsync.ini.example';

    const CONTROLLER_SCHEMA  = 'schema';
    const CONTROLLER_DATA    = 'data';
    const CONTROLLER_TRIGGER = 'trigger';

    /**
     * @var array
     */
    protected $_controllers = array(
        self::CONTROLLER_SCHEMA  => 'DbSync_Controller_SchemaController',
        self::CONTROLLER_DATA    => 'DbSync_Controller_DataController',
        self::CONTROLLER_TRIGGER => 'DbSync_Controller_TriggerController',
    );

    /**
     * @var array
     */
    protected $_scripts = array(
        self::CONTROLLER_SCHEMA  => 'schema.sh',
        self::CONTROLLER_DATA    => 'data.sh',
        self::CONTROLLER_TRIGGER => 'trigger.sh',
    );

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        echo DbSync_Version::getCredits(), PHP_EOL, PHP_EOL;
    }

    /**
     * Load config
     *
     * @param DbSync_Console $console
     * @return array
     */
    public function getConfig(DbSync_Console $console)
    {
        $filename = REAL_PATH . "/" . self::CONFIG_FILE;

        if (!is_file($filename)) {
            echo "Error '" . self::CONFIG_FILE. "' not found.",
                 PHP_EOL,
                 "Would you like to create config file here (YES/no): ";

            $choice = $console->getStdParam('yes');

            if ('yes' == strtolower($choice)) {
                if (!is_writable(REAL_PATH)) {
                    echo "Current directory is not writable";
                } else {
                    copy(APPLICATION_PATH . "/". self::CONFIG_FILE_EXAMPLE, $filename);
                    echo "Created '" . self::CONFIG_FILE . "'.";
                }
            } else {
                echo 'Bye';
            }
            echo PHP_EOL, exit;
        }

        echo "Configuration read from " . $filename, PHP_EOL, PHP_EOL;

        return parse_ini_file($filename, true);
    }

    /**
     * Get controllers
     *
     * @param DbSync_Console $console
     * @return array
     */
    public function getControllers(DbSync_Console $console)
    {
        $controller = $console->getArgument(0);

        if (isset($this->_controllers[$controller])) {
            $controllers = $controller;

            //remove controller from arguments
            $args = $console->getArguments();
            unset($args['0']);
            $console->setArguments($args);
        } else {
            $controllers = array_keys($this->_controllers);
        }
        return (array) $controllers;
    }

    /**
     * Dispatch
     *
     * @param DbSync_Console $console
     */
    public function dispatch(DbSync_Console $console)
    {
        try {
            $config = $this->getConfig($console);

            if (empty($config['adapters']['db'])) {
                throw new DbSync_Exception('Db adapter not set');
            }

            if (empty($config['adapters']['file'])) {
                throw new DbSync_Exception('File adapter not set');
            }

            $db = new $config['adapters']['db']($config['connection']);
            $file = new $config['adapters']['file']($config['path']);

            $pharName = $console->getProgname();

            if ('phar' != pathinfo($pharName, PATHINFO_EXTENSION)) {
                $pharName = false;
            }

            foreach ($this->getControllers($console) as $name) {
                echo ucfirst($name), ' Synchronization Tool: ', PHP_EOL;

                if ($pharName) {
                    $progname = $pharName . ' ' . $name;
                } else {
                    $progname = $this->_scripts[$name];
                }
                $console->setProgname($progname);

                $controller = new $this->_controllers[$name]($db, $file, $config['diffprog']);

                try {
                    $controller->dispatch($console);
                } catch (DbSync_Exception $e) {
                    echo $console->colorize($e->getMessage(), 'red');
                }
                echo PHP_EOL;
            }
        } catch (Exception $e) {
            echo $console->colorize($e->getMessage(), 'red'), PHP_EOL;
        }
    }
}