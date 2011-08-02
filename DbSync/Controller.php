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

    public function __call($method, $args)
    {
        $args[] = $method;
        return call_user_func_array(array($this, 'colorize'), $args);
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

        $action = $this->_console->getAction() . 'Action';

        if (!method_exists($this, $action)) {
            $action = 'helpAction';
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
    abstract function helpAction();

    /**
     * Colorize
     *
     * @param string $text
     * @param string $color
     * @return string
     */
    public function colorize($text, $color = 'yellow')
    {
        switch ($color) {
            case 'red':
                $color = "1;31m";
                break;
            case 'green':
                $color = "1;32m";
                break;
            case 'blue':
                $color = "1;34m";
                break;
            case 'white':
                $color = "1;37m";
                break;
            default:
                $color = "1;33m";
                break;
        }
        return "\033[" . $color . $text . "\033[m";
    }
}