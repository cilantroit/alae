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

// Zend\View\Model\JsonModel;

class UserController extends BaseController
{

    protected $_document = '\\Alae\\Entity\\User';

    public function loginAction()
    {
	$request = $this->getRequest();

	if ($request->isPost())
	{
	    $elements = $this->getRepository()
		    ->findBy(array('username' => $request->getPost('username'), 'password' => md5(sha1($request->getPost('password')))));

	    if ((!empty($elements)))
	    {
		foreach ($elements as $element)
		{
		    if ($element->getActiveFlag() == \Alae\Entity\User::USER_ACTIVE_FLAG)
		    {
			$this->_setSession($element);

			echo 'rrrrrrrr';
			$this->redirect()->toRoute('index', array('action' => 'menu'));
		    }
		}
	    }
	    return new ViewModel();
	}
    }

    public function newaccountAction()
    {

	$request = $this->getRequest();


	$message = array("usr" => " ", "email" => "");
	/*
	 * Los campos son obligatorios en el formulario,
	 * si no están los dos, no puede ingresar nada (No te preocupes con data extraña, en la validacion por jquery lo limpiaremos
	 * ademas Doctrine no te permite sql inyection.
	 */


	if ($request->isPost())
	{
	    $user = new \Alae\Entity\User();
	    $email = $request->getPost('email');
	    $username = $request->getPost('username');
	    $findemail = $this->getRepository()->findBy(array('email' => $email));
	    $findusername = $this->getRepository()->findBy(array('username' => $username));
	    if (!empty($findusername))
	    {
		$message['usr'] = 'Este usuario ya existe';

//array_push($message, 'Este usuario ya existe');
	    }
	    else if (!empty($findemail))
	    {
		$message['email'] = 'Este email ya existe';
	    }
	    else
	    {


		$user->setUsername($request->getPost('username'));
		$user->setEmail($request->getPost('email'));
		//$password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8);
		//$password = '3350';

		$password = '3350';
		$user->setPassword($password);
		$this->getEntityManager()->persist($user);
		$this->getEntityManager()->flush();

		if ($user->getPkUser())
		{

		    $mail = new \Alae\Service\Mailing();
		    // $mail->send(array($user->getEmail()), $this->render('alae/user/template', array('password' => $user->getPassword(), 'user' => $user->getUsername())));

		    $mail->send(array('daniel.farnos.e@gmail.com'), $this->render('alae/user/template_new_usr_sol', array('password' => $user->getPassword(), 'user' => $user->getUsername())));
		}
	    }
//}
	}
	return new ViewModel(array('error' => $message,
	));
    }

    public function confirmnewaccountAction()
    {

	echo $_GET['pass'];
	echo $_GET['usr'];
//	echo $usr = $this->getEvent()->getRouteMatch()->getParam('usr');
//	echo $pass = $this->getEvent()->getRouteMatch()->getParam('pass');

	return new ViewModel(array(
	    'usr' => $_GET['usr'],
	    'pass' => $_GET['pass'],
	));
    }

}
