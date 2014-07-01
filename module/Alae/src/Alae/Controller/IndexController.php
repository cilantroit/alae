<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

/* APLICACION ALAE
   Fichero que se encarga de control del login y logout del usuario, además de mostrar
 * las opciones de menú correspondiente de cada perfil.
   Autor: María Quiroz
*/

namespace Alae\Controller;

use Zend\View\Model\ViewModel,
    Alae\Controller\BaseController,
    Zend\View\Model\JsonModel;

class IndexController extends BaseController
{

    public function init()
    {

    }

    public function logoutAction()
    {
        $User = $this->_getSession();
        if(!$User)
        {
            return $this->forward()->dispatch('alae/Controller/index', array('action' => 'login'));
        }

        $this->transaction(
            "Fin de sesión",
            sprintf("El usuario %s ha cerrado sesión", $User->getUsername()),
            false
        );
        $session_user = new \Zend\Session\Container('user');
        $session_user->getManager()->getStorage()->clear('user');
        
        $query1 = $this->getEntityManager()->createQuery("
        SELECT COUNT(s.pkStudy)
        FROM Alae\Entity\Study s
        WHERE s.status = 1");
        $study1 = $query1->getSingleScalarResult();
        
        $query2 = $this->getEntityManager()->createQuery("
        SELECT COUNT(a.pkAnalyte)
        FROM Alae\Entity\Analyte a
        WHERE a.status = 1");
        $analyte1 = $query2->getSingleScalarResult();
        
        $query3 = $this->getEntityManager()->createQuery("
        SELECT COUNT(b.pkBatch)
        FROM Alae\Entity\Batch b");
        $batch1 = $query3->getSingleScalarResult();
        
        return new ViewModel(array("username" => $User->getUsername(),"study1" => $study1,"analyte1" => $analyte1,"batch1" => $batch1));
    }

    public function menuAction()
    {
        if (!$this->isLogged())
        {
            return $this->forward()->dispatch('alae/Controller/index', array('action' => 'login'));
        }
        return new ViewModel(array("user" => $this->_getSession()));
    }

    public function loginAction()
    {
        if ($this->isLogged())
        {
            header('Location: ' . \Alae\Service\Helper::getVarsConfig("base_url")."/index/menu");
            exit;
        }

	$request = $this->getRequest();

        $error = array(
            "inactive"  => false,
            "incorrect" => false
        );

        if ($request->isPost())
        {
	    $elements = $this->getRepository('\\Alae\\Entity\\User')->findBy(array(
                'username' => $request->getPost('username'),
                'password' => md5(sha1($request->getPost('password')
            ))));

	    if ((!empty($elements)))
	    {
		foreach ($elements as $element)
		{
		    if (!$element->isCron() && $element->getActiveFlag() == \Alae\Entity\User::USER_ACTIVE_FLAG)
		    {
			$this->_setSession($element);
			return $this->redirect()->toRoute('index', array('controller' => 'index', 'action' => 'menu'));
		    }
		    else
		    {
                        $error['inactive'] = true;
		    }
		}
	    }
	    else
	    {
                $error['incorrect'] = true;
	    }
	}

	return new ViewModel($error);
    }

    public function autenticationAction()
    {
	$request = $this->getRequest();
        $response = false;

        if ($request->isPost())
	{
	    $User = $this->_getSession();
            if ($User->getActiveFlag() == \Alae\Entity\User::USER_ACTIVE_FLAG && $User->getVerification() == $request->getPost('password'))
            {
                $response = true;
                $this->transaction(
                    "Firma digital",
                    sprintf("El usuario %s, ha ingresado su firma digital para %s", $User->getName(), $request->getPost('message')),
                    false
                );
            }
	}

        return new JsonModel(array(
            "response" => $response,
            "error" => !$response ? "Contraseña incorrecta" : ""
        ));
    }

}
