<?php
/**
 * DbSync_Table_DbAdapter
 *
 * @author $Id$
 */
class DbSync_Table_DbAdapter
{
    /**
     * @var array
     */
    protected static $_adapters = array(
        'mysql' => 'DbSync_Table_DbAdapter_Mysql',
    );

    /**
     * Constructor
     *
     * @param array $config
     */
    public static function factory($type, array $config)
    {
        if (!isset(self::$_adapters[$type])) {
            throw new Exception("Unknown adapter type - '{$type}'");
        }
        return new self::$_adapters[$type]($config);
    }
}