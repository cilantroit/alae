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
		"created_at" => $AuditTransaction->getCreatedAt(),
		"section" => $AuditTransaction->getSection(),
		"audit_description" => $AuditTransaction->getDescription(),
		"user" => $AuditTransaction->getFkUser()->getUsername()
	    );
	}

	$datatable = new Datatable($data, Datatable::DATATABLE_AUDIT_TRAIL);
	$viewModel = new ViewModel($datatable->getDatatable());
	$viewModel->setVariable('user', $this->_getSession());
	return $viewModel;
    }

    public function ajaxAction()
    {
	$request = $this->getRequest();
	$elements = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkStudy" => $request->getQuery('id')));
	$data = "";
	foreach ($elements as $anaStudy)
	{
	    $data .= '<option value="' . $anaStudy->getFkAnalyte()->getPkAnalyte() . '">' . $anaStudy->getFkAnalyte()->getName() . '</option>';
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
	$request = $this->getRequest();
	if ($request->isGet())
        {
            $study = $this->getRepository('\\Alae\\Entity\\Study')->find($request->getQuery('id'));
            $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkStudy" => $study->getPkStudy()));
            $cs_values = array();
            $qc_values = array();
            foreach ($analytes as $anaStudy)
            {
                $cs_values[] = explode(",", $anaStudy->getCsValues());
                $qc_values[] = explode(",", $anaStudy->getQcValues());
            }

            $properties = array(
                "study" => $study,
                "counterAnalyte" => $counterAnalyte,
                "analytes" => $analytes,
                "cs_values" => $cs_values,
                "qc_values" => $qc_values,
                "filename" => "informacion_general_de_un_estudio". date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
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

    public function r2Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $batch    = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $elements = $this->getRepository("\\Alae\\Entity\\SampleBatch")->findBy(array("fkBatch" => $batch[0]->getPkBatch()));

            foreach ($elements as $SampleBatch)
            {
                $error = "";
                if (!is_null($SampleBatch->getParameters()))
                {
                    $message    = array();
                    $parameters = explode(",", $SampleBatch->getParameters());
                    foreach ($parameters as $parameter)
                    {
                        $Parameter = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                        $message[] = $Parameter->getMessageError();
                    }
                    $error = implode(", ", array_unique($message));
                }
            }

            $list = array();
            foreach ($elements as $SampleBatch)
            {
                $other = $this->getRepository("\\Alae\\Entity\\SampleBatchOtherColumns")->findBy(array("fkSampleBatch" => $SampleBatch->getPkSampleBatch()));

                $message = $reason  = "";
                if (!is_null($SampleBatch->getParameters()))
                {
                    $messages   = $reasons    = array();
                    $parameters = explode(",", $SampleBatch->getParameters());
                    foreach ($parameters as $parameter)
                    {
                        $Parameter  = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                        $messages[] = $Parameter->getMessageError();
                        $reasons[]  = $Parameter->getCodeError();
                    }
                    $message = implode(", ", array_unique($messages));
                    $reason  = implode(", ", array_unique($reasons));
                }
                $list[] = array(
                    "sample_name"              => $SampleBatch->getSampleName(),
                    "acquisition_date"         => $other[0]->getAcquisitionDate(),
                    "analyte_integration_type" => $other[0]->getAnalyteIntegrationType(),
                    "is_integration_type"      => $other[0]->getIsIntegrationType(),
                    "record_modify"            => $other[0]->getRecordModified(),
                    "rejection_reason"         => $reason,
                    "message"                  => $message
                );
            }

            $properties = array(
                "batch"    => $batch[0],
                "elements" => $elements,
                "error"    => $error,
                "list"     => $list,
                "filename" => "tabla_alae_de_cada_lote_analitico" . date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function r3Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $batch    = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $elements = $this->getRepository("\\Alae\\Entity\\SampleBatch")->findBy(array("fkBatch" => $batch[0]->getPkBatch()));

            $list = array();
            foreach ($elements as $SampleBatch)
            {
                $error = "";
                if (!is_null($SampleBatch->getParameters()))
                {
                    $message    = array();
                    $parameters = explode(",", $SampleBatch->getParameters());
                    foreach ($parameters as $parameter)
                    {
                        $Parameter = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                        $message[] = $Parameter->getMessageError();
                    }
                    $error = implode(", ", $message);
                }

                $list[] = array(
                    "sample_name" => $SampleBatch->getSampleName(),
                    "status"      => $SampleBatch->getValidFlag() ? "Aceptado" : "Rechazado",
                    "error"       => $error
                );
            }

            $properties = array(
                "batch"    => $batch[0],
                "list"     => $list,
                "filename" => "resumen_de_lotes_de_un_estudio" . date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function r4Action()
    {
	$request = $this->getRequest();
        if ($request->isGet())
        {
            $batch    = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $elements = $this->getRepository("\\Alae\\Entity\\SampleBatch")->findBy(array("fkBatch" => $batch[0]->getPkBatch()));

            $list = array();
            foreach ($elements as $SampleBatch)
            {
                if (!is_null($SampleBatch->getParameters()))
                {
                    $error = "";
                    $message    = array();
                    $parameters = explode(",", $SampleBatch->getParameters());
                    foreach ($parameters as $parameter)
                    {
                        $Parameter = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                        $message[] = $Parameter->getMessageError();
                    }
                    $error = implode(", ", $message);

                    $list[] = array(
                        "sample_name" => $SampleBatch->getSampleName(),
                        "status"      => $SampleBatch->getFileName(),
                        "error"       => $error
                    );
                }
            }

            $properties = array(
                "batch"    => $batch[0],
                "list"     => $list,
                "filename" => "listado_de_muestras_a_repetir" . date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function r5Action()
    {
	return $viewModel;
    }

    public function r6Action()
    {
	return $viewModel;
    }

    public function r7Action()
    {
	return $viewModel;
    }

    public function r8Action()
    {
	return $viewModel;
    }

    public function r9Action()
    {
	return $viewModel;
    }

}
