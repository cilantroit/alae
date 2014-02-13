<?php

/**
 * Description of VerificationController
 *
 * @author Maria Quiroz
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
                $query = $this->getEntityManager()->createQuery("
                    SELECT COUNT(s.pkSampleBatch)
                    FROM Alae\Entity\SampleBatch s
                    WHERE s.parameters IS NOT NULL");
                $error = $query->getSingleScalarResult();

                $status = ($error > 0) ? false : true;
                $this->updateBatch($Batch, $status);
            }
            else
            {
                return $this->redirect()->toRoute('verification', array(
                    'controller' => 'verification',
                    'action'     => 'error',
                    'id'         => $Batch->getPkBatch()
                ));
            }
        }
    }

    protected function back(\Alae\Entity\Batch $Batch)
    {
        $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->findBy(array(
            "fkAnalyte" => $Batch->getFkAnalyte(),
            "fkStudy" => $Batch->getFkStudy()
        ));

        return $this->redirect()->toRoute('batch', array(
            'controller' => 'batch',
            'action'     => 'list',
            'id'         => $AnaStudy[0]->getPkAnalyteStudy()
        ));
    }

    protected function updateBatch(\Alae\Entity\Batch $Batch, $valid = true)
    {
        $Batch->setValidFlag($valid);
        $Batch->setValidationDate(new \DateTime('now'));
        $Batch->setFkUser($this->_getSession());
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();
        $this->back($Batch);
    }

    protected function acceptedBatch(\Alae\Entity\Batch $Batch)
    {
        $Batch->setValidFlag(true);
        $Batch->setValidationDate(new \DateTime('now'));
        $Batch->setFkUser($this->_getSession());
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();
        $this->back($Batch);
    }

    protected function rejectBatch(\Alae\Entity\Batch $Batch, \Alae\Entity\Parameter $Parameter)
    {
        $Batch->setValidFlag(false);
        $Batch->setValidationDate(new \DateTime('now'));
        $Batch->setFkParameter($Parameter);
        $Batch->setFkUser($this->_getSession());
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();
        $this->back($Batch);
    }

    public function errorAction()
    {
        $request = $this->getRequest();

        if ($request->isPost())
        {
            $Batch = $this->getRepository()->find($request->getPost('id'));

            if ($request->getPost('reason') == "V12.8")
            {
                $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => $request->getPost('reason')));
                $this->rejectBatch($Batch, $parameters[0]);
            }

            $where = "s.fkBatch = " . $Batch->getPkBatch() . " AND REGEXP(s.sampleName, :regexp) = 1 AND s.validFlag = 0 AND s.useRecord <> 0";
            $sql   = Verification::update($where, $request->getPost('reason'));
            $query = $this->getEntityManager()->createQuery($sql);
            $query->setParameter('regexp', '^(CS|QC|(L|H)?DQC)[0-9]+(-[0-9]+)?$');
            $query->execute();
            $this->verification13_24($Batch);
        }

        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $Batch = $this->getRepository()->find($this->getEvent()->getRouteMatch()->getParam('id'));
        }

        $query = $this->getEntityManager()->createQuery("
            SELECT s.fileName, s.sampleName, s.accuracy, s.useRecord
            FROM Alae\Entity\SampleBatch s
            WHERE s.fkBatch = " . $Batch->getPkBatch() . " AND REGEXP(s.sampleName, :regexp) = 1 AND s.validFlag = 0 AND s.useRecord <> 0");
        $query->setParameter('regexp', '^(CS|QC|(L|H)?DQC)[0-9]+(-[0-9]+)?$');

        $data     = array();
        $elements = $query->getResult();
        foreach ($elements as $sampleBatch)
        {
            $data[] = array(
                "filename"    => $sampleBatch['fileName'],
                "sample_name" => $sampleBatch['sampleName'],
                "accuracy"    => $sampleBatch['accuracy'],
                "use_record"  => $sampleBatch['useRecord'],
                "reason"      => $this->getReason()
            );
        }

        $datatable = new \Alae\Service\Datatable($data, \Alae\Service\Datatable::DATATABLE_SAMPLE_BATCH);
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('pkBatch', $Batch->getPkBatch());
        return $viewModel;
    }

    protected function getReason()
    {
        $elements = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("typeParam" => false));
        $options  = "";
        foreach ($elements as $Parameter)
        {
            $options .= sprintf('<option value="%1$s">%2$s</option>', $Parameter->getRule(), $Parameter->getMessageError());
        }
        return sprintf('<select name="reason">%s</select>', $options);
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

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.parameters IS NOT NULL AND s.fkBatch = " . $Batch->getPkBatch());
        $error = $query->getSingleScalarResult();

        if ($error == 0)
        {
            for ($i = 21; $i <= 24; $i++)
            {
                $function = 'V' . $i;
                $this->$function($Batch);
            }
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
        $sql   = Verification::update($where, "V4");
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('regexp1', '^REC|FM$');
        $query->setParameter('regexp2', '^[0-9]+(-)[0-9]+\.[0-9]+$');//09-1.32
        $query->execute();
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
                    $where = "s.sampleName LIKE 'CS" . $i . "%' AND s.analyteConcentration <> " . $cs_values[$i - 1] . " AND s.fkBatch = " . $Batch->getPkBatch();
                    $sql   = Verification::update($where, "V5");
                    $query = $this->getEntityManager()->createQuery($sql);
                    $query->execute();
                }
            }

            if (count($qc_values) == $AnaStudy->getQcNumber())
            {
                for ($i = 1; $i <= count($qc_values); $i++)
                {
                    $where = "s.sampleName LIKE 'QC" . $i . "%' AND s.analyteConcentration <> " . $qc_values[$i - 1] . " AND s.fkBatch = " . $Batch->getPkBatch();
                    $sql   = Verification::update($where, "V5");
                    $query = $this->getEntityManager()->createQuery($sql);
                    $query->execute();
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
            $sql   = Verification::update($where, "V6.1");
            $query = $this->getEntityManager()->createQuery($sql);
            $query->execute();
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
            $sql   = Verification::update($where, "V6.2");
            $query = $this->getEntityManager()->createQuery($sql);
            $query->execute();
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
            $sql   = Verification::update($where, "V7");
            $query = $this->getEntityManager()->createQuery($sql);
            $query->execute();
        }
    }

    /**
     * V8: Sample Type - SAMPLE TYPE ERRÓNEO
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V8(\Alae\Entity\Batch $Batch)
    {
        $query    = $this->getEntityManager()->createQuery("
            SELECT s.pkSampleBatch, s.sampleName
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE  '%R%' AND s.sampleName NOT LIKE  '%\*%' AND  s.fkBatch = " . $Batch->getPkBatch() . "
            ORDER BY s.sampleName DESC");
        $elements = $query->getResult();

        if (count($elements) > 0)
        {
            $replicated = array();
            foreach ($elements as $temp)
            {
                $replicated[$temp["pkSampleBatch"]] = preg_replace('/R[0-9]+/', 'R', $temp["sampleName"]);
            }

            $pkSampleBatch = array_keys(array_unique($replicated));
            $where = "s.pkSampleBatch NOT IN (" . implode(",", $pkSampleBatch) . ") AND s.sampleName LIKE '%R%' AND s.sampleName NOT LIKE '%\*%' AND s.fkBatch = " . $Batch->getPkBatch();
            $sql   = Verification::update($where, "V8", array("s.validFlag = 0"));
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

        $where = "REGEXP(s.sampleName, :regexp) = 1 AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql   = Verification::update($where, "V9.1", array("s.validFlag = 0"));
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('regexp', '^QC[0-9]+-[0-9]+R[0-9]+\\*$');
        $query->execute();

        $where = "REGEXP(s.sampleName, :regexp) = 1 AND s.useRecord <> 0 AND s.fkBatch = " . $Batch->getPkBatch();
        $sql   = Verification::update($where, "V9.2", array("s.validFlag = 0"));
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('regexp', '^QC[0-9]+-[0-9]+R[0-9]+\\*$');
        $query->execute();

        $where = "REGEXP(s.sampleName, :regexp) = 1 AND s.useRecord <> 0 AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql   = Verification::update($where, "V9.3", array("s.validFlag = 0"));
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('regexp', '^QC[0-9]+-[0-9]+R[0-9]+\\*$');
        $query->execute();
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
        $sql        = Verification::update($where, "V10.1", array("s.validFlag = 0"));
        $query      = $this->getEntityManager()->createQuery($sql);
        $query->execute();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.2"));
        $where      = "REGEXP(s.sampleName, :regexp) = 1 AND s.sampleName NOT LIKE 'CS1%' AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql        = Verification::update($where, "V10.2", array("s.validFlag = 0"));
        $query      = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('regexp', '^CS[0-9]+(-[0-9]+)?$');
        $query->execute();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.3"));
        $where      = "REGEXP(s.sampleName, :regexp) = 1 AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql        = Verification::update($where, "V10.3", array("s.validFlag = 0"));
        $query      = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('regexp', '^QC[0-9]+(-[0-9]+)?$');
        $query->execute();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V10.4"));
        $where      = "REGEXP(s.sampleName, :regexp) = 1 AND s.accuracy NOT BETWEEN " . $parameters[0]->getMinValue() . " AND " . $parameters[0]->getMaxValue() . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql        = Verification::update($where, "V10.4", array("s.validFlag = 0"));
        $query      = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('regexp', '^((L|H)?DQC)[0-9]+(-[0-9]+)?$');
        $query->execute();
    }

    /**
     * V11: Revisión del dilution factor en HDQC / LDQC - FACTOR DILUCIÓN ERRÓNEO
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V11(\Alae\Entity\Batch $Batch)
    {
        $where = "s.sampleName LIKE '%DQC%' AND SUBSTRING(s.sampleName,5,1) <> s.dilutionFactor AND s.fkBatch = " . $Batch->getPkBatch();
        $sql   = Verification::update($where, "V11", array("s.validFlag = 0"));
        $query = $this->getEntityManager()->createQuery($sql);
        $query->execute();
    }

    /**
     * V12: Use record (CS/QC/DQC)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V12(\Alae\Entity\Batch $Batch)
    {
        $query   = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch) as counter
            FROM Alae\Entity\SampleBatch s
            WHERE s.fkBatch = " . $Batch->getPkBatch() . " AND REGEXP(s.sampleName, :regexp) = 1 AND s.validFlag = 0 AND s.useRecord <> 0");
        $query->setParameter('regexp', '^(CS|QC|(L|H)?DQC)[0-9]+(-[0-9]+)?$');

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
        $i               = 1; //Si la recta no esta truncada (paso 12) la i = 1
        $query           = $this->getEntityManager()->createQuery("
            SELECT AVG(s.analytePeakArea) as analyte_peak_area, AVG(s.isPeakArea) as is_peak_area
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName like 'CS" . $i . "%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $elements        = $query->getResult();
        $analytePeakArea = $isPeakArea      = 0;
        foreach ($elements as $temp)
        {
            $analytePeakArea = $temp["analyte_peak_area"];
            $isPeakArea      = $temp["is_peak_area"];
        }

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V13.2"));
        $where      = "s.sampleName LIKE 'BLK%' AND s.analytePeakArea > " . ($analytePeakArea * ($parameters[0]->getMinValue() / 100)) . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql        = Verification::update($where, "V13.2", array("s.validFlag = 0"));
        $query      = $this->getEntityManager()->createQuery($sql);
        $query->execute();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V13.3"));
        $where      = "s.sampleName LIKE 'BLK%' AND s.isPeakArea > " . ($isPeakArea * ($parameters[0]->getMinValue() / 100)) . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql        = Verification::update($where, "V13.3", array("s.validFlag = 0"));
        $query      = $this->getEntityManager()->createQuery($sql);
        $query->execute();

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V13.4"));
        $where      = "s.sampleName LIKE 'ZS%' AND s.analytePeakArea > " . ($analytePeakArea * ($parameters[0]->getMinValue() / 100)) . " AND s.fkBatch = " . $Batch->getPkBatch();
        $sql        = Verification::update($where, "V13.4", array("s.validFlag = 0"));
        $query      = $this->getEntityManager()->createQuery($sql);
        $query->execute();
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
            WHERE s.sampleName LIKE 'CS%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setCsTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'QC%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setQcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'LDQC%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setLdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'HDQC%' AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setHdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setCsAcceptedTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'QC%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setQcAcceptedTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'LDQC%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setLdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'HDQC%' AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $value = $query->getSingleScalarResult();
        $Batch->setHdqcTotal($value);

        $query = $this->getEntityManager()->createQuery("
            SELECT AVG(s.isPeakArea)
            FROM Alae\Entity\SampleBatch s
            WHERE (s.sampleName LIKE 'CS%' OR s.sampleName LIKE 'QC%') AND s.validFlag <> 0 AND s.fkBatch = " . $Batch->getPkBatch());
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
            $this->rejectBatch($Batch, $parameters[0]);
        }
    }

    /**
     * V16: CS consecutivos - LOTE RECHAZADO (CS CONSECUTIVOS)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V16(\Alae\Entity\Batch $Batch)
    {
        $query    = $this->getEntityManager()->createQuery("
            SELECT SUBSTRING(s.sampleName, 3, 1) as sample_name
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName like 'CS%' AND s.validFlag = 0 AND s.fkBatch = " . $Batch->getPkBatch() . "
            ORDER BY s.sampleName ASC");
        $elements = $query->getResult();

        $isValid = true;
        $ant     = 0;
        foreach ($elements as $temp)
        {
            if ($ant == ($temp["sample_name"] - 1) && $ant > 0)
            {
                $isValid = false;
                break;
            }

            $ant = $temp["sample_name"];
        }

        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V16"));
        if (!$isValid)
        {
            $this->rejectBatch($Batch, $parameters[0]);
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

        if ($Batch->getCorrelationCoefficient() < $parameters[0]->getMinValue())
        {
            $this->rejectBatch($Batch, $parameters[0]);
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
            $this->rejectBatch($Batch, $parameters[0]);
        }
    }

    /**
     * V19: 50% de cada nivel de QC - LOTE RECHAZADO (50% QCx)
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V19(\Alae\Entity\Batch $Batch)
    {
        $query    = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'QC%' AND s.sampleName NOT LIKE '%R%' AND s.fkBatch = " . $Batch->getPkBatch()
        );
        $qc_total = $query->getSingleScalarResult();

        $query                 = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'QC%' AND s.sampleName NOT LIKE '%R%' AND s.validFlag = 0 AND s.fkBatch = " . $Batch->getPkBatch()
        );
        $qc_not_accepted_total = $query->getSingleScalarResult();

        $value      = ($qc_not_accepted_total / $qc_total) * 100;
        $parameters = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V19"));

        if ($value < $parameters[0]->getMinValue())
        {
            $this->rejectBatch($Batch, $parameters[0]);
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
            $this->rejectBatch($Batch, $parameters[0]);
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
            $this->rejectBatch($Batch, $parameters[0]);
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

        $where = "s.sampleType = 'Unknown' AND s.analyteConcentration > $analyteConcentration AND s.fkBatch = " . $Batch->getPkBatch();
        $sql   = Verification::update($where, "V21", array("s.validFlag = 0"));
        $query = $this->getEntityManager()->createQuery($sql);
        $query->execute();
    }

    /**
     * V22: Variabilidad IS (unknown) ( H ) - VARIABILIDAD IS
     * @param \Alae\Entity\Batch $Batch
     */
    protected function V22(\Alae\Entity\Batch $Batch)
    {
        $min   = $Batch->getIsCsQcAcceptedAvg(); //-%var (AnaStudy);
        $max   = $Batch->getIsCsQcAcceptedAvg(); //+%var (AnaStudy);
        $where = "s.sampleType = 'Unknown' AND s.isPeakArea NOT BETWEEN $min AND $max AND s.fkBatch = " . $Batch->getPkBatch();
        $sql   = Verification::update($where, "V22", array("s.validFlag = 0"));
        $query = $this->getEntityManager()->createQuery($sql);
        $query->execute();
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
        $sql   = Verification::update($where, "V23", array("s.validFlag = 0"));
        $query = $this->getEntityManager()->createQuery($sql);
        $query->execute();
    }

    protected function V24(\Alae\Entity\Batch $Batch)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS1%' AND s.useRecord = 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $cs1   = $query->getSingleScalarResult();

        if ($cs1 == 2)
        {
            $query = $this->getEntityManager()->createQuery("
                SELECT s.analyteConcentration
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE 'CS2%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                ORDER BY s.sampleName DESC")
                ->setMaxResults(1);
            $analyteConcentration = $query->getSingleScalarResult();

            $where = "s.sampleType = 'Unknown' AND s.analyteConcentration < $analyteConcentration AND s.fkBatch = " . $Batch->getPkBatch();
            $sql   = Verification::update($where, "V24");
            $query = $this->getEntityManager()->createQuery($sql);
            $query->execute();
        }

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.sampleName LIKE 'CS" . $Batch->getCsTotal() . "%' AND s.useRecord = 0 AND s.fkBatch = " . $Batch->getPkBatch());
        $csN   = $query->getSingleScalarResult();

        if ($csN == 2)
        {
            $query = $this->getEntityManager()->createQuery("
                SELECT s.analyteConcentration
                FROM Alae\Entity\SampleBatch s
                WHERE s.sampleName LIKE 'CS" . ($Batch->getCsTotal() - 1) . "%' AND s.fkBatch = " . $Batch->getPkBatch() . "
                ORDER BY s.sampleName DESC")
                ->setMaxResults(1);
            $analyteConcentration = $query->getSingleScalarResult();

            $where = "s.sampleType = 'Unknown' AND s.analyteConcentration > $analyteConcentration AND s.fkBatch = " . $Batch->getPkBatch();
            $sql   = Verification::update($where, "V24");
            $query = $this->getEntityManager()->createQuery($sql);
            $query->execute();
        }
    }
}
