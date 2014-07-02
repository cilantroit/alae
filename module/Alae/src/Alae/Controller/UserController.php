<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
 /**
 * Modulo de gestión de usuarios
 * @author Maria Quiroz
   Fecha de creacion: 17/05/2014
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

    /*
     * Función para crear una nueva cuenta de acceso
     */
    public function newaccountAction()
    {
	$request = $this->getRequest();
	$error = array(
            "username"  => false,
            "email"     => false
        );

	if ($request->isPost())
	{
	    $email      = $this->getRepository()->findBy(array('email' => $request->getPost('email')));
	    $username   = $this->getRepository()->findBy(array('username' => $request->getPost('username')));

	    if (count($username) > 0)
	    {
		$error['username'] = true;
	    }
	    else if (count($email) > 0)
	    {
		$error['email'] = true;
	    }
	    else
	    {
                try
                {
                    //CREA NUEVA CUENTA
                    $Profile = $this->getRepository("\\Alae\\Entity\\Profile")->findBy(array("name" => "Sin asignar"));
                    $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8);

                    $User = new \Alae\Entity\User();
                    $User->setUsername($request->getPost('username'));
                    $User->setEmail($request->getPost('email'));
                    $User->setPassword($password);
                    $User->setActiveCode($password);
                    $User->setFkProfile($Profile[0]);
                    $this->getEntityManager()->persist($User);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Solicitud de cuenta",
                        sprintf('El usuario %1$s ha solicitado cuenta en ALAE - Email: %2$s',
                            $User->getUsername(),
                            $User->getEmail()
                        ),
                        true
                    );
                    $mail = new \Alae\Service\Mailing();
                    $mail->send(array(
                    $User->getEmail()),
                    $this->render('alae/user/template', array(
                        'user' => $User->getUsername(),
                        'link' => \Alae\Service\Helper::getVarsConfig("base_url") . '/user/register?active_code=' . $User->getActiveCode() . '&email=' . $User->getEmail())), 'Solicitud de Acceso a ALAE');

                    $Profile  = $this->getRepository("\\Alae\\Entity\Profile")->findBy(array("name" => "Administrador"));
                    $elements = $this->getRepository()->findBy(array('fkProfile' => $Profile, 'activeFlag' => 1));
		    foreach ($elements as $Admin)
		    {
                        //ENVIA EL CORREO ELECTRONICO
			$mail->send(
                            array($Admin->getEmail()),
                            $this->render('alae/user/template_new_account_admin',
                            array('email' => $User->getEmail(), 'user' => $User->getUsername())),
                            'Solicitud de Acceso a ALAE'
                        );
		    }

                    return $this->redirect()->toRoute('index', array(
                        'controller' => 'index',
                        'action'     => 'login'
                    ));
                }
                catch (Exception $ex)
                {
                    exit;
                }
	    }
	}
	return new ViewModel($error);
    }

    /*
     * Función para ver las opciones del profile
     */
    protected function getProfileOptions($pkProfile)
    {
        //LISTADO DE PERFILES
	$elements = $this->getRepository('\\Alae\\Entity\\Profile')->findAll();
	$options = '';
	foreach ($elements as $profile)
	{
	    $selected = ($profile->getPkProfile() == $pkProfile) ? "selected" : "";
	    $options .= sprintf('<option value="%d" %s>%s</option>', $profile->getPkProfile(), $selected, $profile->getName());
	}
	return $options;
    }

    /*
     * Esta función muestra el listado de usuarios
     */
    public function adminAction()
    {
	$users = $this->getRepository()->findAll();

        //MUESTRA LOS DATOS EN PANTALLA
	$data = array();
	foreach ($users as $user)
	{
	    $data[] = array(
		"username" => utf8_encode($user->getUsername()),
		"email" => utf8_encode($user->getEmail()),
		"profile" => '<select class="form-datatable-profile" id="form-datatable-profile-' . $user->getPkUser() . '">' . $this->getProfileOptions($user->getFkProfile()->getPkProfile()) . '</select>',
		"password" => ($user->isAdministrador() || $user->isDirectorEstudio()) ? '<button class="btn" type="button" onclick="sentpassword(' . $user->getPkUser() . ');"><span class="btn-mail"></span>enviar contraseña</button>' : '',
		"status" => $user->getActiveFlag() ? "S" : "N",
		"edit" => $user->getActiveFlag() ? '<span class="form-datatable-save" onclick="javascript:changeProfile(' . $user->getPkUser() . ');"></span><span class="form-datatable-reject" onclick="reject(' . $user->getPkUser() . ');"></span>': '<span class="form-datatable-approve" onclick="approve(' . $user->getPkUser() . ')"></span>'
	    );
	}

	$datatable = new Datatable($data, Datatable::DATATABLE_ADMIN, $this->_getSession()->getFkProfile()->getName());
	$viewModel = new ViewModel($datatable->getDatatable());
	$viewModel->setVariable('user', $this->_getSession());
	return $viewModel;
    }

    /*
     * Esta función se encarga de aprobar el acceso
     */
    public function approveAction()
    {
	$request = $this->getRequest();

	if ($request->isGet() && $request->getQuery('profile') != "" && $request->getQuery('id') != "")
	{
            $Profile = $this->getRepository('\\Alae\\Entity\\Profile')->find($request->getQuery('profile'));
            $User = $this->getRepository()->find($request->getQuery('id'));
            try
            {
                //APROBAR EL ACCESO
                $User->setActiveFlag(\Alae\Entity\User::USER_ACTIVE_FLAG);
                $User->setFkProfile($Profile);
                $this->getEntityManager()->persist($User);
                $this->getEntityManager()->flush();
                $this->transaction(
                    "Aprobación de acceso",
                    sprintf('Se ha aceptado la solicitud de ingreso en ALAE a %1$s',
                        $User->getUsername()
                    ),
                    false
                );

                $mail = new \Alae\Service\Mailing();
                $mail->send(
                    array($User->getEmail()),
                    $this->render('alae/user/template_new_account_user',
                    array('email' => $User->getEmail(), 'user' => $User->getUsername())),
                    'Solicitud de Acceso a ALAE'
                );
            }
            catch (Exception $e)
            {
                exit;
            }
	}

        return new JsonModel();
    }

    /*
     * Función para cambiar el perfil de acceso de un usuario
     */
    public function changeAction()
    {
	$request = $this->getRequest();

	if ($request->isGet() && $request->getQuery('profile') != "" && $request->getQuery('id') != "")
	{
            $Profile = $this->getRepository('\\Alae\\Entity\\Profile')->find($request->getQuery('profile'));
            $User = $this->getRepository()->find($request->getQuery('id'));
            try
            {
                //CAMBIAR EL PERFIL DEL USUARIO
                $User->setFkProfile($Profile);
                $this->getEntityManager()->persist($User);
                $this->getEntityManager()->flush();
                $this->transaction(
                    "Cambio de perfil de acceso de usuario",
                    sprintf('Se ha cambio el perfil de acceso del usuario %1$s',
                        $User->getUsername()
                    ),
                    false
                );
            }
            catch (Exception $e)
            {
                exit;
            }
	}

        return new JsonModel();
    }

    /*
     * Función para registrar un usuario del sistema
     */
    public function registerAction()
    {
        $request = $this->getRequest();
        $data = array(
            "showForm" => false,
            "error"    => false,
            "pkUser"   => 0,
            "username" => "",
            "email"    => "",
        );

        if ($request->isGet() && $request->getQuery('email') && $request->getQuery('active_code'))
	{
	    $User = $this->getRepository()->findBy(array("email" => trim($request->getQuery('email'))));
            if ($User && $User[0]->getActiveCode() == trim($request->getQuery('active_code')))
	    {
		$data['showForm'] = true;
		$data['username'] = $User[0]->getUsername();
                $data['email']    = $User[0]->getEmail();
                $data['pkUser']   = $User[0]->getPkUser();
	    }
	    else
	    {
		$data['error'] = true;
            }
	}

	if ($request->isPost())
	{
            $User = $this->getRepository()->find($request->getPost('id'));
            try
            {
                //REGISTRAR EL USUARIO
                $User->setPassword($request->getPost('password'));
                $User->setName($request->getPost('name'));
                $User->setActiveCode(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8));
                $this->getEntityManager()->persist($User);
                $this->getEntityManager()->flush();
                $this->transaction(
                    "Registro de usuario",
                    sprintf('El usuario %1$s ha registrado sus datos',
                        $User->getUsername()
                    ),
                    true
                );

                header('Location: ' . \Alae\Service\Helper::getVarsConfig("base_url"));
                exit;
            }
            catch (Exception $e)
            {
                exit;
            }
        }

	return new ViewModel($data);
    }

    /*
     * Función para dar de baja un usuario
     */
    public function rejectAction()
    {
	$request = $this->getRequest();

	if ($request->isGet())
	{
            $User = $this->getRepository()->find($request->getQuery('id'));
            try
            {
                //DAR DE BAJA AL USUARIO
                $Profile = $this->getRepository("\\Alae\\Entity\\Profile")->findBy(array("name" => "Sin asignar"));
                $User->setFkProfile($Profile[0]);
                $User->setActiveFlag(\Alae\Entity\User::USER_INACTIVE_FLAG);
                $this->getEntityManager()->persist($User);
                $this->getEntityManager()->flush();
                $this->transaction(
                    "Baja de usuario",
                    sprintf('Se ha dado de baja al usuario %1$s',
                        $User->getUsername()
                    ),
                    false
                );
            }
            catch (Exception $ex)
            {
                exit;
            }

	    return new JsonModel();
	}
    }

    /*
     * Función para generar firma electrónica
     */
    public function sentverificationAction()
    {
	$request = $this->getRequest();

	if ($request->isGet())
	{
            //GENERA LA FIRMA ELECTRONICA DEL USUARIO
            $verification = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8);

	    $User = $this->getRepository()->find($request->getQuery('id'));
	    $User->setVerification($verification);
	    $this->getEntityManager()->persist($User);
	    $this->getEntityManager()->flush();

            $this->transaction(
                "Generación de firma electrónica",
                sprintf('Se le ha generado la firma electrónica al usuario %1$s',
                    $User->getUsername()
                ),
                false
            );

	    $mail = new \Alae\Service\Mailing();
	    $mail->send(array($User->getEmail()), $this->render('alae/user/template_verification',
                    array('verification' => $verification, 'email' => $User->getEmail(), 'name' => $User->getEmail())),
                    'Generación de Firma Digital');
	}
    }

    /*
     * Función para resetear el password del usuario
     */
    public function resetpassAction()
    {
	$request = $this->getRequest();
	$message = '';
	$User = new \Alae\Entity\User();
	if ($request->isPost())
	{
	    $User = $this->getRepository()->findBy(array('username' => $request->getPost('username')));

	    if ($User && $User[0]->getPkUser())
	    {
                //RESETEA EL PASSWORD
		$User[0]->setActiveCode(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8));
		$this->getEntityManager()->persist($User[0]);
		$this->getEntityManager()->flush();
		$mail = new \Alae\Service\Mailing();
		$mail->send(
			array($User[0]->getEmail()),
			$this->render('alae/user/template_reset_pass', array(
				'active_code' => $User[0]->getActiveCode(),
				'link' => \Alae\Service\Helper::getVarsConfig("base_url") . '/user/newpassword',
				'username' => $User[0]->getUsername())),
			'Reinicializar contraseña'
		);
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

    /*
     * Función para asignar el nuevo password del usuario
     */
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
