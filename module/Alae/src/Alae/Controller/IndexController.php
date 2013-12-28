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

    public function indexAction()
    {
	$request = $this->getRequest();






	//var_dump(getParam('message'));
	//$message = getParam('message');
	//$message = $this->getEvent()->getRouteMatch()->getParam('message');
	//$message = $request->getQuery('message');
	//$message = $request->getRouteMatch()->getQuery('message');
	//var_dump($request->getEvent()->getRouteMatch());


	$slug = $this->getEvent()->getRouteMatch()->getParam('message');









	return new ViewModel(array('message' => $slug));
    }

    public function menuAction()
    {
	return new ViewModel();
    }

}
