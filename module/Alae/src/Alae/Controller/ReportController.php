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

    public function ajxstudyAction()
    {
        $request  = $this->getRequest();
        $elements = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array(
            "fkStudy" => $request->getQuery('id')));
        $data     = '<option value="-1">Seleccione</option>';
        foreach ($elements as $anaStudy)
        {
            $data .= '<option value="' . $anaStudy->getFkAnalyte()->getPkAnalyte() . '">' . $anaStudy->getFkAnalyte()->getName() . '</option>';
        }
        return new JsonModel(array("data" => $data));
    }

    public function ajxbatchAction()
    {
        $request  = $this->getRequest();
        $query = $this->getEntityManager()->createQuery("
            SELECT b
            FROM Alae\Entity\Batch b
            WHERE b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
            ORDER BY b.fileName ASC");
        $elements = $query->getResult();
        $data     = '<option value="-1">Seleccione</option>';
        foreach ($elements as $Batch)
        {
            $data .= '<option value="' . $Batch->getPkBatch() . '">' . $Batch->getFileName() . '</option>';
        }
        return new JsonModel(array("data" => $data));
    }

    public function indexAction()
    {
        $error = $this->getEvent()->getRouteMatch()->getParam('id') > 0 ? true : false;
        $elements = $this->getRepository("\\Alae\\Entity\\Study")->findBy(array("status" => true));
        return new ViewModel(array("studies" => $elements, "error" => $error));
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
                $cs_values[]   = explode(",", $anaStudy->getCsValues());
                $qc_values[]   = explode(",", $anaStudy->getQcValues());
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
            ini_set('max_execution_time', 300000);
            $Batch = $this->getRepository('\\Alae\\Entity\\Batch')->find($request->getQuery('ba'));
            if ($Batch && $Batch->getPkBatch())
            {
                $qb = $this->getEntityManager()->createQueryBuilder();
                $qb
                    ->select('s.sampleName, s.analytePeakName, s.sampleType, s.fileName, s.analytePeakArea, s.isPeakArea, s.areaRatio, s.analyteConcentration, s.calculatedConcentration, s.dilutionFactor, s.accuracy, s.useRecord,
                    s.sampleName as sample2, s.acquisitionDate, s.analyteIntegrationType, s.isIntegrationType, s.recordModified,
                    GROUP_CONCAT(DISTINCT p.codeError) as codeError,
                    GROUP_CONCAT(DISTINCT p.messageError) as messageError')
                    ->from('Alae\Entity\SampleBatch', 's')
                    ->leftJoin('Alae\Entity\Error', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 's.pkSampleBatch = e.fkSampleBatch')
                    ->leftJoin('Alae\Entity\Parameter', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'e.fkParameter = p.pkParameter')
                    ->where("s.fkBatch = " . $Batch->getPkBatch())
                    ->groupBy('s.pkSampleBatch')
                    ->orderBy('s.pkSampleBatch', 'ASC');
                $elements = $qb->getQuery()->getResult();

                if(count($elements) > 0)
                {
                    $tr1 = $tr2 = "";
                    foreach ($elements as $sampleBatch)
                    {
                        $row1 = $row2 = "";
                        $isTable2 = false;
                        foreach ($sampleBatch as $key => $value)
                        {
                            if($key == "acquisitionDate")
                            {
                                $value = $value->format('d.m.Y H:i:s');
                            }
                        	if($key == "dilutionFactor")
                            {
                            
                                $value = number_format($value,2,'.','');
                                
                            }
                        if($key == "calculatedConcentration")
                            {
                            
                                $value = number_format($value,2,'.','');
                                
                            }
                            if($isTable2 || $key == "sample2")
                            {
                                $row2 .= sprintf('<td align="center" style="border: black 1px solid;;font-size:13px;padding:4px">%s</td>', $value);
                                $isTable2 = true;
                            }
                            else
                            {
                                $row1 .= sprintf('<td align="center" style="border: black 1px solid;;font-size:13px;padding:4px">%s</td>', $value);
                            }
                        }
                        $tr1 .= sprintf("<tr>%s</tr>", $row1);
                        $tr2 .= sprintf("<tr>%s</tr>", $row2);
                    }

                    $query = $this->getEntityManager()->createQuery("
                        SELECT DISTINCT(p.pkParameter) as pkParameter, p.messageError
                        FROM Alae\Entity\Error e, Alae\Entity\SampleBatch s, Alae\Entity\Parameter p
                        WHERE s.pkSampleBatch = e.fkSampleBatch
                            AND e.fkParameter = p.pkParameter
                            AND ((p.pkParameter BETWEEN 1 AND 8) OR (p.pkParameter BETWEEN 23 AND 29))
                            AND s.fkBatch = " . $Batch->getPkBatch() ."
                        ORDER BY p.pkParameter");
                    $errors = $query->getResult();

                    $message = array();
                    if(!is_null($Batch->getFkParameter()))
                    {
                        $message[$Batch->getFkParameter()->getPkParameter()] = $Batch->getFkParameter()->getMessageError();
                    }
                    foreach ($errors as $data)
                    {
                        $message[$data['pkParameter']] = $data['messageError'];
                    }
                    ksort($message);

                    $properties = array(
                        "batch"     => $Batch,
                        "tr1"       => $tr1,
                        "tr2"       => $tr2,
                        "errors"    => implode("<br>", $message)
                    );
                    $page .= $this->render('alae/report/r2page', $properties);
                }
                else
                {
                    return $this->redirect()->toRoute('report', array(
                        'controller' => 'report',
                        'action'     => 'index',
                        'id'         => 1
                    ));
                }
            }
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
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
                    $query = $this->getEntityManager()->createQuery("
                        SELECT DISTINCT(p.pkParameter) as pkParameter, p.messageError
                        FROM Alae\Entity\Error e, Alae\Entity\SampleBatch s, Alae\Entity\Parameter p
                        WHERE s.pkSampleBatch = e.fkSampleBatch
                            AND e.fkParameter = p.pkParameter
                            AND ((p.pkParameter BETWEEN 1 AND 8) OR (p.pkParameter BETWEEN 23 AND 29))
                            AND s.fkBatch = " . $Batch->getPkBatch());
                    $elements = $query->getResult();

                    $message = array();
                    if(!is_null($Batch->getFkParameter()))
                    {
                        $message[$Batch->getFkParameter()->getPkParameter()] = $Batch->getFkParameter()->getMessageError();
                    }
                    foreach ($elements as $data)
                    {
                        $message[$data['pkParameter']] = $data['messageError'];
                    }
                    ksort($message);
                    $properties[] = array(
                        "filename" => $Batch->getFileName(),
                        "error"    => implode("<br>", $message),
                        "message"  => is_null($Batch->getValidFlag()) ? "Falta validar" : ($Batch->getValidFlag() ? "Aceptado" : "Rechazado")
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
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
            }
        }
    }

    public function r4Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $batch = $this->getRepository("\\Alae\\Entity\\Batch")->findBy(array(
                "fkAnalyte" => $request->getQuery('an'),
                "fkStudy"   => $request->getQuery('id'),
                "validFlag" => true
            ));
            $Analyte = $this->getRepository("\\Alae\\Entity\\Analyte")->find($request->getQuery('an'));
            $Study   = $this->getRepository("\\Alae\\Entity\\Study")->find($request->getQuery('id'));

            if (count($batch) > 0)
            {
                ini_set('max_execution_time', 9000);
                $message = array();
                foreach ($batch as $Batch)
                {
                    $query = $this->getEntityManager()->createQuery("
                        SELECT s.sampleName, GROUP_CONCAT(DISTINCT p.messageError) as messageError
                        FROM Alae\Entity\Error e, Alae\Entity\SampleBatch s, Alae\Entity\Parameter p
                        WHERE s.pkSampleBatch = e.fkSampleBatch
                            AND e.fkParameter = p.pkParameter
                            AND s.sampleType = 'Unknown'
                            AND s.fkBatch = " . $Batch->getPkBatch() . "
                        GROUP BY s.pkSampleBatch
                        ORDER BY p.pkParameter");
                    $elements = $query->getResult();

                    foreach ($elements as $SampleBatch)
                    {
                        $message[] = array(
                            "sampleName"   => $SampleBatch['sampleName'],
                            "messageError" => str_replace(",", "<br>", $SampleBatch['messageError']),
                            "filename"     => $Batch->getFileName()
                        );
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
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
            }
        }
    }

    public function r5Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $query = $this->getEntityManager()->createQuery("
                SELECT b
                FROM Alae\Entity\Batch b
                WHERE b.validFlag = 1 AND b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
                ORDER BY b.fileName ASC");
            $batch = $query->getResult();

            if (count($batch) > 0)
            {
                $viewModel = new ViewModel(array(
                    "batch"    => $batch,
                    "filename" => "summary_of_calibration_curve_parameters" . date("Ymd-Hi")
                ));
                $viewModel->setTerminal(true);
                return $viewModel;
            }
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
            }
        }
    }

    public function r6Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $query = $this->getEntityManager()->createQuery("
                SELECT b
                FROM Alae\Entity\Batch b
                WHERE b.validFlag = 1 AND b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
                ORDER BY b.fileName ASC");
            $batch = $query->getResult();

            if(count($batch) > 0)
            {
                $list    = array();
                $pkBatch = array();
                foreach ($batch as $Batch)
                {
                    $qb = $this->getEntityManager()->createQueryBuilder();
                    $qb
                        ->select('s.calculatedConcentration', 'SUBSTRING(s.sampleName, 1, 3) as sampleName', 'GROUP_CONCAT(DISTINCT p.codeError) as codeError')
                        ->from('Alae\Entity\SampleBatch', 's')
                        ->leftJoin('Alae\Entity\Error', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 's.pkSampleBatch = e.fkSampleBatch')
                        ->leftJoin('Alae\Entity\Parameter', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'e.fkParameter = p.pkParameter')
                        ->where("s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch())
                        ->groupBy('s.pkSampleBatch')
                        ->orderBy('s.sampleName', 'ASC');
                    $elements = $qb->getQuery()->getResult();

                    $Concentration           = array();
                    $calculatedConcentration = array();
                    if (count($elements) > 0)
                    {
                        $counter = 0;
                        foreach ($elements as $temp)
                        {
                            $value                                                          = number_format($temp["calculatedConcentration"], 2, '.', '');
                            $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, $temp["codeError"]);
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
                        "sum"    => number_format($element['suma'], 2, '.', ''),
                        "prom"   => number_format($element['promedio'], 3, '.', ''),
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
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
            }
        }
    }

    public function r7Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $query = $this->getEntityManager()->createQuery("
                SELECT b
                FROM Alae\Entity\Batch b
                WHERE b.validFlag = 1 AND b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
                ORDER BY b.fileName ASC");
            $batch = $query->getResult();

            if(count($batch) > 0)
            {
                $list    = array();
                $pkBatch = array();
                foreach ($batch as $Batch)
                {

                    $qb = $this->getEntityManager()->createQueryBuilder();
                    $qb
                        ->select('s.accuracy', 'SUBSTRING(s.sampleName, 1, 3) as sampleName', 'GROUP_CONCAT(DISTINCT p.codeError) as codeError')
                        ->from('Alae\Entity\SampleBatch', 's')
                        ->leftJoin('Alae\Entity\Error', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 's.pkSampleBatch = e.fkSampleBatch')
                        ->leftJoin('Alae\Entity\Parameter', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'e.fkParameter = p.pkParameter')
                        ->where("s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch())
                        ->groupBy('s.pkSampleBatch')
                        ->orderBy('s.sampleName', 'ASC');
                    $elements = $qb->getQuery()->getResult();

                    $Concentration           = array();
                    $calculatedConcentration = array();
                    if (count($elements) > 0)
                    {
                        $counter = 0;
                        foreach ($elements as $temp)
                        {
                            $value                                                          = number_format($temp["accuracy"], 2, '.', '');
                            $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, $temp["codeError"]);
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
                    SELECT SUM(IF(s.validFlag=1, 1, 0)) as counter, AVG(s.accuracy) as promedio, SUBSTRING(s.sampleName, 1, 3) as sampleName
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
                        "prom"  => number_format($element['promedio'], 2, '.', '')
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
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
            }
        }
    }

    public function r8Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $query = $this->getEntityManager()->createQuery("
                SELECT b
                FROM Alae\Entity\Batch b
                WHERE b.validFlag = 1 AND b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
                ORDER BY b.fileName ASC");
            $batch = $query->getResult();

            if(count($batch) > 0)
            {
                $list    = array();
                $pkBatch = array();
                foreach ($batch as $Batch)
                {
                    $qb = $this->getEntityManager()->createQueryBuilder();
                    $qb
                        ->select('s.calculatedConcentration', 's.accuracy','SUBSTRING(s.sampleName, 1, 3) as sampleName', 'GROUP_CONCAT(DISTINCT p.codeError) as codeError')
                        ->from('Alae\Entity\SampleBatch', 's')
                        ->leftJoin('Alae\Entity\Error', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 's.pkSampleBatch = e.fkSampleBatch')
                        ->leftJoin('Alae\Entity\Parameter', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'e.fkParameter = p.pkParameter')
                        ->where("s.sampleName LIKE 'QC%' AND s.sampleName NOT LIKE '%*%' AND s.fkBatch = " . $Batch->getPkBatch())
                        ->groupBy('s.pkSampleBatch')
                        ->orderBy('s.sampleName', 'ASC');
                    $elements = $qb->getQuery()->getResult();

                    $Concentration           = array();
                    $calculatedConcentration = array();
                    if (count($elements) > 0)
                    {
                        $counter = 0;
                        foreach ($elements as $temp)
                        {
                            $value                                                          = number_format($temp["calculatedConcentration"], 2, '.', '');
                            $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, number_format($temp["accuracy"], 2, '.', ''), $temp['codeError']);
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
                    WHERE s.sampleName LIKE 'QC%' AND s.sampleName NOT LIKE '%*%' AND s.fkBatch in (" . implode(",", $pkBatch) . ")
                    GROUP BY sampleName
                    ORDER By s.sampleName");
                $elements = $query->getResult();

                $calculations = array();
                foreach ($elements as $element)
                {
                    $calculations[] = array(
                        "count"  => $element['counter'],
                        "prom"   => number_format($element['promedio'], 4, '.', ''),
                        "accu"   => number_format($element['accuracy'], 4, '.', ''),
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
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
            }
        }
    }

    public function r9Action()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $analytes = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkAnalyte" => $request->getQuery('an'), "fkStudy" => $request->getQuery('id')));
            $query = $this->getEntityManager()->createQuery("
                SELECT b
                FROM Alae\Entity\Batch b
                WHERE b.validFlag = 1 AND b.fkAnalyte = " . $request->getQuery('an') . " AND b.fkStudy = " . $request->getQuery('id') . "
                ORDER BY b.fileName ASC");
            $batch = $query->getResult();

            if(count($batch) > 0)
            {
                $list    = array();
                $pkBatch = array();
                foreach ($batch as $Batch)
                {
                    $qb = $this->getEntityManager()->createQueryBuilder();
                    $qb
                        ->select('s.calculatedConcentration', 's.accuracy', 's.dilutionFactor', 'SUBSTRING(s.sampleName, 1, 3) as sampleName', 'GROUP_CONCAT(DISTINCT p.codeError) as codeError')
                        ->from('Alae\Entity\SampleBatch', 's')
                        ->leftJoin('Alae\Entity\Error', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 's.pkSampleBatch = e.fkSampleBatch')
                        ->leftJoin('Alae\Entity\Parameter', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'e.fkParameter = p.pkParameter')
                        ->where("s.sampleName LIKE 'LDQC%' AND s.fkBatch = " . $Batch->getPkBatch())
                        ->groupBy('s.pkSampleBatch')
                        ->orderBy('s.sampleName', 'ASC');
                    $elements = $qb->getQuery()->getResult();

                    $Concentration           = array();
                    $calculatedConcentration = array();
                    if (count($elements) > 0)
                    {
                        $counter = 0;
                        foreach ($elements as $temp)
                        {
                            $value                                                          = number_format($temp["calculatedConcentration"], 2, ',', '');
                            $calculatedConcentration[$counter % 2 == 0 ? 'par' : 'impar'][] = array($value, number_format($temp["accuracy"], 2, ',', ''), $temp['codeError']);
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
                        WHERE s.sampleName LIKE 'LDQC%' AND s.fkBatch in (" . implode(",", $pkBatch) . ")
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
                else
                {
                    return $this->redirect()->toRoute('report', array(
                        'controller' => 'report',
                        'action'     => 'index',
                        'id'         => 1
                    ));
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
            else
            {
                return $this->redirect()->toRoute('report', array(
                    'controller' => 'report',
                    'action'     => 'index',
                    'id'         => 1
                ));
            }
        }
    }
}