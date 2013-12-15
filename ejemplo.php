<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Alae\Controller;

use Alae\Controller\BaseController,
    Alae\Service\Helper as Helper;

class CronController extends BaseController
{

    protected $_Study = null;
    protected $_Analyte = null;
    protected $_error = false;

    private function isRepeatedBatch($fileName)
    {
        $query = $this->getEntityManager()->createQuery("SELECT COUNT(b.pkBatch) FROM Alae\Entity\Batch b WHERE b.fileName = '" . Helper::getVarsConfig("batch_directory") . "/" . $fileName . "'");
        $count = $query->getSingleScalarResult();

        if ($count == 0)
        {
            return true;
        }
        else
        {
            $this->setErrorTransaction('Repeated batch', $fileName);
        }

        return false;
    }

    private function validateFile($fileName)
    {
        $string = substr($fileName, 0, -4);
        list($pkBatch, $aux) = explode("-", $string);
        list($codeStudy, $shortening) = explode("_", $aux);
        $this->_Study = $this->_Analyte = null;

        if ($this->isRepeatedBatch($fileName))
        {
            $studies = $this->getRepository("\\Alae\\Entity\\Study")
                    ->findBy(array('code' => $codeStudy));

            if (count($studies) == 1 && $studies [0]->getCloseFlag() == false)
            {
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->select("a")
                        ->from("Alae\Entity\AnalyteStudy", "h")
                        ->innerJoin("Alae\Entity\Analyte", "a", "WITH", "h.fkAnalyte = a.pkAnalyte AND a.shortening = '" . $shortening . "' AND h.fkStudy = " . $studies[0]->getPkStudy())
                ;
                $analytes = $qb->getQuery()->getResult();

                if ($analytes)
                {
                    $this->_Study = $studies[0];
                    $this->_Analyte = $analytes[0];
                }
                else
                {
                    $this->setErrorTransaction('The_analyte_is_not_associated_with_the_study', $shortening);
                }
            }
            else
            {
                $this->setErrorTransaction('The_lot_is_not_associated_with_a_registered_study', $fileName);
            }
        }
    }

    private function setErrorTransaction($msgError, $value)
    {
        $error = Helper::getError($msgError);
        $error['description'] = sprintf($error['description'], $value);
        $this->transactionError($error, true);
        $this->_error = true;
    }

    public function readAction()
    {
        $files = scandir(Helper::getVarsConfig("batch_directory"), 1);

        foreach ($files as $file)
        {
            if (is_file($file))
            {
                if (preg_match("/^(\d+-\d+\_[a-zA-Z0-9]+\.txt)$/i", $file))
                {
                    $this->validateFile($file);
                }
                else
                {
                    $this->isRepeatedBatch($file);
                    $this->setErrorTransaction('Invalid_file_name_in_the_export_process_batches_of_analytes', $file);
                }

                $this->insertBatch(Helper:: getVarsConfig("batch_directory") . "/" . $file, $this->_Study, $this->_Analyte);
                unlink(Helper:: getVarsConfig("batch_directory") . "/" . $file);
            }
        }
    }

    private function insertBatch($fileName, $Study, $Analyte)
    {
        $data = $this->getData($fileName, $Study, $Analyte);
        $Batch = $this->saveBatch($fileName);
        $this->saveSampleBatch($data["headers"], $data['data'], $Batch);

        if (!is_null($Analyte) && !is_null($Study))
        {
            $this->batchVerify($Batch, $fileName);
            $this->updateBatch($Batch, $Analyte, $Study);
        }
    }

    private function cleanHeaders($headers)
    {
        $newsHeaders = array();

        foreach ($headers as $header)
        {
            $newsHeaders[] = preg_replace('/\s\(([a-zA-Z]|\s|\/|%)+\)/i', '', $header);
        }
        return $newsHeaders;
    }

    private function getData($filename)
    {
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        fclose($fp);

        $lines = explode("\n", $content);
        $continue = false;
        $data = $headers = array();

        foreach ($lines as $line)
        {
            if ($continue)
            {
                $data[] = explode("\t", $line);
            }

            if (strstr($line, "Sample Name"))
            {
                $headers = $this->cleanHeaders(explode("\t", $line));
                $continue = true;
            }
        }

        return array(
            "headers" => $headers,
            "data" => $data
        );
    }

    private function setter($headers, $elements)
    {
        $orderHeader = array();

        foreach ($headers as $key => $value)
        {
            if (array_key_exists($value, $elements))
            {
                $orderHeader[$key] = $elements[$value];
            }
        }

        return $orderHeader;
    }

    private function saveBatch($fileName)
    {
        $Batch = new \Alae\Entity\Batch();
        $Batch->setFileName($fileName);
        $Batch->setFkUser($this->_getSystem());
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();

        return $Batch;
    }

    private function updateBatch($Batch, $Analyte, $Study)
    {        
        $Batch->setFkAnalyte($Analyte);
        $Batch->setFkStudy($Study);
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();       
    }

    private function batchVerify($Batch, $Analyte, $fileName)
    {
        $string = substr($fileName, 0, -4);
        list($pkBatch, $aux) = explode("-", $string);

        /*
         * Crear esta función en BaseController execute
         * $query = $this->getEntityManager()->createQuery($sql);
         * $response = $query->execute();
         */
        $this->execute(Verification::updateFk("s.analytePeakName <> '" . $Analyte->getShortening() . " AND s.fkBatch = " . $Batch->getPkBatch() . "'", "V1"));
        $this->execute(Verification::updateFk("SUBSTRING(s.fileName, 0, 2) <> '" . $pkBatch . " AND s.fkBatch = " . $Batch->getPkBatch() . "'", "V2"));
    }
    
    private function saveSampleBatch($headers, $data, $Batch)
    {
        $setters = $this->setter($headers, $this->getSampleBatch());

        foreach ($data as $row)
        {
            $SampleBatch = new \Alae\Entity\SampleBatch();

            if (count($row) > 1)
            {
                foreach ($row as $key => $value)
                {
                    if (isset($setters[$key]))
                    {
                        $SampleBatch->$setters[$key]($value);
                    }
                }
                $SampleBatch->setFkBatch($Batch);
                $SampleBatch->setAnalyteConcentrationUnits("ng/nl");
                $SampleBatch->setCalculatedConcentrationUnits("ng/nl");
                $this->getEntityManager()->persist($SampleBatch);
                $this->getEntityManager()->flush();

                $this->saveSampleBatchOtherColumns($headers, $row, $SampleBatch);
            }
        }
    }

    private function saveSampleBatchOtherColumns($headers, $row, $SampleBatch)
    {

        $setters = $this->setter($headers, $this->getSampleBatchOtherColumns());

        $SampleBatchOtherColumns = new \Alae\Entity\SampleBatchOtherColumns();

        foreach ($row as $key => $value)
        {
            if (isset($setters[$key]))
            {

                $SampleBatchOtherColumns->$setters[$key]($value);
            }
        }
        $SampleBatchOtherColumns->setFkSampleBatch($SampleBatch);
        $this->getEntityManager()->persist($SampleBatchOtherColumns);
        $this->getEntityManager()->flush();
    }

    private function getSampleBatch()
    {
        return array(
            "Sample Name" => "setSampleName",
            "Analyte Peak Name" => "setAnalytePeakName",
            "Sample Type" => "setSampleType",
            "File Name" => "setFileName",
            "Dilution Factor" => "setDilutionFactor",
            "Analyte Peak Area" => "setAnalytePeakArea",
            "IS Peak Name" => "setIsPeakName",
            "IS Peak Area" => "setIsPeakArea",
            "Analyte Concentration" => "setAnalyteConcentration",
            //"Analyte Concentration units" => "setAnalyteConcentration", --> No existe en el fichero de prueba
            "Calculated Concentration" => "setCalculatedConcentration",
            //"Calculated Concentration Units" => "setCalculatedConcentration", --> No existe en el fichero de prueba
            "Accuracy" => "setAccuracy",
            "Use Record" => "setUseRecord",
        );
    }

    private function getSampleBatchOtherColumns()
    {
        return array(
            "Sample ID" => "setSampleId",
            "Sample Comment" => "setSampleComment",
            "Set Number" => "setSetNumber",
            "Acquisition Method" => "setAcquisitionMethod",
            "Rack Type" => "setRackType",
            "Rack Position" => "setRackPosition",
            "Vial Position" => "setVialPosition",
            "Plate Type" => "setPlateType",
            "Plate Position" => "setPlatePosition",
            "Weight To Volume Ratio" => "setWeightToVolumeRatio",
            "Sample Annotation" => "setSampleAnnotation",
            "Disposition" => "setDisposition",
            "Analyte Units" => "setAnalyteUnits",
            "Acquisition Date" => "setAcquisitionDate", //-->debe ser Datetime
            "Analyte Peak Area for DAD" => "setAnalytePeakAreaForDad",
            "Analyte Peak Height" => "setAnalytePeakHeight",
            "Analyte Peak Height for DAD" => "setAnalytePeakHeightForDad",
            "Analyte Retention Time" => "setAnalyteRetentionTime",
            "Analyte Expected RT" => "setAnalyteExpectedRt",
            "Analyte RT Window" => "setAnalyteRtWindow",
            "Analyte Centroid Location" => "setAnalyteCentroidLocation",
            "Analyte Start Scan" => "setAnalyteStartScan",
            "Analyte Start Time" => "setAnalyteStartTime",
            "Analyte Stop Scan" => "setAnalyteStopScan",
            "Analyte Stop Time" => "setAnalyteStopTime",
            "Analyte Integration Type" => "setAnalyteIntegrationType",
            "Analyte Signal To Noise" => "setAnalyteSignalToNoise",
            "Analyte Peak Width" => "setAnalytePeakWidth",
            "Standard Query Status" => "setAnalyteStandarQueryStatus",
            "Analyte Mass Ranges" => "setAnalyteMassRanges",
            "Analyte Wavelength Ranges" => "setAnalyteWavelengthRanges",
            "Height Ratio" => "setHeightRatio",
            "Analyte Annotation" => "setAnalyteAnnotation",
            "Analyte Channel" => "setAnalyteChannel",
            "Analyte Peak Width at 50% Height" => "setAnalytePeakWidthAt50Height",
            "Analyte Slope of Baseline" => "setAnalyteSlopeOfBaseline",
            "Analyte Processing Alg." => "setAnalyteProcessingAlg",
            "Analyte Peak Asymmetry" => "setAnalytePeakAsymmetry",
            "IS Units" => "setIsUnits",
            "IS Peak Area for DAD" => "setIsPeakAreaForDad",
            "IS Peak Height" => "setIsPeakHeight",
            "IS Peak Height for DAD" => "setIsPeakHeightForDad",
            "IS Concentration" => "setIsConcentration",
            "IS Retention Time" => "setIsRetentionTime",
            "IS Expected RT" => "setIsExpectedRt",
            "IS RT Window" => "setIsRtWindows",
            "IS Centroid Location" => "setIsCentroidLocation",
            "IS Start Scan" => "setIsStartScan",
            "IS Start Time" => "setIsStartTime",
            "IS Stop Scan" => "setIsStopScan",
            "IS Stop Time" => "setIsStopTime",
            "IS Integration Type" => "setIsIntegrationType",
            "IS Signal To Noise" => "setIsSignalToNoise",
            "IS Peak Width" => "setIsPeakWidth",
            "IS Mass Ranges" => "setIsMassRanges",
            "IS Wavelength Ranges" => "setIsWavelengthRanges",
            "IS Channel" => "setIsChannel",
            "IS Peak Width at 50% Height" => "setIsPeakWidthAl50Height",
            "IS Slope of Baseline" => "setIsSlopeOfBaseline",
            "IS Processing Alg." => "setIsProcessingAlg",
            "IS Peak Asymmetry" => "setIsPeakAsymemtry",
            "Record Modified" => "setRecordModified",
            "Area Ratio" => "setAreaRatio",
            "Calculated Concentration for DAD" => "setCalculatedConcentrationForDad",
            "Relative Retention Time" => "setRelativeRetentionTime",
            "Response Factor" => "setResponseFactor",
        );
    }

}



class Verification
{

    /*
     * V4, V6, V10
     */

    public static function update($where, $fkParameter)
    {
        return "
            UPDATE Alae\Entity\SampleBatch s 
            SET s.parameters = (SELECT CONCAT(p.pkParameter, ',') FROM Alae\Entity\Parameter p WHERE p.rule = '" . $fkParameter . "')
            WHERE $where";
    }

    /*
     * V5, V7, V8, V9
     */
    public static function updateInner($table, $join, $fkParameter)
    {
        return "
            UPDATE Alae\Entity\SampleBatch s
            INNER JOIN $table t ON $join
            SET s.parameters = (SELECT CONCAT(p.pkParameter, ',') FROM Alae\Entity\Parameter p WHERE p.rule = '" . $fkParameter . "')";
    }


}

class VerificationController extends BaseController
{
    protected function getParameters($rule)
    {
        $parameter = $this->getRepository("\\Alae\\Entity\\Parameter")
                    ->findBy(array('rule' => $rule));

        return $parameter[0];
    }
}


/*******************************

INSERT INTO alae_parameter (rule, verification, min_value, max_value, code_error, message_error) VALUES
('V1','Revisión del archivo exportado  (código de estudio)',null,null,null,'V1 - EXPORT ERRÓNEO'),
('V2','Revisión del archivo exportado (abreviatura analito)',null,null,null,'V2 - ANALITO ERRÓNEO'),
('V3','Revisión del archivo exportado  (nº de lote)',null,null,null,'V3 - EXPORT ERRÓNEO'),
('V4','Sample Type',null,null,null,'V4 - SAMPLE TYPE ERRÓNEO'),
('V5','Concentración nominal de CS/QC',null,null,null,'V5 - CONCENTRACIÓN NOMINAL ERRÓNEA'),
('V6.1','Replicados CS (mínimo)',2,null,null,'V6.1 - REPLICADOS INSUFICIENTES'),
('V6.2','Replicados QC (mínimo)',2,null,null,'V6.2 - REPLICADOS INSUFICIENTES'),
('V7','Sample Name repetido',null,null,null,'V7 - SAMPLE NAME REPETIDO'),
('V8','Búsqueda de Muestras reinyectadas',null,null,null,null)
('V9.1','Accuracy (QCRx*)',0.85,1.15,'O','V9.1 - QCR* ACCURACY FUERA DE RANGO'),
('V9.2','Use record = 0 ( QCRx*)',null,null,'O','V9.2 - QCR* USE RECORD NO VALIDO'),
('V9.3','Que tanto V 9.1 como V 9.2 se cumplan',null,null,'O','V9.3 - QCR* NO VALIDO'),
('V10.1','Accuracy (CS1)',0.8,1.2,'O','V10.1 - NO CUMPLE ACCURACY'),
('V10.2','Accuracy (CS2-CSx)',0.85,1.15,'O','V10.2 - NO CUMPLE ACCURACY'),
('V10.3','Accuracy (QC)',0.85,1.15,'O','V10.3 - NO CUMPLE ACCURACY'),
('V10.4','Accuracy (DQC)',0.85,1.15,'O','V10.4 - NO CUMPLE ACCURACY'),
('V11','Revisión del dilution factor en HDQC / LDQC',null,null,'O','V11- FACTOR DILUCIÓN ERRÓNEO'),
('V12','Use record (CS/QC/DQC)',null,null,null,null)
('V13.1','Selección manual de los CS válidos',null,null,null,null)
('V13.2','Interf. Analito en BLK',0.2,null,'O','V13.2 - BLK NO CUMPLE'),
('V13.3','Interf. IS en BLK',0.05,null,'O','V13.3 - BLK NO CUMPLE'),
('V13.4','Interf. Analito en ZS',0.2,null,'O','V13.4 - ZS NO CUMPLE'),
('V15','75% CS',0.75,null,null,'V15 - LOTE RECHAZADO (75% CS)'),
('V16','CS consecutivos',null,null,null,'V16 - LOTE RECHAZADO (CS CONSECUTIVOS)'),
('V17','r > 0.99',0.99,null,null,'V17 - LOTE RECHAZADO (r< 0.99)'),
('V18','67% QC',0.67,null,null,'V18 - LOTE RECHAZADO (67% QC)'),
('V19','50% de cada nivel de QC',0.5,null,null,'V19 - LOTE RECHAZADO (50% QCx)'),
('V20.1','50% BLK',0.5,null,null,'V20.1 - LOTE RECHAZADO (INTERF. BLK)'),
('V20.2','50% ZS',0.5,null,null,'V20.2 - LOTE RECHAZADO (INTERF. ZS)'),
('V21','Conc. (unknown) > ULOQ ( E )',null,null,'E','V21 - CONC. SUPERIOR AL ULOQ'),
('V22','Variabilidad IS (unknown) ( H )',null,null,'H','V22 - VARIABILIDAD IS'),
('V23','< 5% respuesta IS (unknown) ( B )',null,null,'B','V23 - ERROR EXTRACCIÓN IS'),
('V24','Fuera rango recta truncada ( F )',null,null,'F','V24 - FUERA DE RANGO/RECTA TRUNCADA')




 *
 */
?>