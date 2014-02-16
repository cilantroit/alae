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
        $data     = array();
        foreach ($elements as $AuditTransaction)
        {
            $data[] = array(
                "created_at"        => $AuditTransaction->getCreatedAt(),
                "section"           => $AuditTransaction->getSection(),
                "audit_description" => $AuditTransaction->getDescription(),
                "user"              => $AuditTransaction->getFkUser()->getUsername()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_AUDIT_TRAIL, $this->_getSession()->getFkProfile()->getName());
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('user', $this->_getSession());
        return $viewModel;
    }

    public function ajaxAction()
    {
        $request  = $this->getRequest();
        $elements = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkStudy" => $request->getQuery('id')));
        $data     = "";
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

    protected function counterAnalyte($pkStudy)
    {
        $query    = $this->getEntityManager()->createQuery("
            SELECT COUNT(a.fkAnalyte)
            FROM \Alae\Entity\AnalyteStudy a
            WHERE a.fkStudy = " . $pkStudy . "
            GROUP BY a.fkStudy");
        $response = $query->execute();
        return $response ? $query->getSingleScalarResult() : 0;
    }

    public function r1Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $study          = $this->getRepository('\\Alae\\Entity\\Study')->find($request->getQuery('id'));
            $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
            $analytes       = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkStudy" => $study->getPkStudy()));
            $cs_values      = array();
            $qc_values      = array();
            foreach ($analytes as $anaStudy)
            {
                $cs_values[] = explode(",", $anaStudy->getCsValues());
                $qc_values[] = explode(",", $anaStudy->getQcValues());
            }

            $properties = array(
                "study"          => $study,
                "counterAnalyte" => $counterAnalyte,
                "analytes"       => $analytes,
                "cs_values"      => $cs_values,
                "qc_values"      => $qc_values
            );

            $viewModel = new ViewModel();
            $viewModel->setTerminal(true);
            $page      = $this->render('alae/report/r1page', $properties);
            $viewModel->setVariable('page', $page);
            $viewModel->setVariable('filename', "informacion_general_de_un_estudio" . date("Ymd-Hi"));
            return $viewModel;
        }
    }

    public function r2Action()
    {
        $request = $this->getRequest();
        $page    = "";
        if ($request->isGet())
        {
            ini_set('max_execution_time', 600);
            $batch = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));

            if (count($batch) > 0)
            {
                foreach ($batch as $Batch)
                {
                    $query    = $this->getEntityManager()->createQuery("
                        SELECT s, o.acquisitionDate, o.analyteIntegrationType, o.isIntegrationType, o.recordModified
                        FROM Alae\Entity\SampleBatch s, Alae\Entity\SampleBatchOtherColumns o
                        WHERE s.fkBatch = " . $Batch->getPkBatch() . " AND s.parameters IS NOT NULL AND o.fkSampleBatch = s.pkSampleBatch
                        ORDER By s.sampleName");
                    $elements = $query->getResult();

                    $parameters = array();
                    foreach ($elements as $key => $value)
                    {
                        $parameters[] = $value[0]->getParameters();
                    }

                    $query    = $this->getEntityManager()->createQuery("
                        SELECT p.pkParameter, p.messageError, p.codeError, p.rule
                        FROM Alae\Entity\Parameter p
                        WHERE p.pkParameter in (" .implode(",",$parameters). ")
                        GROUP By p.pkParameter");
                    $parameters = $query->getResult();

                    $errors = array();
                    foreach ($parameters as $parameter)
                    {
                        $errors[$parameter['pkParameter']] = $parameter;
                    }

                    $list = array();
                    foreach ($elements as $key => $value)
                    {
                        $parameters = explode(",", $value[0]->getParameters());
                        $message    = $reason     = "";
                        foreach ($parameters as $parameter)
                        {
                            $message .= $errors[$parameter]['messageError'] . "<br>";
                            $reason  .= $errors[$parameter]['codeError'];
                        }
                        $list[] = array(
                            "sample_name"              => $value[0]->getSampleName(),
                            "acquisition_date"         => $value['acquisitionDate']->format("d.m.Y H:i"),
                            "analyte_integration_type" => $value['analyteIntegrationType'],
                            "is_integration_type"      => $value['isIntegrationType'],
                            "record_modify"            => $value['recordModified'],
                            "rejection_reason"         => $reason,
                            "message"                  => $message
                        );
                    }
                    $properties = array(
                        "batch"    => $Batch,
                        "elements" => $elements,
                        "errors"    => $errors,
                        "list"     => $list
                    );
                    $page .= $this->render('alae/report/r2page', $properties);
                }
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('page', $page);
        $viewModel->setVariable('filename', "tabla_alae_de_cada_lote_analitico" . date("Ymd-Hi"));
        return $viewModel;
    }

    public function r3Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $query = $this->getEntityManager()->createQuery("
                SELECT b
                FROM Alae\Entity\Batch b
                WHERE b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
                ORDER BY b.fileName ASC");
            $elements = $query->getResult();

            $Analyte  = $this->getRepository("\\Alae\\Entity\\Analyte")->find($request->getQuery('an'));
            $Study    = $this->getRepository("\\Alae\\Entity\\Study")->find($request->getQuery('id'));

            if (count($elements) > 0)
            {
                $properties = array();

                foreach ($elements as $Batch)
                {
                    $query    = $this->getEntityManager()->createQuery("
                        SELECT s
                        FROM Alae\Entity\SampleBatch s
                        WHERE s.fkBatch = " . $Batch->getPkBatch() . " AND s.parameters IS NOT NULL
                        ORDER By s.sampleName");
                    $elements = $query->getResult();

                    $error = "";
                    $message    = array();

                    if(!is_null($Batch->getFkParameter()))
                    {
                        $message[$Batch->getFkParameter()->getPkParameter()] = $Batch->getFkParameter()->getMessageError();
                    }
                    foreach ($elements as $SampleBatch)
                    {
                        if (!is_null($SampleBatch->getParameters()))
                        {
                            $query    = $this->getEntityManager()->createQuery("
                                SELECT p
                                FROM Alae\Entity\Parameter p
                                WHERE p.pkParameter in (" . $SampleBatch->getParameters() . ")
                                ORDER BY p.pkParameter");
                            $parameters = $query->getResult();
                            foreach ($parameters as $Parameter)
                            {
                                $message[$Parameter->getPkParameter()] = $Parameter->getMessageError();
                            }
                        }
                    }
                    ksort($message);
                    $error = implode("<br>", $message);

                    $properties[] = array(
                        "filename" => $Batch->getFileName(),
                        "error"    => $error,
                        "message"  => is_null($Batch->getValidFlag()) ? "Falta validar" : ($Batch->getValidFlag() ? "Aceptado" : "Rechazado")//($error != "" ? "Rechazado" : "Aceptado")
                    );
                }

                $viewModel = new ViewModel();
                $viewModel->setTerminal(true);
                $viewModel->setVariable('list', $properties);
                $viewModel->setVariable('analyte', $Analyte->getName());
                $viewModel->setVariable('study', $Study->getCode());
                $viewModel->setVariable('filename', "resumen_de_lotes_de_un_estudio" . date("Ymd-Hi"));
                return $viewModel;
            }
        }
    }

    public function r4Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $query   = $this->getEntityManager()->createQuery("
                SELECT b
                FROM Alae\Entity\Batch b
                WHERE b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
                ORDER BY b.fileName ASC");
            $batch   = $query->getResult();
            $Analyte = $this->getRepository("\\Alae\\Entity\\Analyte")->find($request->getQuery('an'));
            $Study   = $this->getRepository("\\Alae\\Entity\\Study")->find($request->getQuery('id'));

            if (count($batch) > 0)
            {
                $message = array();
                foreach ($batch as $Batch)
                {
                    $query    = $this->getEntityManager()->createQuery("
                        SELECT s
                        FROM Alae\Entity\SampleBatch s
                        WHERE s.fkBatch = " . $Batch->getPkBatch() . " AND s.sampleType = 'Unknown' AND s.parameters IS NOT NULL
                        ORDER By s.sampleName");
                    $elements = $query->getResult();

                    foreach ($elements as $SampleBatch)
                    {
                        $query      = $this->getEntityManager()->createQuery("
                            SELECT p.messageError
                            FROM Alae\Entity\Parameter p
                            WHERE p.pkParameter in (" . $SampleBatch->getParameters() . ")
                            ORDER BY p.pkParameter");
                        $parameters = $query->getResult();
                        foreach ($parameters as $Parameter)
                        {
                            $message[$SampleBatch->getSampleName()] = $Parameter['messageError'];
                        }
                    }
                }

                $viewModel = new ViewModel();
                $viewModel->setTerminal(true);
                $viewModel->setVariable('list', $message);
                $viewModel->setVariable('analyte', $Analyte->getName());
                $viewModel->setVariable('study', $Study->getCode());
                $viewModel->setVariable('filename', "listado_de_muestras_a_repetir" . date("Ymd-Hi"));
                return $viewModel;
            }
        }
    }

    public function r5Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $batch = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));

            if (count($batch) > 0)
            {
                $viewModel = new ViewModel(array(
                    "batch"    => $batch,
                    "filename" => "summary_of_calibration_curve_parameters" . date("Ymd-Hi")
                ));
                $viewModel->setTerminal(true);
                return $viewModel;
            }
        }
    }

    public function r6Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $batch    = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));

            $list    = array();
            $pkBatch = array();
            foreach ($batch as $Batch)
            {
                $query    = $this->getEntityManager()->createQuery("
                    SELECT s.sampleName, s.calculatedConcentration, s.validFlag, s.parameters, SUBSTRING(s.sampleName, 1, 3) as sampleName
                    FROM Alae\Entity\SampleBatch s
                    WHERE s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                    ORDER By s.sampleName");
                $elements = $query->getResult();

                $Concentration           = array();
                $calculatedConcentration = array();
                if (count($elements) > 0)
                {
                    $counter = 0;
                    foreach ($elements as $temp)
                    {
                        $error = "";
                        if (!$temp["validFlag"])
                        {
                            $errors = array();
                            foreach (explode(",", $temp["parameters"]) as $parameter)
                            {
                                $Parameter = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                                $errors[]  = $Parameter->getCodeError();
                            }
                            $error = implode(",", $errors);
                        }
                        $value                                                          = number_format($temp["calculatedConcentration"], 2, ',', '');
                        $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, $error);
                        $Concentration[$temp["sampleName"]][]                           = $value;
                        $counter++;
                    }
                }
                list($name, $aux) = explode("_", $Batch->getFileName());
                $calculatedConcentration['name'] = $name;

                $list[]    = $calculatedConcentration;
                $pkBatch[] = $Batch->getPkBatch();
            }

            $query    = $this->getEntityManager()->createQuery("
                SELECT SUM(IF(s.validFlag=1, 1, 0)) as counter, SUM(s.calculatedConcentration) as suma, AVG(s.calculatedConcentration) as promedio, SUBSTRING(s.sampleName, 1, 3) as sampleName
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE 'CS%' AND s.fkBatch in (" . implode(",", $pkBatch) . ")
                GROUP BY sampleName
                ORDER By s.sampleName");
            $elements = $query->getResult();

            $calculations = array();
            foreach ($elements as $element)
            {
                $calculations[] = array(
                    "count"  => $element['counter'],
                    "sum"    => number_format($element['suma'], 2, ',', ''),
                    "prom"   => number_format($element['promedio'], 2, ',', ''),
                    "values" => implode(";", $Concentration[$element['sampleName']])
                );
            }
            $properties = array(
                "analyte"      => $analytes[0],
                "cs_values"    => explode(",", $analytes[0]->getCsValues()),
                "list"         => $list,
                "calculations" => $calculations,
                "filename"     => "back-calculated_concentration_of_calibration_standard" . date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function r7Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $batch    = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));

            $list    = array();
            $pkBatch = array();
            foreach ($batch as $Batch)
            {
                $query    = $this->getEntityManager()->createQuery("
                    SELECT s.sampleName, s.calculatedConcentration, s.validFlag, s.parameters, SUBSTRING(s.sampleName, 1, 3) as sampleName
                    FROM Alae\Entity\SampleBatch s
                    WHERE s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                    ORDER By s.sampleName");
                $elements = $query->getResult();

                $Concentration           = array();
                $calculatedConcentration = array();
                if (count($elements) > 0)
                {
                    $counter = 0;
                    foreach ($elements as $temp)
                    {
                        $error = "";
                        if (!$temp["validFlag"])
                        {
                            $errors = array();
                            foreach (explode(",", $temp["parameters"]) as $parameter)
                            {
                                $Parameter = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                                $errors[]  = $Parameter->getCodeError();
                            }
                            $error = implode(",", $errors);
                        }
                        $value                                                          = number_format($temp["calculatedConcentration"], 2, ',', '');
                        $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, $error);
                        $Concentration[$temp["sampleName"]][]                           = $value;
                        $counter++;
                    }
                }
                list($name, $aux) = explode("_", $Batch->getFileName());
                $calculatedConcentration['name'] = $name;

                $list[] = $calculatedConcentration;

                $pkBatch[] = $Batch->getPkBatch();
            }

            $query    = $this->getEntityManager()->createQuery("
                SELECT SUM(IF(s.validFlag=1, 1, 0)) as counter, AVG(s.calculatedConcentration) as promedio, SUBSTRING(s.sampleName, 1, 3) as sampleName
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE 'CS%' AND s.fkBatch in (" . implode(",", $pkBatch) . ")
                GROUP BY sampleName
                ORDER By s.sampleName");
            $elements = $query->getResult();

            $calculations = array();
            foreach ($elements as $element)
            {
                $calculations[] = array(
                    "count" => $element['counter'],
                    "prom"  => number_format($element['promedio'], 2, ',', '')
                );
            }
            $properties = array(
                "analyte"      => $analytes[0],
                "cs_values"    => explode(",", $analytes[0]->getCsValues()),
                "list"         => $list,
                "calculations" => $calculations,
                "filename"     => "calculated_nominal_concentration_of_calibration_standards" . date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function r8Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $batch    = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));

            $list    = array();
            $pkBatch = array();
            foreach ($batch as $Batch)
            {
                $query    = $this->getEntityManager()->createQuery("
                    SELECT s.calculatedConcentration, s.validFlag, s.parameters, SUBSTRING(s.sampleName, 1, 3) as sampleName, s.accuracy
                    FROM Alae\Entity\SampleBatch s
                    WHERE s.sampleName LIKE 'QC%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                    ORDER By s.sampleName");
                $elements = $query->getResult();

                $Concentration           = array();
                $calculatedConcentration = array();
                if (count($elements) > 0)
                {
                    $counter = 0;
                    foreach ($elements as $temp)
                    {
                        $error = "";
                        if (!$temp["validFlag"])
                        {
                            $errors = array();
                            foreach (explode(",", $temp["parameters"]) as $parameter)
                            {
                                $Parameter = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                                $errors[]  = $Parameter->getCodeError();
                            }
                            $error = implode(",", $errors);
                        }
                        $value                                                          = number_format($temp["calculatedConcentration"], 2, ',', '');
                        $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, number_format($temp["accuracy"], 2, ',', ''), $error);
                        $Concentration[$temp["sampleName"]][]                           = $value;
                        $counter++;
                    }
                }
                list($name, $aux) = explode("_", $Batch->getFileName());
                $calculatedConcentration['name'] = $name;

                $list[] = $calculatedConcentration;

                $pkBatch[] = $Batch->getPkBatch();
            }

            $query    = $this->getEntityManager()->createQuery("
                SELECT SUM(IF(s.validFlag=1, 1, 0)) as counter, AVG(s.calculatedConcentration) as promedio, AVG(s.accuracy) as accuracy, SUBSTRING(s.sampleName, 1, 3) as sampleName
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE 'QC%' AND s.fkBatch in (" . implode(",", $pkBatch) . ")
                GROUP BY sampleName
                ORDER By s.sampleName");
            $elements = $query->getResult();

            $calculations = array();
            foreach ($elements as $element)
            {
                $calculations[] = array(
                    "count"  => $element['counter'],
                    "prom"   => number_format($element['promedio'], 2, ',', ''),
                    "accu"   => number_format($element['accuracy'], 2, ',', ''),
                    "values" => implode(";", $Concentration[$element['sampleName']])
                );
            }
            $properties = array(
                "analyte"      => $analytes[0],
                "qc_values"    => explode(",", $analytes[0]->getQcValues()),
                "list"         => $list,
                "calculations" => $calculations,
                "filename"     => "calculated_nominal_concentration_of_calibration_standards" . date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function r9Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $batch    = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));

            $list    = array();
            $pkBatch = array();
            foreach ($batch as $Batch)
            {
                $query    = $this->getEntityManager()->createQuery("
                    SELECT s.calculatedConcentration, s.dilutionFactor, SUBSTRING(s.sampleName, 1, 3) as sampleName, s.accuracy
                    FROM Alae\Entity\SampleBatch s
                    WHERE s.sampleName LIKE 'LQC%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                    ORDER By s.sampleName");
                $elements = $query->getResult();

                $Concentration           = array();
                $calculatedConcentration = array();
                if (count($elements) > 0)
                {
                    $counter = 0;
                    foreach ($elements as $temp)
                    {
                        $error = "";
                        if (!$temp["validFlag"])
                        {
                            $errors = array();
                            foreach (explode(",", $temp["parameters"]) as $parameter)
                            {
                                $Parameter = $this->getRepository("\\Alae\\Entity\\Parameter")->find($parameter);
                                $errors[]  = $Parameter->getCodeError();
                            }
                            $error = implode(",", $errors);
                        }
                        $value                                                          = number_format($temp["calculatedConcentration"], 2, ',', '');
                        $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, number_format($temp["accuracy"], 2, ',', ''), $error);
                        $Concentration[$temp["sampleName"]][]                           = $value;
                        $counter++;
                    }

                    list($name, $aux) = explode("_", $Batch->getFileName());
                    $calculatedConcentration['name'] = $name;
                    $list[]                          = $calculatedConcentration;
                    $pkBatch[]                       = $Batch->getPkBatch();
                }
            }

            $calculations = array();
            if (count($pkBatch) > 0)
            {
                $query    = $this->getEntityManager()->createQuery("
                    SELECT SUM(IF(s.validFlag=1, 1, 0)) as counter, AVG(s.calculatedConcentration) as promedio, AVG(s.accuracy) as accuracy, SUBSTRING(s.sampleName, 1, 3) as sampleName
                    FROM Alae\Entity\SampleBatch s
                    WHERE s.sampleName LIKE 'LQC%' AND s.fkBatch in (" . implode(",", $pkBatch) . ")
                    GROUP BY sampleName
                    ORDER By s.sampleName");
                $elements = $query->getResult();

                foreach ($elements as $element)
                {
                    $calculations[] = array(
                        "count"  => $element['counter'],
                        "prom"   => number_format($element['promedio'], 2, ',', ''),
                        "accu"   => number_format($element['accuracy'], 2, ',', ''),
                        "values" => implode(";", $Concentration[$element['sampleName']])
                    );
                }
            }

            $properties = array(
                "analyte"      => $analytes[0],
                "list"         => $list,
                "calculations" => $calculations,
                "filename"     => "calculated_nominal_concentration_of_calibration_standards" . date("Ymd-Hi")
            );

            $viewModel = new ViewModel($properties);
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}