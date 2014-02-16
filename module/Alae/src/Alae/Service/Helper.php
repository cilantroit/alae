<?php

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

}

?>
