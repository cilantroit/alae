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
    Alae\Service\Helper as Helper;

class AnalyteController extends BaseController
{
    protected $_datatable = 'analyte';

    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost())
        {
            //$request->getPost('campo')
            echo "captura el POST";
            var_dump($_POST);
        }

        $data = array(
            array("id" => "1", "name" => "Anapharina", "shortname" => "ANA", "edit" => "1"),
            array("id" => "2", "name" => "Anapharina-d5", "shortname" => "ANA-d5", "edit" => "2"),
            array("id" => "3", "name" => "4-Hidroxyanapharina", "shortname" => "OHANA", "edit" => "3")
        );

        $view = new ViewModel(array(
            'data' => $data,
            'columns'  => \Alae\Service\Datatable::getColumns($this->_datatable),
            'filters'  => \Alae\Service\Datatable::getFilters($data, $this->_datatable),
            'editable' => \Alae\Service\Datatable::editable($this->_datatable)
        ));

        return $view;
    }


}