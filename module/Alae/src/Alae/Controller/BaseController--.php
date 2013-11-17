<?php

namespace Alae\Controller;

use Zend\View\Model\JsonModel,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Controller\AbstractActionController,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping;

abstract class BaseController extends AbstractActionController
{

    protected $_em;
    protected $_repository;
    protected $_document;

    protected function sendResponse($data)
    {
	$jsonModel = new JsonModel($data);
	if ($this->getRequest()->getQuery('callback'))
	{
	    $jsonModel->setJsonpCallback($this->getRequest()->getQuery('callback'));
	}
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
	{
	    $repository = $this->_document;
	}
	return $this->getEntityManager()->getRepository($repository);
    }

}
