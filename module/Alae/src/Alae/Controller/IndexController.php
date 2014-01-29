<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Alae\Controller;

use Zend\View\Model\ViewModel,
    Alae\Controller\BaseController;

class IndexController extends BaseController
{

    public function logoutAction()
    {


	$session_user = new \Zend\Session\Container('user');
	$session_user->getManager()->getStorage()->clear('user');
	$viewModel = new ViewModel();
	$viewModel->setTerminal(true);
	return $this->forward()->dispatch('alae/Controller/index', array('action' => 'login'));
    }

    public function menuAction()
    {

	return new ViewModel();
    }

    public function loginAction()
    {
	$request = $this->getRequest();

	if ($request->isPost())
	{
	    $elements = $this->getRepository('\\Alae\\Entity\\User')
		    ->findBy(array('username' => $request->getPost('username'), 'password' => md5(sha1($request->getPost('password')))));



	    if ((!empty($elements)))
	    {


		foreach ($elements as $element)
		{
		    if ($element->getActiveFlag() == \Alae\Entity\User::USER_ACTIVE_FLAG)
		    {


			$this->_setSession($element);
			return $this->redirect()->toRoute('index', array('controller' => 'index', 'action' => 'menu'));
		    }
		    else
		    {
			$message = 'Este usuario no tiene permiso de acceso. Por favor contacte al administrador.';
		    }
		}
	    }
	    else
	    {
		$message = 'Usuario o contraseña invalidos. Por favor revise los campos.';
	    }
	}


	return new ViewModel(array('error' => $message,
	));
    }

}
