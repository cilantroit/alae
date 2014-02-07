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

class ReportController extends BaseController
{
    public function init()
    {
        if (!$this->isLogged())
        {
            header('Location: ' . \Alae\Service\Helper::getVarsConfig("base_url"));
            exit;
        }
    }

    public function auditAction()
    {
        $query = $this->getEntityManager()->createQuery("
                SELECT a
                FROM Alae\Entity\AuditTransaction a
                ORDER BY a.createdAt DESC");
        $elements = $query->getResult();
        $data = array();
        foreach ($elements as $AuditTransaction)
        {
            $data[] = array(
                "created_at"        => $AuditTransaction->getCreatedAt(),
                "section"           => $AuditTransaction->getSection(),
                "audit_description" => $AuditTransaction->getDescription(),
                "user"              => $AuditTransaction->getFkUser()->getUsername()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_AUDIT_TRAIL);
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('user', $this->_getSession());
        return $viewModel;
    }

    public function ajaxAction()
    {
        $elements = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkStudy" => $this->getEvent()->getRouteMatch()->getParam('id')));
        $data = "";
        foreach ($elements as $anaStudy)
        {
            $data .= '<option value="'.$anaStudy->getFkAnalyte()->getPkAnalyte().'">'.$anaStudy->getFkAnalyte()->getName().'</option>';
        }

        return new JsonModel(array("data" => $data));
    }

    public function indexAction()
    {
        $elements = $this->getRepository("\\Alae\\Entity\\Study")->findBy(array("status" => true));

        return new ViewModel(array("studies" => $elements));
    }

    /**
     * Información General del Estudio (pdf)
     */
    public function r1Action()
    {
        $viewModel = new ViewModel();

        $data     = array();
        $study = $this->getRepository()->find($this->getEvent()->getRouteMatch()->getParam('id'));
        $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
        $data[]         = array(
            "code"     => $study->getCode(),
            "analyte"  => $counterAnalyte,
            "dilution" => $study->getFkDilutionTree()
        );
        $viewModel->setVariable('study', $data);




        return $viewModel;
    }

    protected function counterAnalyte($pkStudy)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(a.fkAnalyte)
            FROM \Alae\Entity\AnalyteStudy a
            WHERE a.fkStudy = " . $pkStudy . "
            GROUP BY a.fkStudy");
        $response = $query->execute();
        return $response ? $query->getSingleScalarResult() : 0;
    }
}