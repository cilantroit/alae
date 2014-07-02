<?php

 /**
 * Controlador padre con funciones comunes. Este fichero contiene clases abstractas encargadas de
  * generar funciones generales dentro del sistema.
  * Esas funciones son: Control de sesiones, control de entidades, control de las transacciones audit
 * @author Maria Quiroz
  * Fecha de creación: 10/05/2014
 */

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
        //ENVIA RESPUESTA DE JQUERY
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
        //INGRESA EL USUARIO A LAS VARIABLES DE SESSION
        $config = new \Zend\Session\Config\StandardConfig();
        $config->setOptions(array(
            'remember_me_seconds' => 900000,
            'name'                => 'zf2',
        ));
        $manager = new \Zend\Session\SessionManager($config);
	$session = new \Zend\Session\Container('user', $manager);
        $session->id = $user->getPkUser();
	$session->name = $user->getName();
	$session->profile = $user->getFkProfile()->getName();

        //INDICA EN EL AUDIT TRAIL EL INICIO DE SESIÓN
        $this->transaction(
            "Inicio de sesión",
            sprintf("El usuario %s ha iniciado sesión", $user->getUsername()),
            false
        );
    }

    protected function _getSession()
    {
        //OBTIENE EL INICIO DE SESION
	$session = new \Zend\Session\Container('user');
        if ($session->offsetExists('id'))
	{
            return $this->getRepository("\\Alae\\Entity\\User")->find($session->id);
        }
        return false;
    }

    protected function transaction($section, $description, $system = false)
    {
        //OBTIENE LA TRANSACCION
        $user = $system ? $this->_getSystem() : $this->_getSession();
	$audit = new \Alae\Entity\AuditTransaction();
        $audit->setSection($section);
        $audit->setDescription($description);
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
        //OBTIENE LOS ERRORES DE TRANSACCION
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
        //VERIFICA EL USUARIO LOGUINADO
        $session = new \Zend\Session\Container('user');
        return $session->id ? true : false;
    }
}
