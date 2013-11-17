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
    Zend\Mail,
    Zend\Mime\Part as MimePart,
    Zend\Mime\Message as MimeMessage;

class UserController extends BaseController
{

    protected $_document = '\\Alae\\Entity\\User';

    public function loginAction()
    {
	$request = $this->getRequest();

	//$message =

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
	$usr = htmlentities($request->getPost('username'));
	$mailing = htmlentities($request->getPost('email'));
	if ($request->isPost())
	{
	    if ((!empty($usr)) && (!empty($mailing)))
	    {

		$elements = $this->getRepository()->setUsername($usr)
			->setEmail($mailing);


		$this->getEntityManager()->persist($elements);
		$this->getEntityManager()->flush();
		return $this->sendResponse(array('id' => $elements->getId()));


		$options = new Mail\Transport\SmtpOptions(array(
		    'name' => 'localhost',
		    'host' => 'smtp.gmail.com',
		    'port' => 587,
		    'connection_class' => 'login',
		    'connection_config' => array(
			'username' => 'daniel.farnos.e@gmail.com',
			'password' => '335044__df123',
			'ssl' => 'tls',
		    ),
		));

		$this->renderer = $this->getServiceLocator()->get('ViewRenderer');
		$content = $this->renderer->render('alae/user/template', null);

		// make a header as html
		$html = new MimePart($content);
		$html->type = "text/html";
		$body = new MimeMessage();
		$body->setParts(array($html,));

		// instance mail
		$mail = new Mail\Message();
		$mail->setBody($body); // will generate our code html from template.phtml
		$mail->setFrom('daniel.farnos.e@gmail.com', 'Alae');
		$mail->setTo($mailing);
		$mail->setSubject('Alae');

		$transport = new Mail\Transport\Smtp($options);
		$transport->send($mail);
	    }
	}
	else
	{
	    //te regresa a la página
	}
    }

}

