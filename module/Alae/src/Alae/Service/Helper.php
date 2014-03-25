<?php

/**
 * @author Maria Quiroz
 */
 
namespace Alae\Service;

class Helper
{

    protected static $_varsConfig;
    protected static $_message;
    protected static $_errors;

    public static function getVarsConfig($var)
    {
        if (is_null(self::$_varsConfig))
	{
	    self::$_varsConfig = include 'module\Alae\config\vars.config.php';
	}

	return self::$_varsConfig[$var];
    }

    public static function getMessage($message)
    {
	if (is_null(self::$_message))
	{
	    self::$_message = include 'module\Alae\config\messages.php';
	}

	return self::$_message[$message];
    }

    public static function getError($error)
    {
	if (is_null(self::$_errors))
	{
	    self::$_errors = include 'module\Alae\config\errors.php';
	}

	return self::$_errors[$error];
    }

    public static function getUserSession()
    {
	$session = new \Zend\Session\Container('user');

	if ($session->offsetExists('id'))
	{
	    return sprintf("<strong>%s</strong> | %s", $session->profile, $session->name);
	}

        return false;
    }

    public static function getformatDecimal($value)
    {

    	switch ($value)
    	{
    		case $value >= 0.1 && $value <= 0.9:
    			$decimal = number_format($value, 3, '.', '');
    			break;
    		case $value >= 0.01 && $value <= 0.09:
    			$decimal = number_format($value, 4, '.', '');
    			break;
    		case $value >= 0.001 && $value <= 0.009:
    			$decimal = number_format($value, 5, '.', '');
    			break;
    		case $value >= 0.0001 && $value <= 0.0009:
    			$decimal = number_format($value, 6, '.', '');
    			break;
    		case $value >= 0.00001 && $value <= 0.00009:
    			$decimal = number_format($value, 7, '.', '');
    			break;
    		default:
    			$decimal = $value;
    			break;
    	}
	return $decimal;
    }
}

?>
