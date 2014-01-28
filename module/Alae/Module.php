<?php

namespace Alae;

use Zend\Mvc\ModuleRouteListener,
    Zend\Mvc\MvcEvent,
    Zend\ModuleManager\Feature\ConfigProviderInterface,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements ConfigProviderInterface, AutoloaderProviderInterface
{

//    public function onBootstrap(MvcEvent $e)
//    {
//        $eventManager        = $e->getApplication()->getEventManager();
//        $moduleRouteListener = new ModuleRouteListener();
//        $moduleRouteListener->attach($eventManager);
//        $eventManager->getSharedManager()->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, 'preDispatch', 100);
//    }

    public function getConfig()
    {
	return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
	return array(
	    'Zend\Loader\StandardAutoloader' => array(
		'namespaces' => array(
		    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
		),
	    ),
	);
    }
}
