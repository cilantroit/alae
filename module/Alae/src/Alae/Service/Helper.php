<?php

/**
 * Clase para funciones como:
 * Obtener las variables de configuración del sistema
 * Obtener mensajes, obtener errores
 * Obtener el usuario actual del sistema
 *  Conversor de cifras significativas
 * @author Maria Quiroz
 * Fecha de creación: 11/05/2014
 */
 
namespace Alae\Service;

class Helper
{

    protected static $_varsConfig;
    protected static $_message;
    protected static $_errors;

    /*
     * obtener las variables de configuración del sistema
     */
    public static function getVarsConfig($var)
    {
        if (is_null(self::$_varsConfig))
	{
	    self::$_varsConfig = include 'module\Alae\config\vars.config.php';
	}

	return self::$_varsConfig[$var];
    }

    /*
     * obtener mensajes
     */
    public static function getMessage($message)
    {
	if (is_null(self::$_message))
	{
	    self::$_message = include 'module\Alae\config\messages.php';
	}

	return self::$_message[$message];
    }

    /*
     * obtener errores
     */
    public static function getError($error)
    {
	if (is_null(self::$_errors))
	{
	    self::$_errors = include 'module\Alae\config\errors.php';
	}

	return self::$_errors[$error];
    }

    /*
     * obtener el usuario actual
     */
    public static function getUserSession()
    {
	$session = new \Zend\Session\Container('user');

	if ($session->offsetExists('id'))
	{
	    return sprintf("<strong>%s</strong> | %s", $session->profile, $session->name);
	}

        return false;
    }

    /*
     * conversor de cifras significativas pdf
     */
    public static function getformatDecimal($value)
    {
    	switch ($value)
    	{
    		case $value >= 0.1 && $value <= 0.9:
                    //FORMATO HTML
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
    /*
     * conversor de cifras significativas excel
     */
    public static function getformatDecimal2($value)
    {
    	switch ($value)
    	{
    		case $value >= 0.1 && $value <= 0.9:
                    //FORMATO EXCEL
    			$decimal = "mso-number-format:'#,##0.000'";
    			break;
    		case $value >= 0.01 && $value <= 0.09:
    			$decimal = "mso-number-format:'#,##0.0000'";
    			break;
    		case $value >= 0.001 && $value <= 0.009:
    			$decimal = "mso-number-format:'#,##0.00000'";
    			break;
    		case $value >= 0.0001 && $value <= 0.0009:
    			$decimal = "mso-number-format:'#,##0.000000'";
    			break;
    		case $value >= 0.00001 && $value <= 0.00009:
    			$decimal = "mso-number-format:'#,##0.0000000'";
    			break;
    		default:
    			$decimal = "";
    			break;
    	}
	return $decimal;
    }
    
}

?>
