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
    Alae\Service\Helper as Helper;

class CronController extends BaseController
{

    protected $_Study = null;
    protected $_Analyte = null;
    protected $_error = false;

    private function validateFile($fileName)
    {
        $string = substr($fileName, 0, -4);
        list($pkBatch, $aux) = explode("-", $string);
        list($codeStudy, $shortening) = explode("_", $aux);
        $this->_Study = $this->_Analyte = null;

        $query = $this->getEntityManager()->createQuery("SELECT COUNT(b.pkBatch) FROM Alae\Entity\Batch b WHERE b.fileName = '" . $fileName . "'");
        $count = $query->getSingleScalarResult();

        if ($count == 0)
        {
            $studies = $this->getRepository("\\Alae\\Entity\\Study")
                    ->findBy(array('code' => $codeStudy));

            if (count($studies) == 1 && $studies[0]->getCloseFlag() == false)
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
        else
        {
            $this->setErrorTransaction('Repeated batch', $fileName);
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
                if (preg_match("/^(\d+-\d+\_[a-zA-Z0-9]+\.txt)$/i", $file))
                {
                    $this->validateFile($file);
                }
                else
                {
                    $this->setErrorTransaction('Invalid_file_name_in_the_export_process_batches_of_analytes', $file);
                }

                $this->insertBatch(Helper::getVarsConfig("batch_directory") . "/" . $file, $this->_Study, $this->_Analyte);

                //break;
            }
        }
    }

    private function insertBatch($fileName, $Study, $Analyte)
    {
        $data = $this->getData($fileName, $Study, $Analyte);
        //var_dump($data);
        $Batch = $this->saveBatch($fileName, $Analyte, $Study);
        $this->saveSampleBatch($data["headers"], $data['data'], $Batch);
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

    private function saveBatch($fileName, $Analyte, $Study)
    {
        $Batch = new \Alae\Entity\Batch();
        $Batch->setFileName($fileName);
        $Batch->setFkUser($this->_getSystem());

        if (!is_null($Analyte))
            $Batch->setFkAnalyte($Analyte);

        if (!is_null($Study))
            $Batch->setFkStudy($Study);


        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();

        return $Batch;
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
?>
