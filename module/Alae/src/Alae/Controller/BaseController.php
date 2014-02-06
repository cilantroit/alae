<?php

namespace Alae\Controller;

use Zend\View\Model\JsonModel,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Controller\AbstractActionController,
    Zend\EventManager\EventManagerInterface,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping;

abstract class BaseController extends AbstractActionController
{
    protected $_em;
    protected $_repository;
    protected $_document;

    public function setEventManager(\Zend\EventManager\EventManagerInterface $events)
    {
        parent::setEventManager($events);
        $this->init();
    }

    protected function sendResponse($data)
    {
	$jsonModel = new JsonModel($data);
	if ($this->getRequest()->getQuery('callback'))
	    $jsonModel->setJsonpCallback($this->getRequest()->getQuery('callback'));
	return $jsonModel;
    }

    public function setEntityManager(EntityManager $em)
    {
	$this->_em = $em;
    }

    public function getEntityManager()
    {
	if (null === $this->_em)
	    $this->_em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	return $this->_em;
    }

    protected function getRepository($repository = null)
    {
	if (is_null($repository))
	    $repository = $this->_document;
	return $this->getEntityManager()->getRepository($repository);
    }

    protected function _setSession(\Alae\Entity\User $user)
    {
        $config = new \Zend\Session\Config\StandardConfig();
        $config->setOptions(array(
            'remember_me_seconds' => 900,
            'name'                => 'zf2',
        ));
        $manager = new \Zend\Session\SessionManager($config);
	$session = new \Zend\Session\Container('user', $manager);
        $session->id = $user->getPkUser();
	$session->name = $user->getName();
	$session->profile = $user->getFkProfile()->getName();

        $this->transaction(__METHOD__, "El usuario %s ha iniciado sesión", json_encode(array(
            "User"    => $user->getUsername(),
            "Name"    => $user->getName(),
            "Email"   => $user->getEmail(),
            "Profile" => $user->getFkProfile()->getName()
        )));
    }

    protected function _getSession()
    {
	$session = new \Zend\Session\Container('user');
	return $this->getRepository("\\Alae\\Entity\\User")->find($session->id);
    }

    protected function transaction($_method = false, $section = false, $description = false, $system = false)
    {
        $user = $system ? $this->_getSystem() : $this->_getSession();
	$audit = new \Alae\Entity\AuditTransaction();
	$audit->__prepare($_method, $section, $description);
	$audit->setFkUser($user);
	$this->getEntityManager()->persist($audit);
	$this->getEntityManager()->flush();
    }

    protected function render($view, $params)
    {
	$renderer = $this->getServiceLocator()->get('ViewRenderer');
	return $renderer->render($view, $params);
    }

    protected function _getSystem()
    {
	return $this->getRepository("\\Alae\\Entity\\User")->find(1);
    }

    protected function transactionError($data, $system = false)
    {
	$user = $system ? $this->_getSystem() : $this->_getSession();

	$audit = new \Alae\Entity\AuditTransactionError();
	$audit->setDescription($data['description']);
	$audit->setMessage($data['message']);
	$audit->setSection($data['section']);
	$audit->setFkUser($user);
	$this->getEntityManager()->persist($audit);
	$this->getEntityManager()->flush();
    }

    protected function execute($sql)
    {
	$query = $this->getEntityManager()->createQuery($sql);
	return $query->execute();
    }

    protected function isLogged()
    {
        $session = new \Zend\Session\Container('user');
        return $session->id ? true : false;
    }
}
