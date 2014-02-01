<?php
/**
 * Description of BatchController
 *
 * @author Maria Quiroz
 */

namespace Alae\Controller;

use Zend\View\Model\ViewModel,
    Alae\Controller\BaseController,
    Zend\View\Model\JsonModel,
    Alae\Service\Datatable;

class BatchController extends BaseController
{
    protected $_document = '\\Alae\\Entity\\Batch';

    public function init()
    {
        if (!$this->isLogged())
        {
            header('Location: ' . \Alae\Service\Helper::getVarsConfig("base_url"));
            exit;
        }
    }

    public function unfilledAction()
    {
        $data     = array();
        $elements = $this->getRepository()->findBy(array("fkAnalyte" => null, "fkStudy" => null));

        foreach ($elements as $unfilled)
        {
            if (!is_null($unfilled->getFkParameter()))
            {
                $data[] = array(
                    "batch"     => $unfilled->getSerial(),
                    "filename"  => $unfilled->getFileName(),
                    "create_at" => $unfilled->getCreatedAt(),
                    "reason"    => $unfilled->getFkParameter()->getMessageError()
                );
            }
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_UNFILLED);
        return new ViewModel($datatable->getDatatable());
    }

    public function downloadAction()
    {
        $data     = array();
        $data[]   = array("# Lote", "Nombre del archivo", "Importado el", "Motivo de descarte");
        $elements = $this->getRepository()->findBy(array("fkAnalyte" => null, "fkStudy" => null));

        foreach ($elements as $unfilled)
        {
            $data[] = array(
                $unfilled->getSerial(),
                $unfilled->getFileName(),
                $unfilled->getCreatedAt(),
                $unfilled->getFkParameter()->getMessageError()
            );
        }

        return new JsonModel($data);
    }

    public function excelAction()
    {
        \Alae\Service\Download::excel(\Alae\Service\Helper::getVarsConfig("base_url") . "/batch/download", "lotes_sin_asignar");
    }

    public function listAction()
    {
        $request = $this->getRequest();

        if ($request->isPost())
        {
            $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->find($request->getPost('id'));
            $updateJustification = $request->getPost('update-justification');
            $updateAcceptedFlag  = $request->getPost('update-accepted_flag');

            if (!empty($updateJustification))
            {
                foreach ($updateJustification as $key => $value)
                {
                    $Batch = $this->getRepository()->find($key);

                    if ($Batch && $Batch->getPkBatch())
                    {
                        try
                        {
                            $Batch->setValidFlag((bool)$updateAcceptedFlag[$key]);
                            $Batch->setAcceptedFlag((bool)$updateAcceptedFlag[$key]);
                            $Batch->setJustification($updateJustification[$key]);
                            $Batch->setFkUser($this->_getSession());
                            $this->getEntityManager()->persist($Batch);
                            $this->getEntityManager()->flush();

                            return $this->redirect()->toRoute('batch', array(
                                'controller' => 'batch',
                                'action'     => 'list',
                                'id'         => $AnaStudy->getPkAnalyteStudy()
                            ));
                        }
                        catch (Exception $e)
                        {
                            $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(array("Id" => $Batch->getPkBatch(), "Justification" => $updateJustification[$key])));
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

        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->find($this->getEvent()->getRouteMatch()->getParam('id'));
        }

        $data = array();
        $elements = $this->getRepository()->findBy(array("fkAnalyte" => $AnaStudy->getFkAnalyte(), "fkStudy" => $AnaStudy->getFkStudy()));
        foreach ($elements as $batch)
        {
            $data[] = array(
                "batch"           => $batch->getSerial(),
                "filename"        => $batch->getFileName(),
                "create_at"       => $batch->getCreatedAt(),
                "valid_flag"      => is_null($batch->getValidFlag()) ? '<a href="' . \Alae\Service\Helper::getVarsConfig("base_url") . '/verification/index/' . $batch->getPkBatch() . '" class="btn" type="button"><span class="btn-validate"></span>validar</a>' : "",
                "validation_date" => $batch->getValidationDate(),
                "result"          => is_null($batch->getValidFlag()) ? "" : ($batch->getValidFlag() ? "VÁLIDO" : "NO VÁLIDO"),
                "modify"          => is_null($batch->getValidFlag()) ? "" : ($batch->getValidFlag() ? '<button class="btn" onclick="changeElement(this, ' . $batch->getPkBatch() . ');"><span class="btn-reject"></span>rechazar</button>' : '<button class="btn" onclick="changeElement(this, ' . $batch->getPkBatch() . ');"><span class="btn-validate"></span>aceptar</button>'),
                "accepted_flag"   => is_null($batch->getAcceptedFlag()) ? "" : ($batch->getAcceptedFlag() ? "S" : "N"),
                "justification"   => is_null($batch->getJustification()) ? "" : $batch->getJustification()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_BATCH);
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('pkAnalyteStudy', $AnaStudy->getPkAnalyteStudy());
        return $viewModel;
    }

    public function acceptAction()
    {
        if ($this->_getSession()->isAdministrador())
        {

        }
    }

    public function rejectAction()
    {
        if ($this->_getSession()->isAdministrador())
        {

        }
    }
}