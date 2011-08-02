<?php
/**
 * DbSync_Console
 *
 * Simple console parser
 *
 * $> script.sh [create action2 ...] [--optionName=optionValue] [-optionName=optionValue]
 *
 * $> script.sh create user --name john --status
 *
 * $console->getActions() array() {0 => create, 1 => user}
 * $console->getOptions() array() {name => john, status => null}
 *
 * @version $Id$
 */
class DbSync_Console
{
    protected $_actions = array();

    protected $_options = array();

    protected $_progname;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->_progname = $_SERVER["argv"]['0'];

        unset($_SERVER["argv"]['0']);
    }

    /**
     * Parse input
     *
     * @return DbSync_Console
     */
    public function parse()
    {
        $searchActions =  true;

        foreach ($_SERVER["argv"] as $arg) {
            if (strpos($arg, '-') === 0) {
                $searchActions = false;
                $this->_options[trim($arg, '-')] = null;
            } elseif ($searchActions) {
                $this->_actions[] = $arg;
            } else {
                if (count($this->_options)) {
                    end($this->_options);

                    $this->_options[ key($this->_options) ] = $arg;
                }
            }
        }

        return $this;
    }

    /**
     * Get all actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Get action
     *
     * @param integer $i
     * @param mixed $default
     */
    public function getAction($i = 0, $default = false)
    {
        if (isset($this->_actions[$i])) {
            return $this->_actions[$i];
        }
        return $default;
    }

    /**
     * Has action
     *
     * @param string $actionName
     * @return boolean
     */
    public function hasAction($actionName)
    {
        return in_array($actionName, $this->_actions);
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get options
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name, $default = false)
    {
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
        return false;
    }

    /**
     * Has option
     *
     * @param string $name
     * @return boolen
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->_options);
    }

    /**
     * Get progname
     *
     * @return string
     */
    public function getProgname()
    {
        return $this->_progname;
    }
}