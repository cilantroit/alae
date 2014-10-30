<?php

/**
 * Modulo encargado de realizar las verificaciones a cada lote
 * Verificaciones de 4 - 24
 *
 * @author Maria Quiroz
 * Fecha de creación: 18/05/2014
 */

namespace Alae\Controller;

use Zend\View\Model\ViewModel,
    Alae\Controller\BaseController,
    Zend\View\Model\JsonModel,
    Alae\Service\Verification;

class VerificationController extends BaseController
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

    /*
     * Función para redirigir a la verificación del batch
     */
    public function indexAction()
    {
        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $Batch = $this->getRepository()->find($this->getEvent()->getRouteMatch()->getParam('id'));
            for ($i = 4; $i < 12; $i++)
            {
                $function = 'V' . $i;
                $this->$function($Batch);
            }

            $response = $this->V12($Batch);
            if ($response)
            {
                $this->V13_24($Batch);
            }
            else
            {
                /*return $this->redirect()->toRoute('verification', array(
                    'controller' => 'verification',
                    'action'     => 'error',
                    'id'         => $Batch->getPkBatch()
                ));*/
            }
        }
    }

    /*
     * Función para verificar los parametros del sistema
     */
    public function errorAction()
    {
        $request = $this->getRequest();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.1"));
        $min1 = $parameters[0]->getMinValue();
        $max1 = $parameters[0]->getMaxValue();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.2"));
        $min2 = $parameters[0]->getMinValue();
        $max2 = $parameters[0]->getMaxValue();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.3"));
        $min3 = $parameters[0]->getMinValue();
        $max3 = $parameters[0]->getMaxValue();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.4"));
        $min4 = $parameters[0]->getMinValue();
        $max4 = $parameters[0]->getMaxValue();

        if ($request->isPost())
        {
            $Batch = $this->getRepository()->find($request->getPost('id'));
            $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => $request->getPost('reason')));
            if ($request->getPost('reason') == "V12.8")
            {
                $this->evaluation($Batch, false, $parameters[0]);
            }

            $where = "s.fkBatch = " . $Batch->getPkBatch() . "
                AND (
                       (s.sampleName LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $min1  . " AND " . $max1 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp1) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $min2 . " AND " . $max2 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp2) = 1 AND s.accuracy NOT BETWEEN " . $min3 . " AND " . $max3 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp3) = 1 AND s.accuracy NOT BETWEEN " . $min4 . " AND " . $max4 . " AND s.useRecord = 1)
                    OR (s.sampleName LIKE 'CS1%' AND s.accuracy BETWEEN " . $min1  . " AND " . $max1 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp1) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy BETWEEN " . $min2 . " AND " . $max2 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp2) = 1 AND s.accuracy BETWEEN " . $min3 . " AND " . $max3 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp3) = 1 AND s.accuracy BETWEEN " . $min4 . " AND " . $max4 . " AND s.useRecord = 0)
                )";

            $this->error($where, $parameters[0], array('regexp1' => '^CS[0-9]+(-[0-9]+)?$','regexp2' => '^QC[0-9]+(-[0-9]+)?$','regexp3' => '^((L|H)?DQC)[0-9]+(-[0-9]+)?$'), false);
            $this->V13_24($Batch);
        }

        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $Batch = $this->getRepository()->find($this->getEvent()->getRouteMatch()->getParam('id'));
        }

        $query   = $this->getEntityManager()->createQuery("
            SELECT s.fileName, s.sampleName, s.accuracy, s.useRecord
            FROM Alae\Entity\SampleBatch s
            WHERE s.fkBatch = " . $Batch->getPkBatch() . "
                AND (
                       (s.sampleName LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $min1  . " AND " . $max1 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp1) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $min2 . " AND " . $max2 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp2) = 1 AND s.accuracy NOT BETWEEN " . $min3 . " AND " . $max3 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp3) = 1 AND s.accuracy NOT BETWEEN " . $min4 . " AND " . $max4 . " AND s.useRecord = 1)
                    OR (s.sampleName LIKE 'CS1%' AND s.accuracy BETWEEN " . $min1  . " AND " . $max1 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp1) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy BETWEEN " . $min2 . " AND " . $max2 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp2) = 1 AND s.accuracy BETWEEN " . $min3 . " AND " . $max3 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp3) = 1 AND s.accuracy BETWEEN " . $min4 . " AND " . $max4 . " AND s.useRecord = 0)
                )");
        $query->setParameter('regexp1', '^CS[0-9]+(-[0-9]+)?$');
        $query->setParameter('regexp2', '^QC[0-9]+(-[0-9]+)?$');
        $query->setParameter('regexp3', '^((L|H)?DQC)[0-9]+(-[0-9]+)?$');

        $data     = array();
        $elements = $query->getResult();
        foreach ($elements as $sampleBatch)
        {
            $data[] = array(
                "filename"    => preg_replace('/\\\\[0-9]+\.wiff/', '', $sampleBatch['fileName']),
                "sample_name" => $sampleBatch['sampleName'],
                "accuracy"    => $sampleBatch['accuracy'],
                "use_record"  => $sampleBatch['useRecord'],
                "reason"      => $this->getReason()
            );
        }

        $datatable = new \Alae\Service\Datatable($data, \Alae\Service\Datatable::DATATABLE_SAMPLE_BATCH, $this->_getSession()->getFkProfile()->getName());
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('pkBatch', $Batch->getPkBatch());
        return $viewModel;
    }

    /*
     * Función para evaluar el status del lote
     */
    protected function evaluation(\Alae\Entity\Batch $Batch, $status = true, $parameter = false)
    {
        if (is_null($Batch->getFkParameter()))
        {
            if ($parameter)
            {
                $Batch->setFkParameter($parameter);
            }

            if ($status)
            {
                $query = $this->getEntityManager()->createQuery("
                    SELECT COUNT(e.fkParameter)
                    FROM Alae\Entity\Error e, Alae\Entity\SampleBatch s
                    WHERE s.pkSampleBatch = e.fkSampleBatch
                        AND ((e.fkParameter BETWEEN 1 AND 8) OR (e.fkParameter BETWEEN 23 AND 29))
                        AND s.fkBatch = " . $Batch->getPkBatch());
                $errors = $query->getSingleScalarResult();
                $status = $errors > 0 ? false : true;
            }

            $Batch->setValidFlag($status);
            $Batch->setValidationDate(new \DateTime('now'));
            $Batch->setFkUser($this->_getSession());
            $this->getEntityManager()->persist($Batch);
            $this->getEntityManager()->flush();

            return $status;
        }

        return $Batch->getValidFlag();
    }

    /*
     * Retornar el listado de Reason
     */
    protected function getReason()
    {
        $elements = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("typeParam" => false));
        $options  = "";
        foreach ($elements as $Parameter)
        {
            $options .= sprintf('<option value="%1$s">%2$s</option>', $Parameter->getRule(), $Parameter->getMessageError());
        }
        return '<select name="reason" style="width:100%;">'.$options.'</select>';
    }

    /**
     * Varificaciones desde la 13 hasta la 24
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V13_24(\Alae\Entity\Batch $Batch)
    {
        for ($i = 13; $i <= 20; $i++)
        {
            $function = 'V' . $i;
            $this->$function($Batch);
        }

        $continue = $this->evaluation($Batch);

        if ($continue && is_null($Batch->getFkParameter()))
        {
            for ($i = 21; $i <= 24; $i++)
            {
                $function = 'V' . $i;
                $this->$function($Batch);
            }
        }

        $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->findBy(array(
            "fkAnalyte" => $Batch->getFkAnalyte(),
            "fkStudy" => $Batch->getFkStudy()
        ));

        /*return $this->redirect()->toRoute('batch', array(
            'controller' => 'batch',
            'action'     => 'list',
            'id'         => $AnaStudy[0]->getPkAnalyteStudy()
        ));*/
    }

    /*
     * Verifica los errores de los lotes
     */
    protected function error($where, $fkParameter, $parameters = array(), $isValid = true)
    {
        $sql = "
            SELECT s
            FROM Alae\Entity\SampleBatch s
            WHERE $where";
        $query = $this->getEntityManager()->createQuery($sql);
        if(count($parameters) > 0)
            foreach ($parameters as $key => $value)
                $query->setParameter($key, $value);
        $elements = $query->getResult();

        $pkParameter = array();
        foreach($elements as $sampleBatch)
        {
            $Error = new \Alae\Entity\Error();
            $Error->setFkSampleBatch($sampleBatch);
            $Error->setFkParameter($fkParameter);
            $this->getEntityManager()->persist($Error);
            $this->getEntityManager()->flush();
            $pkParameter[] = $sampleBatch->getPkSampleBatch();
        }

        if(!$isValid && count($pkParameter) > 0)
        {
            $sql = "
                UPDATE Alae\Entity\SampleBatch s
                SET s.validFlag = 0
                WHERE s.pkSampleBatch in (" . implode(",", $pkParameter) . ")";
            $query = $this->getEntityManager()->createQuery($sql);
            $query->execute();
        }
    }

    /**
     * V4: Sample Type - SAMPLE TYPE ERRÓNEO
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V4(\Alae\Entity\Batch $Batch)
    {
        $where = "
        (
            (s.sampleName LIKE 'BLK%' AND s.sampleType <> 'Blank') OR
            (s.sampleName LIKE 'CS%' AND s.sampleType <> 'Standard') OR
            (s.sampleName LIKE '%QC%' AND s.sampleType <> 'Quality Control') OR
            (REGEXP(s.sampleName, :regexp1) = 1 AND s.sampleType <> 'Solvent') OR
            (REGEXP(s.sampleName, :regexp2) = 1 AND s.sampleType <> 'Unknown')
        ) AND s.fkBatch = " . $Batch->getPkBatch();
        $fkParameter = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V4"));
        $this->error(
            $where,
            $fkParameter[0],
            array(
                "regexp1" => "^REC|FM$",
                "regexp2" => "^[0-9]+(-)[0-9]+\.[0-9]+$"
            )
        );
    }

    /**
     * V5: Concentración nominal de CS/QC - CONCENTRACIÓN NOMINAL ERRÓNEA
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V5(\Alae\Entity\Batch $Batch)
    {
        $elements = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->findBy(array("fkStudy" => $Batch->getFkStudy(), "fkAnalyte" => $Batch->getFkAnalyte()));

        foreach ($elements as $AnaStudy)
        {
            $cs_values = explode(",", $AnaStudy->getCsValues());
            $qc_values = explode(",", $AnaStudy->getQcValues());

            if (count($cs_values) == $AnaStudy->getCsNumber())
            {
                for ($i = 1; $i <= count($cs_values); $i++)
                {
                    $value = \Alae\Service\Conversion::conversion(
                        $AnaStudy->getFkUnit()->getName(),
                        $Batch->getAnalyteConcentrationUnits(),
                        $cs_values[$i - 1]
                    );

                    $where = "s.sampleName LIKE 'CS" . $i . "%' AND s.analyteConcentration <> " . $value . " AND s.fkBatch = " . $Batch->getPkBatch();
                    $fkParameter = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V5"));
                    $this->error($where, $fkParameter[0]);
                }
            }

            if (count($qc_values) == $AnaStudy->getQcNumber())
            {
                for ($i = 1; $i <= count($qc_values); $i++)
                {
                    $value = \Alae\Service\Conversion::conversion(
                        $AnaStudy->getFkUnit()->getName(),
                        $Batch->getAnalyteConcentrationUnits(),
                        $qc_values[$i - 1]
                    );

                    $where = "s.sampleName LIKE 'QC" . $i . "%' AND s.analyteConcentration <> " . $value . " AND s.fkBatch = " . $Batch->getPkBatch();
                    $fkParameter = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V5"));
                    $this->error($where, $fkParameter[0]);
                }
            }
        }


    }

    /**
     * V6.1: Replicados CS (mínimo) - REPLICADOS INSUFICIENTES
     * V6.2: Replicados QC (mínimo) - REPLICADOS INSUFICIENTES
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V6(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V6.1"));
        $query      = $this->getEntityManager()->createQuery("
            SELECT s.pkSampleBatch, SUBSTRING(s.sampleName, 1, 4) as sampleName,  COUNT(s.pkSampleBatch) as counter
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch() . "
            GROUP BY sampleName
            HAVING counter < " . $parameters[0]->getMinValue());
        $elements   = $query->getResult();

        if (count($elements) > 0)
        {
            $pkSampleBatch = array();
            foreach ($elements as $temp)
            {
                $pkSampleBatch[] = $temp["pkSampleBatch"];
            }

            $where = "s.pkSampleBatch IN (" . implode(",", $pkSampleBatch) . ")";
            $this->error($where, $parameters[0]);
        }

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V6.2"));
        $query      = $this->getEntityManager()->createQuery("
            SELECT s.pkSampleBatch, SUBSTRING(s.sampleName, 1, 4) as sampleName,  COUNT(s.pkSampleBatch) as counter
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'QC%' AND s.fkBatch = " . $Batch->getPkBatch() . "
            GROUP BY sampleName
            HAVING counter < " . $parameters[0]->getMinValue());
        $elements   = $query->getResult();

        if (count($elements) > 0)
        {
            $pkSampleBatch = array();
            foreach ($elements as $temp)
            {
                $pkSampleBatch[] = $temp["pkSampleBatch"];
            }

            $where = "s.pkSampleBatch IN (" . implode(",", $pkSampleBatch) . ")";
            $this->error($where, $parameters[0]);
        }
    }

    /**
     * V7: Sample Name repetido - SAMPLE NAME REPETIDO
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V7(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V7"));
        $query    = $this->getEntityManager()->createQuery("
            SELECT s.sampleName,  COUNT(s.pkSampleBatch) as counter
            FROM Alae\Entity\SampleBatch s
            WHERE s.fkBatch = " . $Batch->getPkBatch() . "
            GROUP BY s.sampleName
            HAVING counter > 1");
        $elements = $query->getResult();

        if (count($elements) > 0)
        {
            $sampleNames = array();
            foreach ($elements as $temp)
            {
                $sampleNames[] = sprintf("'%s'", $temp["sampleName"]);
            }

            $where = "s.sampleName IN (" . implode(",", $sampleNames) . ") AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }
    }

    /**
     * V8: Muestras Reinyectadas
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V8(\Alae\Entity\Batch $Batch)
    {
        $query    = $this->getEntityManager()->createQuery("
            SELECT s.pkSampleBatch, s.sampleName
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE  '%R%' AND s.sampleName NOT LIKE  '%\*%' AND  s.fkBatch = " . $Batch->getPkBatch() . "
            ORDER BY s.sampleName ASC");
        $elements = $query->getResult();
        
        var_dump($elements);
        echo "<br><br>";
        
        if (count($elements) > 0)
        {
            $replicated = array();
            $original   = array();
            foreach ($elements as $temp)
            {
                $replicated[$temp["pkSampleBatch"]] = preg_replace('/R[0-9]+/', 'R', $temp["sampleName"]);
                $original[] = preg_replace('/R[0-9]+/', '', "'".$temp["sampleName"]."'");
            }

            $sampleName    = array_unique($original);
            $pkSampleBatch = array_keys(array_unique($replicated));
            
            $sql = "
               UPDATE Alae\Entity\SampleBatch s
               SET s.isUsed = 0, s.validFlag = 0
               WHERE s.fkBatch = " . $Batch->getPkBatch() . " AND (
                s.pkSampleBatch in (" . implode(",", $pkSampleBatch) . ") OR
                s.sampleName in (" . implode(",", $sampleName) . ")
                )";
            
            echo "$sql<br><br>";

            $query = $this->getEntityManager()->createQuery($sql);
            $query->execute();
        }
    }

    /**
     * Verificar muestras reinyectadas [QCRx*]
     * V9.1: Accuracy (QCRx*) - QCR* ACCURACY FUERA DE RANGO
     * V9.2: Use record = 0 ( QCRx*) - QCR* USE RECORD NO VALIDO
     * V9.3: Que tanto V 9.1 como V 9.2 se cumplan - QCR* NO VALIDO
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V9(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V9.1"));
        $min = $parameters[0]->getMinValue();
        $max = $parameters[0]->getMaxValue();

        $where = "REGEXP(s.sampleName, :regexp) = 1 AND s.accuracy NOT BETWEEN " . $min . " AND " . $max . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array('regexp' => '^QC[0-9]+-[0-9]+R[0-9]+\\*$'), false);

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V9.2"));
        $where = "REGEXP(s.sampleName, :regexp) = 1 AND s.useRecord <> 0 AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array('regexp' => '^QC[0-9]+-[0-9]+R[0-9]+\\*$'), false);

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V9.3"));
        $where = "REGEXP(s.sampleName, :regexp) = 1 AND s.useRecord <> 0 AND s.accuracy NOT BETWEEN " . $min . " AND " . $max . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array('regexp' => '^QC[0-9]+-[0-9]+R[0-9]+\\*$'), false);

        $query    = $this->getEntityManager()->createQuery("
            SELECT s.sampleName
            FROM Alae\Entity\SampleBatch s
            WHERE REGEXP(s.sampleName, :regexp) = 1 AND (s.useRecord <> 0 OR s.accuracy NOT BETWEEN " . $min . " AND " . $max . ") AND s.fkBatch = " . $Batch->getPkBatch() . "
            ORDER BY s.sampleName DESC");
        $query->setParameter('regexp', '^QC[0-9]+-[0-9]+R[0-9]+\\*$');
        $elements = $query->getResult();

        foreach($elements as $SampleName)
        {
            $name  = preg_replace(array('/QC[0-9]+-[0-9]+/', '/\*/'), '', $SampleName['sampleName']);
            $where = "s.sampleName LIKE '%" . $name . "%' AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0], array(), false);
        }
    }

    /**
     * Verificacion de accuracy
     * V10.1: Accuracy (CS1) - NO CUMPLE ACCURACY
     * V10.2: Accuracy (CS2-CSx) - NO CUMPLE ACCURACY
     * V10.3: Accuracy (QC) - NO CUMPLE ACCURACY
     * V10.4: Accuracy (DQC) - NO CUMPLE ACCURACY
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V10(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.1"));
        $where      = "s.sampleName LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array(), false);

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.2"));
        $where      = "REGEXP(s.sampleName, :regexp) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array('regexp' => '^CS[0-9]+(-[0-9]+)?$'), false);

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.3"));
        $where      = "REGEXP(s.sampleName, :regexp) = 1 AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array('regexp' => '^QC[0-9]+(-[0-9]+)?$'), false);

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.4"));
        $where      = "REGEXP(s.sampleName, :regexp) = 1 AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array('regexp' => '^((L|H)?DQC)[0-9]+(-[0-9]+)?$'), false);
    }

    /**
     * V11: Revisión del dilution factor en HDQC / LDQC - FACTOR DILUCIÓN ERRÓNEO
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V11(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V11"));
        $query      = $this->getEntityManager()->createQuery("
            SELECT s.pkSampleBatch, s.sampleName, s.dilutionFactor
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE '%DQC%' AND s.fkBatch = " . $Batch->getPkBatch());
        $elements = $query->getResult();

        $pkSampleBatch = array();
        foreach($elements as $SampleBatch)
        {
            $factor = preg_replace('/LDQC|HDQC|-\d+/i', '', $SampleBatch['sampleName']);
            if((float)$factor <> (float)$SampleBatch['dilutionFactor'])
            {
                $pkSampleBatch[] = $SampleBatch['pkSampleBatch'];
            }
        }

        if(count($pkSampleBatch) > 0)
        {
            $ids = implode(",", $pkSampleBatch);
            $where = "s.pkSampleBatch in ($ids) AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0], array(), false);
        }
    }

    /**
     * V12: Use record (CS/QC/DQC)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V12(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.1"));
        $min1 = $parameters[0]->getMinValue();
        $max1 = $parameters[0]->getMaxValue();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.2"));
        $min2 = $parameters[0]->getMinValue();
        $max2 = $parameters[0]->getMaxValue();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.3"));
        $min3 = $parameters[0]->getMinValue();
        $max3 = $parameters[0]->getMaxValue();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.4"));
        $min4 = $parameters[0]->getMinValue();
        $max4 = $parameters[0]->getMaxValue();

        $query   = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch) as counter
            FROM Alae\Entity\SampleBatch s
            WHERE s.fkBatch = " . $Batch->getPkBatch() . "
                AND (
                       (s.sampleName LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $min1  . " AND " . $max1 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp1) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $min2 . " AND " . $max2 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp2) = 1 AND s.accuracy NOT BETWEEN " . $min3 . " AND " . $max3 . " AND s.useRecord = 1)
                    OR (REGEXP(s.sampleName, :regexp3) = 1 AND s.accuracy NOT BETWEEN " . $min4 . " AND " . $max4 . " AND s.useRecord = 1)
                    OR (s.sampleName LIKE 'CS1%' AND s.accuracy BETWEEN " . $min1  . " AND " . $max1 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp1) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy BETWEEN " . $min2 . " AND " . $max2 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp2) = 1 AND s.accuracy BETWEEN " . $min3 . " AND " . $max3 . " AND s.useRecord = 0)
                    OR (REGEXP(s.sampleName, :regexp3) = 1 AND s.accuracy BETWEEN " . $min4 . " AND " . $max4 . " AND s.useRecord = 0)
                )");
        $query->setParameter('regexp1', '^CS[0-9]+(-[0-9]+)?$');
        $query->setParameter('regexp2', '^QC[0-9]+(-[0-9]+)?$');
        $query->setParameter('regexp3', '^((L|H)?DQC)[0-9]+(-[0-9]+)?$');

        return $query->getSingleScalarResult() > 0 ? false : true;
    }

    /**
     * Criterio de aceptación de blancos y ceros
     * V13.1: Selección manual de los CS válidos
     * V13.2: Interf. Analito en BLK - BLK NO CUMPLE
     * V13.3: Interf. IS en BLK - BLK NO CUMPLE
     * V13.4: Interf. Analito en ZS - ZS NO CUMPLE
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V13(\Alae\Entity\Batch $Batch)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS1%' AND s.useRecord = 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $counter = $query->getSingleScalarResult();

        $i = ($counter == 2) ? 2 : 1;
        $query = $this->getEntityManager()->createQuery("
            SELECT AVG(s.analytePeakArea) as analyte_peak_area, AVG(s.isPeakArea) as is_peak_area
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName like 'CS" . $i . "%' AND s.validFlag = 1 AND s.fkBatch = " . $Batch->getPkBatch());
        $elements = $query->getResult();

        $analytePeakArea = $isPeakArea      = 0;
        foreach ($elements as $temp)
        {
            $analytePeakArea = $temp["analyte_peak_area"];
            $isPeakArea      = $temp["is_peak_area"];
        }

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V13.2"));
        $where      = "s.sampleName LIKE 'BLK%' AND s.analytePeakArea > " . ($analytePeakArea * ($parameters[0]->getMinValue() / 100)) . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array(), false);

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V13.3"));
        $where      = "s.sampleName LIKE 'BLK%' AND s.isPeakArea > " . ($isPeakArea * ($parameters[0]->getMinValue() / 100)) . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array(), false);

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V13.4"));
        $where      = "s.sampleName LIKE 'ZS%' AND s.analytePeakArea > " . ($analytePeakArea * ($parameters[0]->getMinValue() / 100)) . " AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array(), false);
    }

    /**
     * Guardar Valores Para Revisión De Criterios Del Lote
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V14(\Alae\Entity\Batch $Batch)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS%' AND s.isUsed = 1 AND s.sampleName NOT LIKE  '%\*%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setCsTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'QC%' AND s.isUsed = 1 AND s.sampleName NOT LIKE  '%\*%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setQcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'LDQC%' AND s.isUsed = 1 AND s.sampleName NOT LIKE  '%\*%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setLdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'HDQC%' AND s.isUsed = 1 AND s.sampleName NOT LIKE  '%\*%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setHdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS%' AND s.sampleName NOT LIKE  '%\*%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setCsAcceptedTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'QC%' AND s.sampleName NOT LIKE  '%\*%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setQcAcceptedTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'LDQC%' AND s.sampleName NOT LIKE  '%\*%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setLdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'HDQC%' AND s.sampleName NOT LIKE  '%\*%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setHdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT AVG(s.isPeakArea)
            FROM Alae\Entity\SampleBatch s
            WHERE (s.sampleName LIKE 'CS%' OR s.sampleName LIKE 'QC%') AND s.sampleName NOT LIKE  '%\*%' AND s.validFlag <> 0 AND s.useRecord = 1 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setIsCsQcAcceptedAvg($value);

        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();
    }

    /**
     * V15: 75% CS - LOTE RECHAZADO (75% CS)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V15(\Alae\Entity\Batch $Batch)
    {
        $value      = ($Batch->getCsAcceptedTotal() / $Batch->getCsTotal()) * 100;
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V15"));

        if ($value < $parameters[0]->getMinValue())
        {
            $where = "s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }
    }

    /**
     * V16: CS consecutivos - LOTE RECHAZADO (CS CONSECUTIVOS)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V16(\Alae\Entity\Batch $Batch)
    {
        $isValid = true;
        $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->findBy(array(
            "fkAnalyte" => $Batch->getFkAnalyte(),
            "fkStudy" => $Batch->getFkStudy()
        ));

        for ($i = 2; $i <= $AnaStudy[0]->getCsNumber(); $i++)
        {
            $query         = $this->getEntityManager()->createQuery("
                SELECT s.pkSampleBatch, s.validFlag
                FROM Alae\Entity\SampleBatch s
                WHERE (s.sampleName LIKE 'CS" . $i . "%' OR s.sampleName LIKE 'CS" . ($i - 1) . "%') AND s.fkBatch = " . $Batch->getPkBatch() . "
                ORDER BY s.sampleName ASC"
            );
            $results       = $query->getResult();

            $pkSampleBatch = array();
            $count         = 0;
            foreach ($results as $result)
            {
                if ($result['validFlag'] == 0)
                {
                    $pkSampleBatch[] = $result['pkSampleBatch'];
                    $count++;
                }
            }

            if ($count == count($results))
            {
                $isValid = false;
                break;
            }
        }

        if (!$isValid)
        {
            $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V16"));
            $where = "s.pkSampleBatch in (" . implode(",", $pkSampleBatch) . ") AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }
    }

    /**
     * Revisar
     * V17: r > 0.99 - LOTE RECHAZADO (r< 0.99)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V17(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V17"));

        if (($Batch->getCorrelationCoefficient() * 100) < $parameters[0]->getMinValue())
        {
            $this->evaluation($Batch, false, $parameters[0]);
        }
    }

    /**
     * V18: 67% QC - LOTE RECHAZADO (67% QC)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V18(\Alae\Entity\Batch $Batch)
    {
        $value      = ($Batch->getQcAcceptedTotal() / $Batch->getQcTotal()) * 100;
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V18"));

        if ($value < $parameters[0]->getMinValue())
        {
            $where = "s.sampleName LIKE 'QC%' AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }
    }

    /**
     * V19: 50% de cada nivel de QC - LOTE RECHAZADO (50% QCx)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V19(\Alae\Entity\Batch $Batch)
    {
        $query    = $this->getEntityManager()->createQuery("
            SELECT SUBSTRING(s.sampleName, 1, 3) as sample_name, s.validFlag, s.sampleName as otro
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName like 'QC%' AND s.isUsed = 1 AND s.fkBatch = " . $Batch->getPkBatch() . "
            GROUP BY sample_name
            ORDER BY sample_name ASC");
        $elements = $query->getResult();

        foreach ($elements as $qc)
        {
            $query    = $this->getEntityManager()->createQuery("
                SELECT COUNT(s.pkSampleBatch)
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE '" . $qc['sample_name'] . "%' AND s.isUsed = 1 AND s.sampleName NOT LIKE '%*%' AND s.fkBatch = " . $Batch->getPkBatch()
            );
            $qc_total = $query->getSingleScalarResult();

            $query                 = $this->getEntityManager()->createQuery("
                SELECT COUNT(s.pkSampleBatch)
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE '" . $qc['sample_name'] . "%' AND s.sampleName NOT LIKE '%*%' AND s.validFlag = 1 AND s.fkBatch = " . $Batch->getPkBatch()
            );
            $qc_not_accepted_total = $query->getSingleScalarResult();

            $value      = ($qc_not_accepted_total / $qc_total) * 100;
            $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V19"));

            if ($value < $parameters[0]->getMinValue())
            {
                $where = "s.sampleName LIKE '" . $qc['sample_name'] . "%' AND s.sampleName NOT LIKE '%R%' AND s.fkBatch = " . $Batch->getPkBatch();
                $this->error($where, $parameters[0]);
            }
        }
    }

    /**
     * V20.1: 50% BLK - LOTE RECHAZADO (INTERF. BLK)
     * V20.2: 50% ZS  - LOTE RECHAZADO (INTERF. ZS)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V20(\Alae\Entity\Batch $Batch)
    {
        $query    = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'BLK%' AND s.fkBatch = " . $Batch->getPkBatch()
        );
        $blk_total = $query->getSingleScalarResult();
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'BLK%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch()
        );
        $blk_accepted_total = $query->getSingleScalarResult();
        $value              = ($blk_accepted_total / $blk_total) * 100;
        $parameters         = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V20.1"));
        if ($value < $parameters[0]->getMinValue())
        {
            $where = "s.sampleName LIKE 'BLK%' AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }

        $query    = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'ZS%' AND s.fkBatch = " . $Batch->getPkBatch()
        );
        $zs_total = $query->getSingleScalarResult();
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'ZS%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch()
        );
        $zs_accepted_total = $query->getSingleScalarResult();
        $value             = ($zs_accepted_total / $zs_total) * 100;
        $parameters        = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V20.2"));
        if ($value < $parameters[0]->getMinValue())
        {
            $where = "s.sampleName LIKE 'ZS%' AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }
    }

    /**
     * V21: Conc. (unknown) > ULOQ ( E ) - CONC. SUPERIOR AL ULOQ
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V21(\Alae\Entity\Batch $Batch)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT s.analyteConcentration
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch() . "
            ORDER BY s.sampleName DESC")
            ->setMaxResults(1);
        $analyteConcentration = $query->getSingleScalarResult();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V21"));
        $where = "s.sampleType = 'Unknown' AND s.calculatedConcentration > $analyteConcentration AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array(), false);
    }

    /**
     * V22: Variabilidad IS (unknown) ( H ) - VARIABILIDAD IS
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V22(\Alae\Entity\Batch $Batch)
    {
        $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->findBy(array(
            "fkAnalyte" => $Batch->getFkAnalyte(),
            "fkStudy"   => $Batch->getFkStudy()
        ));

        if ($AnaStudy[0]->getIsUsed())
        {
            $varIs = $Batch->getIsCsQcAcceptedAvg() * ($AnaStudy[0]->getInternalStandard() / 100);
            $min   = $Batch->getIsCsQcAcceptedAvg() - $varIs;
            $max   = $Batch->getIsCsQcAcceptedAvg() + $varIs;

            $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V22"));
            $where = "s.sampleType = 'Unknown' AND s.isPeakArea NOT BETWEEN $min AND $max AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0], array(), false);
        }
    }

    /**
     * V23: < 5% respuesta IS (unknown) ( B ) - ERROR EXTRACCIÓN IS
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V23(\Alae\Entity\Batch $Batch)
    {
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V23"));
        $value      = $Batch->getIsCsQcAcceptedAvg() * ($parameters[0]->getMinValue() / 100);
        $where = "s.sampleType = 'Unknown' AND s.isPeakArea < $value AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $parameters[0], array(), false);
    }

    protected function V24(\Alae\Entity\Batch $Batch)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS1%' AND s.useRecord = 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $counter = $query->getSingleScalarResult();

        if ($counter == 2)
        {
            $query = $this->getEntityManager()->createQuery("
                SELECT s.analyteConcentration
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE 'CS2%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                ORDER BY s.sampleName ASC")
                ->setMaxResults(1);
            $min = $query->getSingleScalarResult();

            $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V24"));
            $where = "s.sampleType = 'Unknown' AND s.calculatedConcentration < $min AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }

        $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->findBy(array(
            "fkAnalyte" => $Batch->getFkAnalyte(),
            "fkStudy" => $Batch->getFkStudy()
        ));

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS" . $AnaStudy[0]->getCsNumber() . "%' AND s.useRecord = 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $counter = $query->getSingleScalarResult();

        if ($counter == 2)
        {
            $query = $this->getEntityManager()->createQuery("
                SELECT s.analyteConcentration
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE 'CS" . ($AnaStudy[0]->getCsNumber() - 1) . "%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                ORDER BY s.sampleName DESC")
                ->setMaxResults(1);
            $max = $query->getSingleScalarResult();

            $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V24"));
            $where = "s.sampleType = 'Unknown' AND s.calculatedConcentration > $max AND s.fkBatch = " . $Batch->getPkBatch();
            $this->error($where, $parameters[0]);
        }
    }
}
