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

    const CONTROLLER_SCHEMA  = 'schema';
    const CONTROLLER_DATA    = 'data';
    const CONTROLLER_TRIGGER = 'trigger';
    /**
     * @var DbSync_Console
     */
    protected $_console;

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
     * @param DbSync_Console $console
     */
    public function __construct(DbSync_Console $console)
    {
        $this->_console = $console;

        echo DbSync_Version::getCredits(), PHP_EOL, PHP_EOL;
    }

    /**
     * Load config
     *
     * @return array
     */
    public function loadConfig()
    {
        $filename = './' . self::CONFIG_FILE;

        if (!is_file($filename)) {
            echo "Error '" . self::CONFIG_FILE. "' not found.",
                 PHP_EOL,
                 "Would you like to create config file here (YES/no): ";

            $choice = $this->_console->getStdParam('yes');

            if ('yes' == strtolower($choice)) {
                if (!is_writable('.')) {
                    echo "Current directory is not writable";
                } else {
                    copy(self::CONFIG_FILE . '.example', $filename);
                    echo "Created '" . self::CONFIG_FILE . "'.";
                }
            } else {
                echo 'Bye';
            }
            echo PHP_EOL, exit;
        }

        echo "Configuration read from " . realpath($filename), PHP_EOL, PHP_EOL;

        return parse_ini_file($filename, true);
    }

    /**
     * Get controllers
     *
     * @return array
     */
    public function getControllers()
    {
        $controller = $this->_console->getArgument(0);

        if (isset($this->_controllers[$controller])) {
            $controllers = $controller;

            //remove controller from arguments
            $args = $this->_console->getArguments();
            unset($args['0']);
            $this->_console->setArguments($args);
        } else {
            $controllers = array_keys($this->_controllers);
        }
        return (array) $controllers;
    }

    /**
     * Dispatch
     *
     */
    public function dispatch()
    {
        $config = $this->loadConfig();

        $pharName = $this->_console->getProgname();

        if ('phar' != pathinfo($pharName, PATHINFO_EXTENSION)) {
            $pharName = false;
        }

        foreach ($this->getControllers() as $name) {
            try {
                echo ucfirst($name), ' Synchronization Tool: ', PHP_EOL;

                if ($pharName) {
                    $progname = $pharName . ' ' . $name;
                } else {
                    $progname = $this->_scripts[$name];
                }

                $this->_console->setProgname($progname);

                $controller = new $this->_controllers[$name]($config);

                $controller->dispatch($this->_console);
            } catch (Exception $e) {
                echo $this->_console->colorize($e->getMessage(), 'red');
            }

            echo PHP_EOL;
        }
    }
}