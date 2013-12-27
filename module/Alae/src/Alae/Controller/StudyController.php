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
    Zend\View\Model\JsonModel,
    Alae\Service\Datatable;

class StudyController extends BaseController
{
    protected $_document  = '\\Alae\\Entity\\Study';

    public function preDispatch()
    {
        //$this->authorization();
    }

    public function indexAction()
    {
        $data     = array();
        $elements = $this->getRepository()->findBy(array("status" => true));

        foreach ($elements as $study)
        {
            $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
            $data[]         = array(
                "code"        => $study->getCode(),
                "description" => $study->getDescription(),
                "date"        => $study->getCreatedAt(),
                "analyte"     => $counterAnalyte,
                "observation" => $study->getObservation(),
                "closed"      => $study->getCloseFlag(),
                "edit"        => $study->getPkStudy()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_STUDY);
        return new ViewModel($datatable->getDatatable());

//      $this->view->layout()->disableLayout();
//      $this->view->headTitle("Gestor de disponibilidad");
//     	$this->view->headLink()->appendStylesheet($this->view->baseUrl().'/plugin/cluetip-1.0.6/jquery.cluetip.css');
//		$this->view->headScript()->appendFile($this->view->baseUrl().'/plugin/cluetip-1.0.6/jquery.cluetip.min.js');
    }

    public function downloadAction()
    {
        $data     = array();
        $data[]   = array("Id", "Nombre Analito", "Abreviatura");
        $elements = $this->getRepository()->findBy(array("status" => true));

        foreach ($elements as $study)
        {
            $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
            $data[]         = array($study->getCode(), $study->getDescription(), $study->getCreatedAt(), $counterAnalyte, $study->getObservation(), $study->getCloseFlag());
        }

        return new JsonModel($data);
    }

    public function createAction()
    {

    }

    public function deleteAction()
    {

    }

    public function editAction()
    {

    }

    public function excelAction()
    {
        \Alae\Service\Download::excel("http://localhost/alae/public/study/download", "listado_de_estudios");
    }

    public function pdfAction()
    {
        \Alae\Service\Download::pdf("http://localhost/alae/public/study/download", "listado_de_estudios");
    }

    protected function counterAnalyte($pkStudy)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(a.fkAnalyte)
            FROM \Alae\Entity\AnalyteStudy a
            WHERE a.fkStudy = " . $pkStudy . "
            GROUP BY a.fkStudy");
        return $query->getSingleScalarResult();
    }
}