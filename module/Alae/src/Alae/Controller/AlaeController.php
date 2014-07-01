<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

/* APLICACION ALAE
   Fichero de configuración de conexión a la base de datos
   Autor: María Quiroz
*/

namespace Alae\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

class AlaeController extends AbstractActionController
{

    public function indexAction()
    {

	$enlace = mysql_connect('localhost', 'alae', 'S3Y6zQVAJfyBT2MK');
	mysql_select_db('alae', $enlace);
	if (!$enlace)
	{
	    die('No pudo conectarse: ' . mysql_error());
	}

	$resultado = mysql_query("select * from alae_profile");
	var_dump($resultado);
	echo 'Conectado satisfactoriamente';
	mysql_close($enlace);



	return new ViewModel();
    }

}

