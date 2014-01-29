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

    protected $_document = '\\Alae\\Entity\\Study';

    public function init()
    {
        if (!$this->isLogged())
        {
            header('Location: ' . \Alae\Service\Helper::getVarsConfig("base_url"));
            exit;
        }
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
                "closed"      => $study->getCloseFlag() ? "S" : "N",
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

    public function createAction()
    {
        $request = $this->getRequest();

        if ($request->isPost())
        {
            $User = $this->_getSession();

            /*
             * Creación de los datos básicos del estudio
             */
            try
            {
                $Study = new \Alae\Entity\Study();
                $Study->setCode($request->getPost('code'));
                $Study->setDescription($request->getPost('description'));
                $Study->setCreatedAt(new \DateTime('now'));
                $Study->setObservation($request->getPost('observation'));
                $Study->setFkDilutionTree($request->getPost('dilution_tree'));
                $Study->setStatus(true);
                $Study->setFkUser($User);
                $this->getEntityManager()->persist($Study);
                $this->getEntityManager()->flush();
                $this->transaction(__METHOD__, sprintf("Ingreso del estudio #%s", $Study->getCode()), json_encode(array(
                    "User"         => $User->getUsername(),
                    "Code"         => $Study->getCode(),
                    "Description"  => $Study->getDescription(),
                    "Observation"  => $Study->getObservation(),
                    "DilutionTree" => $Study->getFkDilutionTree()
                )));
                return $this->redirect()->toRoute('study', array(
                            'controller' => 'study',
                            'action'     => 'edit',
                            'id'         => $Study->getPkStudy()
                ));
            }
            catch (Exception $e)
            {
                $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(array(
                    "User"         => $User->getUsername(),
                    "Code"         => $request->getPost('code'),
                    "Description"  => $request->getPost('description'),
                    "Observation"  => $request->getPost('observation'),
                    "DilutionTree" => $request->getPost('dilution_tree')
                )));
                $error   = array(
                    "description" => $message,
                    "message"     => $e,
                    "section"     => __METHOD__
                );
                $this->transactionError($error);
            }
        }
    }

    public function deleteanastudyAction()
    {
        $request = $this->getRequest();

        if ($request->isGet())
        {
            $AnaStudy = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->find($request->getQuery('pk'));
            if ($AnaStudy && $AnaStudy->getPkAnalyteStudy())
            {
                try
                {
                    $message = sprintf("Se ha descativado el analito %s del estudio %s", $AnaStudy->getFkAnalyte()->getShortening(), $AnaStudy->getFkStudy()->getCode());
                    $data    = json_encode(array(
                        "CsNumber"        => $AnaStudy->getCsNumber(),
                        "QcNumber"        => $AnaStudy->getQcNumber(),
                        "InternalStandar" => $AnaStudy->getInternalStandard(),
                        "Use"             => $AnaStudy->getImportedFlag()
                    ));
                    $this->getEntityManager()->remove($AnaStudy);
                    $this->getEntityManager()->flush();
                    $this->transaction(__METHOD__, $message, $data);
                    return new JsonModel(array("status" => true));
                }
                catch (Exception $e)
                {
                    $message = sprintf("Se ha presentado un error al desactivar el analito %s del estudio %s", $AnaStudy->getFkAnalyte()->getShortening(), $AnaStudy->getFkStudy()->getCode());
                    $error   = array(
                        "description" => $message,
                        "message"     => $e,
                        "section"     => __METHOD__
                    );
                    $this->transactionError($error);
                    return new JsonModel(array("status" => false, "message" => $message));
                }
            }
        }
    }

    public function editAction()
    {
        $request = $this->getRequest();

        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $Study = $this->getRepository()->find($this->getEvent()->getRouteMatch()->getParam('id'));
        }

        if ($request->isPost())
        {
            $User  = $this->_getSession();
            $Study = $this->getRepository()->find($request->getPost('study_id'));

            /*
             * Creación de los datos básicos del estudio
             */
            if ($request->getPost('form') == 1)
            {
                try
                {
                    $Study->setCode($request->getPost('code'));
                    $Study->setDescription($request->getPost('description'));
                    $Study->setObservation($request->getPost('observation'));
                    $Study->setFkDilutionTree($request->getPost('dilution_tree'));
                    $Study->setFkUser($User);
                    $this->getEntityManager()->persist($Study);
                    $this->getEntityManager()->flush();
                    $this->transaction(__METHOD__, sprintf("Ingreso del estudio #%s", $Study->getCode()), json_encode(array(
                        "User"         => $User->getUsername(),
                        "Code"         => $Study->getCode(),
                        "Description"  => $Study->getDescription(),
                        "Observation"  => $Study->getObservation(),
                        "DilutionTree" => $Study->getFkDilutionTree()
                    )));
                }
                catch (Exception $e)
                {
                    $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(array(
                        "User"         => $User->getUsername(),
                        "Code"         => $request->getPost('code'),
                        "Description"  => $request->getPost('description'),
                        "Observation"  => $request->getPost('observation'),
                        "DilutionTree" => $request->getPost('dilution_tree')
                    )));
                    $error   = array(
                        "description" => $message,
                        "message"     => $e,
                        "section"     => __METHOD__
                    );
                    $this->transactionError($error);
                }
            }

            /*
             * Asociaciones de analitos y ediciones de los existentes
             */
            if ($request->getPost('form') == 2)
            {
                $createAnalyte   = $request->getPost('create-analyte');
                $createAnalyteIs = $request->getPost('create-analyte_is');
                $createCsNumber  = $request->getPost('create-cs_number');
                $createQcNumber  = $request->getPost('create-qc_number');
                $createUnit      = $request->getPost('create-unit');
                $createIs        = $request->getPost('create-is');
                $createUse       = $request->getPost('create-use');
                $updateCsNumber  = $request->getPost('update-cs_number');
                $updateQcNumber  = $request->getPost('update-qc_number');
                $updateIs        = $request->getPost('update-is');
                $updateUse       = $request->getPost('update-use');

                if (!empty($createAnalyte))
                {
                    foreach ($createAnalyte as $key => $value)
                    {
                        $Analyte   = $this->getRepository('\\Alae\\Entity\\Analyte')->find($value);
                        $AnalyteIs = $this->getRepository('\\Alae\\Entity\\Analyte')->find($createAnalyteIs[$key]);
                        $Unit      = $this->getRepository('\\Alae\\Entity\\Unit')->find($createUnit[$key]);

                        try
                        {
                            $AnaStudy = new \Alae\Entity\AnalyteStudy();
                            $AnaStudy->setFkAnalyte($Analyte);
                            $AnaStudy->setFkAnalyteIs($AnalyteIs);
                            $AnaStudy->setFkStudy($Study);
                            $AnaStudy->setCsNumber($createCsNumber[$key]);
                            $AnaStudy->setQcNumber($createQcNumber[$key]);
                            $AnaStudy->setFkUnit($Unit);
                            $AnaStudy->setInternalStandard($createIs[$key]);
                            $AnaStudy->setIsUsed((isset($createUse[$key]) ? true : false));
                            $AnaStudy->setStatus(true);
                            $AnaStudy->setFkUser($User);
                            $this->getEntityManager()->persist($AnaStudy);
                            $this->getEntityManager()->flush();
                            $this->transaction(__METHOD__, sprintf("Asociación del analito (%s) con el estudio (%s)", $Analyte->getShortening(), $Study->getCode()), json_encode(array(
                                "User"            => $User->getUsername(),
                                "Analyte"         => $Analyte->getName(),
                                "AnalyteIS"       => $AnalyteIs->getName(),
                                "Study"           => $Study->getCode(),
                                "CsNumber"        => $AnaStudy->getCsNumber(),
                                "QcNumber"        => $AnaStudy->getQcNumber(),
                                "Unit"            => $AnaStudy->getFkUnit()->getName(),
                                "InternalStandar" => $AnaStudy->getInternalStandard(),
                                "Use"             => $AnaStudy->getIsUsed()
                            )));
                        }
                        catch (Exception $e)
                        {
                            $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(array(
                                "User"            => $User->getUsername(),
                                "Analyte"         => $Analyte->getName(),
                                "AnalyteIS"       => $AnalyteIs->getName(),
                                "Study"           => $Study->getCode(),
                                "CsNumber"        => $createCsNumber[$key],
                                "QcNumber"        => $createQcNumber[$key],
                                "Unit"            => $Unit->getName(),
                                "InternalStandar" => $createIs[$key],
                                "Use"             => (isset($createUse[$key]) ? true : false)
                            )));
                            $error   = array(
                                "description" => $message,
                                "message"     => $e,
                                "section"     => __METHOD__
                            );
                            $this->transactionError($error);
                        }
                    }
                }

                if (!empty($updateCsNumber))
                {
                    foreach ($updateCsNumber as $key => $value)
                    {
                        $AnaStudy = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->find($key);

                        if ($AnaStudy && $AnaStudy->getPkAnalyteStudy())
                        {
                            try
                            {
                                $older = array(
                                    "CsNumber"        => $AnaStudy->getCsNumber(),
                                    "QcNumber"        => $AnaStudy->getQcNumber(),
                                    "InternalStandar" => $AnaStudy->getInternalStandard(),
                                    "Use"             => $AnaStudy->getIsUsed()
                                );
                                $AnaStudy->setCsNumber($updateCsNumber[$key]);
                                $AnaStudy->setQcNumber($updateQcNumber[$key]);
                                $AnaStudy->setInternalStandard($updateIs[$key]);
                                $AnaStudy->setIsUsed(isset($updateUse[$key]) ? true : false);
                                $this->getEntityManager()->persist($AnaStudy);
                                $this->getEntityManager()->flush();

                                $audit = array(
                                    "Antiguos valores" => $older,
                                    "Nuevos valores"   => array(
                                        "User"            => $User->getUsername(),
                                        "CsNumber"        => $AnaStudy->getCsNumber(),
                                        "QcNumber"        => $AnaStudy->getQcNumber(),
                                        "InternalStandar" => $AnaStudy->getInternalStandard(),
                                        "Use"             => $AnaStudy->getIsUsed()
                                    )
                                );
                                $this->transaction(__METHOD__, sprintf("Asociación del analito (%s) con el estudio (%s)", $AnaStudy->getFkAnalyte()->getShortening(), $AnaStudy->getFkStudy()->getCode()), json_encode($audit));
                            }
                            catch (Exception $e)
                            {
                                $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(array(
                                    "Id"              => $AnaStudy->getPkAnalyteStudy(),
                                    "User"            => $User->getUsername(),
                                    "CsNumber"        => $updateCsNumber[$key],
                                    "QcNumber"        => $updateQcNumber[$key],
                                    "InternalStandar" => $updateIs[$key],
                                    "Use"             => isset($updateUse[$key]) ? true : false
                                )));
                                $error   = array(
                                    "description" => $message,
                                    "message"     => $e,
                                    "section"     => __METHOD__
                                );
                                $this->transactionError($error);
                            }
                        }
                    }
                }
            }
        }

        $data     = array();
        $elements = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkStudy" => $Study->getPkStudy()));

        foreach ($elements as $anaStudy)
        {
            $data[] = array(
                "analyte"    => $anaStudy->getFkAnalyte()->getShortening(),
                "analyte_is" => $anaStudy->getFkAnalyteIs()->getShortening(),
                "cs_number"  => $anaStudy->getCsNumber(),
                "qc_number"  => $anaStudy->getQcNumber(),
                "unit"       => $anaStudy->getFkUnit()->getName(),
                "is"         => $anaStudy->getInternalStandard(),
                "use"        => $anaStudy->getIsUsed(),
                "edit"       => $anaStudy->getPkAnalyteStudy()
            );
        }

        $Analyte   = $this->getRepository('\\Alae\\Entity\\Analyte')->findAll();
        $Unit      = $this->getRepository('\\Alae\\Entity\\Unit')->findAll();
        $datatable = new Datatable($data, Datatable::DATATABLE_ANASTUDY);
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('study', $Study);
        $viewModel->setVariable('analytes', $Analyte);
        $viewModel->setVariable('units', $Unit);
        return $viewModel;
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $Study = $this->getRepository()->find($request->getQuery('pk'));
            if ($Study && $Study->getPkStudy())
            {
                try
                {
                    $User = $this->_getSession();
                    $Study->setStatus(false);
                    $Study->setFkUser($User);
                    $this->getEntityManager()->persist($Study);
                    $this->getEntityManager()->flush();
                    $this->transaction(__METHOD__, sprintf("Se ha descativado el estudio con código #%d", $Study->getCode()), json_encode(array(
                        "User"        => $User->getUsername(),
                        "Code"        => $Study->getCode(),
                        "Description" => $Study->getDescription(),
                        "Observation" => $Study->getObservation()
                    )));
                    return new JsonModel(array("status" => true));
                }
                catch (Exception $e)
                {
                    $message = sprintf("Se ha presentado un error al desactivar el estudio con identificador #%d", $Study->getCode());
                    $error   = array(
                        "description" => $message,
                        "message"     => $e,
                        "section"     => __METHOD__
                    );
                    $this->transactionError($error);
                    return new JsonModel(array("status" => false, "message" => $message));
                }
            }
        }
    }

    public function downloadAction()
    {
        $data     = array();
        $data[]   = array("Código", "Descripción", "Fecha", "Nº Analitos", "Observaciones", "Cerrado (S/N)");
        $elements = $this->getRepository()->findBy(array("status" => true));

        foreach ($elements as $study)
        {
            $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
            $data[]         = array(
                $study->getCode(),
                $study->getDescription(),
                $study->getCreatedAt(),
                $counterAnalyte,
                $study->getObservation(),
                $study->getCloseFlag() ? "S" : "N"
            );
        }

        return new JsonModel($data);
    }

    public function excelAction()
    {
        \Alae\Service\Download::excel(\Alae\Service\Helper::getVarsConfig("base_url") . "/study/download", "listado_de_estudios");
    }

    public function approveAction()
    {

    }

    public function closeAction()
    {

    }

    public function duplicateAction()
    {
        //ejemplo AAE
    }

    /**
     * Approve: Nominal Concentration
     */
    public function approvencAction()
    {

    }

    public function unlockAction()
    {

    }

    public function nominalconcentrationAction()
    {
        $request = $this->getRequest();

        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->find($this->getEvent()->getRouteMatch()->getParam('id'));
        }

        if ($request->isPost())
        {
            $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->find($request->getPost('id'));
            $AnaStudy->setCsValues(implode(",", $request->getPost("cs_number")));
            $AnaStudy->setQcValues(implode(",", $request->getPost("qc_number")));
            $this->getEntityManager()->persist($AnaStudy);
            $this->getEntityManager()->flush();
        }

        $viewModel = new ViewModel();
        $viewModel->setVariable('AnaStudy', $AnaStudy);
        $viewModel->setVariable('cs_number', explode(",", $AnaStudy->getCsValues()));
        $viewModel->setVariable('qc_number', explode(",", $AnaStudy->getQcValues()));
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