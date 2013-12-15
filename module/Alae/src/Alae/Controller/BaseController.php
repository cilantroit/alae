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
        $session = new \Zend\Session\Container('user');
        $session->id = $user->getPkUser();

        $audit = new \Alae\Entity\AuditSession();
        $audit->setFkUser($user);
        $this->getEntityManager()->persist($audit);
        $this->getEntityManager()->flush();
    }

    protected function _getSession()
    {
        $session = new \Zend\Session\Container('user');
        return $this->getRepository("\\Alae\\Entity\\User")->find(1);
    }

    protected function transaction($_method = false, $section = false, $description = false)
    {
        $audit = new \Alae\Entity\AuditTransaction();
        $audit->__prepare($_method, $section, $description);
        $audit->setFkUser($this->_getSession());
        $this->getEntityManager()->persist($audit);
        $this->getEntityManager()->flush();
    }

    protected function render($view, $params)
    {
        $renderer = $this->getServiceLocator()->get('ViewRenderer');
        return $renderer->render($view, $params);
    }

    protected function update()
    {

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

    protected function sessionError($error)
    {

    }

    protected function execute($sql)
    {
        $query = $this->getEntityManager()->createQuery($sql);
        $response = $query->execute();
    }

}
