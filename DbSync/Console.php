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
 * @package  DbSync_Console
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id$
 */

/**
 * DbSync_Console
 *
 * Simple console parser
 *
 * $> script.sh [arg1 arg2 ...] [--optionName=optionValue] [-optionName=optionValue]
 *
 * $> script.sh create user --name john --status
 *
 * $console->getArguments() array() {0 => create, 1 => user}
 * $console->getOptions() array() {name => john, status => null}
 *
 * @category DbSync
 * @package  DbSync_Console
 * @version  $Id$
 */
class DbSync_Console
{
    /**
     * @var array
     */
    protected $_arguments = array();

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var array
     */
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
        $searchArguments =  true;

        foreach ($_SERVER["argv"] as $arg) {
            if (strpos($arg, '-') === 0) {
                $searchArguments = false;
                $this->_options[trim($arg, '-')] = null;
            } elseif ($searchArguments) {
                $this->_arguments[] = $arg;
            } else {
                if (count($this->_options)) {
                    end($this->_options);

                    $optionName = key($this->_options);
                    if (!empty($this->_options[ $optionName ])) {
                        $this->_options[ $optionName ] = (array) $this->_options[ $optionName ];
                        $this->_options[ $optionName ][] = $arg;
                    } else {
                        $this->_options[ $optionName ] = $arg;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get all arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Set arguments
     *
     * @param array $args
     * @return DbSync_Console
     */
    public function setArguments(array $args)
    {
        $this->_arguments = $args;
        return $this;
    }

    /**
     * Get argument
     *
     * @param integer $index
     * @param mixed   $default
     * @return mixed
     */
    public function getArgument($index = 0, $default = false)
    {
        if (isset($this->_arguments[$index])) {
            return $this->_arguments[$index];
        }
        return $default;
    }

    /**
     * Has argument
     *
     * @param string $argName
     * @return boolean
     */
    public function hasArgument($argName)
    {
        return in_array($argName, $this->_arguments);
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
     * @param mixed  $default
     * @return mixed
     */
    public function getOption($name, $default = false)
    {
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
        return $default;
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

    /**
     * Set progname
     *
     * @param string $progname
     * @return DbSync_Console
     */
    public function setProgname($progname)
    {
        $this->_progname = (string) $progname;
        return $this;
    }
}