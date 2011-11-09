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
    /**
     * @var DbSync_Console
     */
    protected $_console;

    /**
     * @var array
     */
    protected $_config;

    /**
     * @var array
     */
    protected $_controllers = array(
        'schema'  => 'DbSync_Controller_SchemaController',
        'data'    => 'DbSync_Controller_DataController',
        'trigger' => 'DbSync_Controller_TriggerController',
    );

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;

        echo PHP_EOL;
    }

    /**
     * Dispatch
     *
     * @param DbSync_Console $console
     * @param array|string   $controllers
     * @return mixed
     */
    public function dispatch(DbSync_Console $console, $controllers = array())
    {
        $controllers = (array) $controllers;

        if (empty($controllers)) {
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
        }

        foreach ((array) $controllers as $name) {
            try {
                echo ucfirst($name), ':', PHP_EOL;
                $controller = new $this->_controllers[$name]($this->_config);

                $controller->dispatch($console);
            } catch (Exception $e) {
                //echo $this->colorize($e->getMessage(), 'red');
                echo $e->getMessage();
            }

            echo PHP_EOL;
        }
    }
}