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
    Zend\View\Model\JsonModel;

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

            if (count($elements) == 1)
            {
                foreach ($elements as $element)
                {
                    if ($element->getActiveFlag() == \Alae\Entity\User::USER_ACTIVE_FLAG)
                    {
                        $this->_setSession($element);

                        return $this->redirect()->toRoute('index', array('action' => 'menu'));
                    }
                }
            }
        }

        return $this->redirect()->toRoute('index', array('action' => 'index', 'message' => $message));
    }

    public function newaccountAction()
    {

        $request = $this->getRequest();

        /*
         * Los campos son obligatorios en el formulario,
         * si no están los dos, no puede ingresar nada (No te preocupes con data extraña, en la validacion por jquery lo limpiaremos
         * ademas Doctrine no te permite sql inyection.
         */


        if ($request->isPost())
        {

            /*
             * Lo que pasamos a doctrine es el objeto User, con la data cargada
             */
            $user = new \Alae\Entity\User();

            $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ9879"), 0, 8);

            /*  DATA */
            $user->setUsername($request->getPost('username'));
            $user->setEmail($request->getPost('email'));
            $user->setPassword($password);
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();

            if ($user->getPkUser())
            {
                $mail = new \Alae\Service\Mailing();
                $mail->send(array($user->getEmail()), $this->render('alae/user/template', array('password' => $user->getPassword(), 'user' => $user->getUsername())));
            }
        }
        else
        {
            //te regresa a la página
        }
    }

    public function ajaxuserexistAction()
    {
        $response = array('success' => true);
        if ($_GET)
        {
            $username = $_GET['usr'];
            $elements = $this->getRepository()
                    ->findBy(array('username' => $username));

            if (count($elements) > 0)
            {
                $response = array('success' => false);
            }
        }
        return new JsonModel($response);
    }

    public function ajaxemailexistAction()
    {
        $response = array('success' => true);
        if ($_GET)
        {
            $email = $_GET['email'];
            $elements = $this->getRepository()
                    ->findBy(array('email' => $email));



            if (count($elements) > 0)
            {
                $response = array('success' => false);
            }
        }
        return new JsonModel($response);
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




//	echo $slug = $this->getEvent()->getRouteMatch()->getParam('usr');
//	echo $slug = $this->getEvent()->getRouteMatch()->getParam('pass');
    }

}

