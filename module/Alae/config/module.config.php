<?php
/* APLICACION ALAE
   Fichero de control de enlaces del sistema
   Autor: María Quiroz
   Fecha de creación: 10/05/2014
*/
namespace Alae;
// Controladores
return array(
    'controllers' => array(
	'invokables' => array(
	    'Alae\Controller\Alae' => 'Alae\Controller\AlaeController',
	    'Alae\Controller\Index' => 'Alae\Controller\IndexController',
	    'Alae\Controller\User' => 'Alae\Controller\UserController',
	    'Alae\Controller\Cron' => 'Alae\Controller\CronController',
	    'Alae\Controller\Analyte' => 'Alae\Controller\AnalyteController',
	    'Alae\Controller\Parameter' => 'Alae\Controller\ParameterController',
	    'Alae\Controller\Study' => 'Alae\Controller\StudyController',
	    'Alae\Controller\Batch' => 'Alae\Controller\BatchController',
	    'Alae\Controller\Verification' => 'Alae\Controller\VerificationController',
	    'Alae\Controller\Report' => 'Alae\Controller\ReportController',
	),
    ),
    // Secciones de las rutas
    'router' => array(
	'routes' => array(
	    'cron' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/cron[/][:action]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Cron',
			'action' => 'read',
		    ),
		),
	    ),
	    'user' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/user[/][:action][/:id][/:profile][?:usr][&:pass]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
			'profile' => '[a-zA-Z][a-zA-Z0-9_-]*',
			'pass' => '[a-zA-Z][a-zA-Z0-9_-]*',
			'usr' => '[a-zA-Z][a-zA-Z0-9_-]*',
			'pass' => '[a-zA-Z][a-zA-Z0-9_-]*',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\User',
			'action' => 'index',
		    ),
		),
	    ),
	    'index' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/[index/:action]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Index',
			'action' => 'login',
		    ),
		),
	    ),
	    'analyte' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/analyte[/][:action][/:id]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Analyte',
			'action' => 'index',
		    ),
		),
	    ),
	    'parameter' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/parameter[/][:action][/][:param]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Parameter',
			'action' => 'index',
		    ),
		),
	    ),
	    'study' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/study[/][:action][/][:id]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
			'id' => '[0-9]+',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Study',
			'action' => 'index',
		    ),
		),
	    ),
	    'batch' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/batch[/][:action][/:id]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Batch',
			'action' => 'index',
		    ),
		),
	    ),
	    'verification' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/verification[/][:action][/:id]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Verification',
			'action' => 'index',
		    ),
		),
	    ),
	    'report' => array(
		'type' => 'segment',
		'options' => array(
		    'route' => '/report[/][:action][/:id][/:an]',
		    'constraints' => array(
			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
			'id' => '[0-9]+',
			'an' => '[0-9]+',
		    ),
		    'defaults' => array(
			'controller' => 'Alae\Controller\Report',
			'action' => 'audit',
		    ),
		),
	    ),
	),
    ),
    'view_manager' => array(
	'display_not_found_reason' => true,
	'display_exceptions' => true,
	'doctype' => 'HTML5',
	/* 'not_found_template' => 'error/404',
	  'exception_template' => 'error/index',
	 *
	 */
	'template_map' => array(
	    'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
	    'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
	/* 'error/404' => __DIR__ . '/../view/error/404.phtml',
	  'error/index' => __DIR__ . '/../view/error/index.phtml', */
	),
	'template_path_stack' => array(
	    'alae' => __DIR__ . '/../view',
	),
	'strategies' => array(
	    'ViewJsonStrategy',
	),
    ),
    'doctrine' => array(
	'driver' => array(
	    __NAMESPACE__ . '_driver' => array(
		'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
		'cache' => 'array',
		'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
	    ),
	    'orm_default' => array(
		'drivers' => array(
		    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
		)
	    )
	)
    ),
    'configuration' => array(
	'orm_default' => array(
//            'numeric_functions'  => array(),
//            'datetime_functions' => array(),
	    'string_functions' => array(
		'REGEXP' => 'DoctrineExtensions\Query\Mysql\Regexp'
	    ),
//            'metadata_cache'     => 'filesystem',
//            'query_cache'        => 'filesystem',
//            'result_cache'       => 'filesystem',
	)
    ),
    'console' => array(
	'router' => array(
	    'routes' => array(
		'cronroute' => array(
		    'options' => array(
			'route' => 'checkdirectory',
			'defaults' => array(
			    'controller' => 'Alae\Controller\Cron',
			    'action' => 'read'
			)
		    )
		)
	    )
	)
    )
);



