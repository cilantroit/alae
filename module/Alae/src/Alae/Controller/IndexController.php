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

    public function init()
    {

    }

    public function logoutAction()
    {
        $User = $this->_getSession();

        $this->transaction(__METHOD__, "El usuario %s ha cerrado sesión", json_encode(array(
            "User"    => $User->getUsername(),
            "Name"    => $User->getName(),
            "Email"   => $User->getEmail(),
            "Profile" => $User->getFkProfile()->getName()
        )));

	$session_user = new \Zend\Session\Container('user');
	$session_user->getManager()->getStorage()->clear('user');

	return new ViewModel(array("username" => $User->getUsername()));
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
		    if ($element->getActiveFlag() == \Alae\Entity\User::USER_ACTIVE_FLAG)
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

}
