<?php
/**
 * DbSync_Controller
 *
 * @version $Id$
 */
abstract class DbSync_Controller
{
    protected $_adapter;

    protected $_path;

    protected $_console;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_adapter = DbSync_Table_Adapter::factory(
            $config['db']['adapter'],
            $config['db']['params']
        );

        $this->_path = $config['path'];
    }

    /**
     * Dispatch
     *
     * @param DbSync_Console $console
     * @return mixed
     */
    public function dispatch(DbSync_Console $console)
    {
        $this->_console = $console;

        $action = $this->_console->getAction();

        if (!method_exists($this, $action)) {
            $action = 'help';
        }
        $actions = $console->getActions();
        unset($actions['0']);

        return call_user_func_array(array($this, $action), $actions);
    }


    /**
     * Descructor
     *
     */
    public function __destruct()
    {
        echo PHP_EOL;
    }

    /**
     * Help action
     */
    abstract function help();
}