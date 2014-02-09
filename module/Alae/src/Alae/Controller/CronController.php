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
    protected $_Study   = null;
    protected $_Analyte = null;
    protected $_error   = false;
    protected $_other   = array();
    protected $_analyteConcentrationUnits;
    protected $_calculatedConcentrationUnits;

    public function init()
    {

    }

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
            $qb = $this->getEntityManager()->getRepository("\\Alae\\Entity\\Study")->createQueryBuilder('s')
                    ->where('s.code like :code')
                    ->setParameter('code', '%' . $codeStudy);
            $studies = $qb->getQuery()->getResult();

            if (count($studies) == 1 && $studies [0]->getCloseFlag() == false)
            {
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->select("a")
                        ->from("Alae\Entity\AnalyteStudy", "h")
                        ->innerJoin("Alae\Entity\Analyte", "a", "WITH", "h.fkAnalyte = a.pkAnalyte AND a.shortening = '" . $shortening . "' AND h.fkStudy = " . $studies[0]->getPkStudy());
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
            if (!is_dir($file))
            {
                if (preg_match("/^([a-zA-Z0-9]+-\d+\_[a-zA-Z0-9]+\.txt)$/i", $file))
                {
                    $this->validateFile($file);
                }
                else
                {
                    $this->isRepeatedBatch($file);
                    $this->setErrorTransaction('Invalid_file_name_in_the_export_process_batches_of_analytes', $file);
                }

                $this->insertBatch($file, $this->_Study, $this->_Analyte);
                unlink(Helper:: getVarsConfig("batch_directory") . "/" . $file);
            }
        }
    }

    private function insertBatch($fileName, $Study, $Analyte)
    {
        $data  = $this->getData(Helper::getVarsConfig("batch_directory") . "/" . $fileName, $Study, $Analyte);
        $Batch = $this->saveBatch($fileName);
        $this->saveSampleBatch($data["headers"], $data['data'], $Batch);

        if (!is_null($Analyte) && !is_null($Study))
        {
            $this->batchVerify($Batch, $Analyte, $fileName);
            $this->updateBatch($Batch, $Analyte, $Study);
        }
        else
        {
            $this->execute(\Alae\Service\Verification::update("s.fkBatch = " . $Batch->getPkBatch(), "V1"));
            $this->execute(\Alae\Service\Verification::updateBatch("b.pkBatch = " . $Batch->getPkBatch(), "V1"));
        }
    }

    private function cleanHeaders($headers)
    {
        $newsHeaders = array();

        $this->_analyteConcentrationUnits    = $this->_calculatedConcentrationUnits = "";

        foreach ($headers as $header)
        {
            if (preg_match("/Analyte Concentration/i", $header))
            {
                $this->_analyteConcentrationUnits = preg_replace(array("/Analyte Concentration\s/", "/\(/", "/\)/"), "", $header);
            }
            if (preg_match("/Calculated Concentration/i", $header))
            {
                $this->_calculatedConcentrationUnits = preg_replace(array("/Calculated Concentration\s/", "/\(/", "/\)/"), "", $header);
            }

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
        $data = $headers = $other = array();

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

            if(!$continue)
            {
                $this->_other[] = $line;
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
        $string = substr($fileName, 0, -4);
        list($serial, $aux) = explode("-", $string);

        $Batch = new \Alae\Entity\Batch();
        $Batch->setSerial((string) $serial);
        $Batch->setFileName($fileName);
        $Batch->setFkUser($this->_getSystem());
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();

        return $Batch;
    }

    private function updateBatch($Batch, $Analyte, $Study)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(s.pkSampleBatch)
            FROM Alae\Entity\SampleBatch s
            WHERE s.parameters IS NOT NULL");
        $error = $query->getSingleScalarResult();
        $status = ($error > 0) ? false : true;

        $header = $this->getHeaderInfo($Analyte);

        if (count($header) > 0)
        {
            $Batch->setIntercept($header['intercept']);
            $Batch->setCorrelationCoefficient($header['correlationCoefficient']);
            $Batch->setSlope($header['slope']);
        }

        $Batch->setValidFlag($status);
        $Batch->setValidationDate(new \DateTime('now'));
        $Batch->setFkAnalyte($Analyte);
        $Batch->setFkStudy($Study);
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();
    }

    private function getHeaderInfo($Analyte)
    {
        $header = array();
        $continue = false;
        foreach($this->_other as $line)
        {
            if (strstr($line, $Analyte->getShortening()))
            {
                $continue = true;
            }
            if($continue)
            {
                if(strstr($line, "Intercept"))
                {
                    $aux = explode("\t", $line);
                    $header['intercept'] = $aux[1];
                }
                if(strstr($line, "Slope"))
                {
                    $aux = explode("\t", $line);
                    $header['slope'] = $aux[1];
                }
                if(strstr($line, "Correlation coefficient"))
                {
                    $aux = explode("\t", $line);
                    $header['correlationCoefficient'] = $aux[1];
                }
            }
        }

        return $header;
    }

    private function batchVerify($Batch, $Analyte, $fileName)
    {
        $string = substr($fileName, 0, -4);
        list($pkBatch, $aux) = explode("-", $string);
        $this->execute(\Alae\Service\Verification::update("s.analytePeakName <> '" . $Analyte->getShortening() . "' AND s.fkBatch = " . $Batch->getPkBatch(), "V1", array("s.validFlag = 0")));
        $this->execute(\Alae\Service\Verification::update("SUBSTRING(s.fileName, 1, 2) <> '" . $pkBatch . "' AND s.fkBatch = " . $Batch->getPkBatch(), "V2", array("s.validFlag = 0")));
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
                $SampleBatch->setAnalyteConcentrationUnits($this->_analyteConcentrationUnits);
                $SampleBatch->setCalculatedConcentrationUnits($this->_calculatedConcentrationUnits);
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
            "Calculated Concentration" => "setCalculatedConcentration",
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

?>
