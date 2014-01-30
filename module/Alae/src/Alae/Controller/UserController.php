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
    Alae\Controller\BaseController,
    Alae\Service\Datatable,
    Zend\View\Model\JsonModel;

class UserController extends BaseController
{

    protected $_document = '\\Alae\\Entity\\User';

    public function init()
    {

    }

    public function newaccountAction()
    {
	$request = $this->getRequest();
	$message = array("usr" => " ", "email" => "");

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
		$user->setActiveCode($password);
		$this->getEntityManager()->persist($user);
		$this->getEntityManager()->flush();

		if ($user->getPkUser())
		{
		    $mail = new \Alae\Service\Mailing();
		    $mail->send(array('daniel.farnos.e@gmail.com'), $this->render('alae/user/template_new_usr_sol', array('password' => $user->getPassword(), 'user' => $user->getUsername())));
		}
	    }
	}
	return new ViewModel(array('error' => $message));
    }

    protected function getProfileOptions($pkProfile)
    {
	$elements = $this->getRepository('\\Alae\\Entity\\Profile')->findAll();
	$options = '<option value="0">Seleccione</option>';
	foreach ($elements as $profile)
	{
	    $selected = ($profile->getPkProfile() == $pkProfile) ? "selected" : "";
	    $options .= sprintf('<option value="%d" %s>%s</option>', $profile->getPkProfile(), $selected, $profile->getName());
	}
	return $options;
    }

    public function adminAction()
    {
	$users = $this->getRepository()->findAll();
	$data = array();
	foreach ($users as $user)
	{
	    $data[] = array(
		"username" => utf8_encode($user->getUsername()),
		"email" => utf8_encode($user->getEmail()),
		"profile" => '<select class="form-datatable-profile" id="form-datatable-profile-' . $user->getPkUser() . '">' . $this->getProfileOptions($user->getFkProfile()->getPkProfile()) . '</select>',
		"password" => $user->getPkUser(),
		"status" => $user->getActiveFlag() ? "S" : "N",
		"edit" => $user->getPkUser()
	    );
	}

	$datatable = new Datatable($data, Datatable::DATATABLE_ADMIN);
	return new ViewModel($datatable->getDatatable());
    }

    public function approveAction()
    {
	$request = $this->getRequest();
	$return = false;

	if ($request->isGet())
	{
	    $User = $this->getRepository()->find($request->getQuery('id'));
	    $Profile = $this->getRepository('\\Alae\\Entity\\Profile')->find($request->getQuery('profile'));
	    $User->setActiveFlag(\Alae\Entity\User::USER_ACTIVE_FLAG);

	    if (!$this->getRequest()->isXmlHttpRequest())
	    {
		return array();
	    }
	    $User->setFkProfile($Profile);
	    $this->getEntityManager()->persist($User);
	    $this->getEntityManager()->flush();


	    $mail = new \Alae\Service\Mailing();
	    $mail->send(array($User->getEmail()), $this->render('alae/user/template', array('active_code' => $User->getActiveCode(), 'email' => $User->getEmail())));
	    $jsonModel = new JsonModel();
	    return $jsonModel;
	}
    }

    public function registerAction()
    {
	/**
	 * Recibe un GET con el email del user + active code
	 */
	$showForm = false;
	$message = "";
	$pkUser = 0;
	$username = "";
	$email = "";
	$request = $this->getRequest();
	$request->getQuery('email');
	if ($request->isGet() && $request->getQuery('email') && $request->getQuery('active_code'))
	{
	    $User = $this->getRepository()->findBy(array("email" => trim($request->getQuery('email'))));
	    $username = $User[0]->getUsername();
	    $email = $User[0]->getEmail();
	    $pkUser = $User[0]->getPkUser();
	    /*
	     * Verificamos que el activeCode registrado = al activeCode del GET
	     */
	    if ($User && $User[0]->getActiveCode() == trim($request->getQuery('active_code')))
	    {
		$showForm = true;
		$pkUser = $User[0]->getPkUser();
	    }
	    else
	    {
		$message = "<div class='error'>El código de activación ha caducado...</div>";
	    }
	}

	if ($request->isPost())
	{
	    $User = $this->getRepository()->find($request->getPost('id'));
	    $User->setPassword($request->getPost('password'));
	    $User->setName($request->getPost('name'));
	    $User->setActiveCode(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8));
	    $this->getEntityManager()->persist($User);
	    $this->getEntityManager()->flush();
	    $message = '<a href="' . \Alae\Service\Helper::getVarsConfig("base_url") . '/index/login">Se ha registrado la información de manera exitosa!!! Para acceder inicie sesion;</a>';
	}

	return new ViewModel(array(
	    "showForm" => $showForm,
	    "message" => $message,
	    "pkUser" => $pkUser,
	    "username" => $username,
	    "email" => $email,
	));
    }

    public function rejectAction()
    {
	$request = $this->getRequest();

	if ($request->isGet())
	{
	    $User = $this->getRepository()->find($request->getQuery('id'));
	    $User->setActiveFlag(\Alae\Entity\User::USER_INACTIVE_FLAG);
	    $this->getEntityManager()->persist($User);
	    $this->getEntityManager()->flush();
	    $jsonModel = new JsonModel();
	    return $jsonModel;
	}
    }

    public function sentverificationAction()
    {
	$request = $this->getRequest();

	if ($request->isGet())
	{
	    $User = $this->getRepository()->find($request->getQuery('id'));
	    $verification = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8);


	    $User->setVerification($verification);
	    $this->getEntityManager()->persist($User);
	    $this->getEntityManager()->flush();
	    $mail = new \Alae\Service\Mailing();
	    $mail->send(array($User->getEmail()), $this->render('alae/user/template_verification', array('verification' => $verification, 'email' => $User->getEmail(), 'name' => $User->getEmail())));


//	    $jsonModel = new JsonModel();
//	    return $jsonModel;
	}
    }

    public function resetpassAction()
    {
	$request = $this->getRequest();
	$message = '';
	$User = new \Alae\Entity\User();
	if ($request->isPost())
	{
	    $User = $this->getRepository()->findBy(array('name' => $request->getPost('username')));

	    if ($User)
	    {
		$User[0]->setActiveCode(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8));
		$this->getEntityManager()->persist($User[0]);
		$this->getEntityManager()->flush();
		$mail = new \Alae\Service\Mailing();
		$mail->send(array('daniel.farnos.e@gmail.com'), $this->render('alae/user/template_reset_pass', array('active_code' => $User[0]->getActiveCode(), 'link' => \Alae\Service\Helper::getVarsConfig("base_url") . '/user/newpassword', 'username' => $User[0]->getName())));
	    }
	    else
	    {
		$message = "<div class='error'>El Nombre de usuario es incorrecto....</div>";
	    }
	}


	return new ViewModel(array(
	    "message" => $message
	));
    }

    public function newpasswordAction()
    {
	$Username = "";
	$message = "";
	$ShowForm = "";
	$Email = "";
	$Id = "";
	$Perfil = '';
	$Request = $this->getRequest();
	$Entity = new \Alae\Entity\User();
	if ($Request->isPost())
	{
	    $User = $this->getRepository()->findBy(array('pkUser' => $Request->getPost('id')));
	    if (!empty($User[0]))
	    {
		$showForm = true;
		$message = 'Ok';
		$Activecode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8);
		$User[0]->setActiveCode($Activecode);
		$User[0]->setPassword($Request->getPost('password'));
		$this->getEntityManager()->persist($User[0]);
		$this->getEntityManager()->flush();


		$message = '<a href="' . \Alae\Service\Helper::getVarsConfig("base_url") . '/index/login">Su cambio de password se realizó de manera exitosa!!!</a>';
	    }
	    else
	    {
		$message = 'Se ha presentado un error.Por favor intente de nuevo';
		$showForm = false;
	    }
	}

	if ($Request->isGet())
	{
	    $User = $this->getRepository()->findBy(array("activeCode" => trim($Request->getQuery('active_code'))));
	    if (empty($User))
	    {
		$message = '<div class="errordiv">Sú código de ha caducado. Debe repetir el proceso<div>';
		$showForm = false;
	    }
	    else
	    {
		$showForm = true;
		$Username = $User[0]->getUsername();
		$Email = $User[0]->getEmail();
		$Perfil = $User[0]->getFkProfile()->getName();
		$Id = $User[0]->getPkUser();
	    }
	}
	return new ViewModel(array(
	    "showForm" => $showForm,
	    "message" => $message,
	    "username" => $Username,
	    "email" => $Email,
	    "perfil" => $Perfil,
	    "id" => $Id
	));
    }

}
