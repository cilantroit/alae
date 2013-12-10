<?php

namespace Alae;

return array(
    'controllers' => array(
        'invokables' => array(
            'Alae\Controller\Alae' => 'Alae\Controller\AlaeController',
            'Alae\Controller\Index' => 'Alae\Controller\IndexController',
            'Alae\Controller\User' => 'Alae\Controller\UserController',
            'Alae\Controller\Cron'    => 'Alae\Controller\CronController',
            'Alae\Controller\Analyte' => 'Alae\Controller\AnalyteController',
        ),
    ),
    // The following section is new and should be added to your file
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
                    'route' => '/user[/][:action][/:id][?:usr][&:pass]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
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
                    'route' => '/[/:action][/:message]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Alae\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'analyte' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'       => '/analyte[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults'    => array(
                        'controller' => 'Alae\Controller\Analyte',
                        'action'     => 'index',
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
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
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



