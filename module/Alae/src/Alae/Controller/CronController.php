<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

  /**
 * Modulo encargado del procesamiento de lotes.
 * En este modulo se comprueban las 3 primeras verificaciones,
 * además, se asigna cada lote al estudio y analito que corresponde
   * Limpia los enlaces.
 * @author Maria Quiroz
 */

namespace Alae\Controller;

use Alae\Controller\BaseController,
    Alae\Service\Helper as Helper;

class CronController extends BaseController
{
    protected $_Study   = null;
    protected $_Analyte = null;
    protected $_error   = false;
    protected $_other;
    protected $_analyteConcentrationUnits;
    protected $_calculatedConcentrationUnits;

    public function init()
    {

    }

    /**
     * Verificamos que el lote no se encuentre repetido.
     */
    private function isRepeatedBatch($fileName)
    {
        $query = $this->getEntityManager()->createQuery("
                SELECT COUNT(b.pkBatch)
                FROM Alae\Entity\Batch b
                WHERE b.fileName = '" . $fileName . "' AND b.fkStudy IS NOT NULL AND b.fkAnalyte IS NOT NULL");
        $count = $query->getSingleScalarResult();

        if ($count == 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Realizamos la búsqueda del estudio y analito al cual pertenece el lote.
     *      1.- Buscamos las coincidencias que tenga el nombre del fichero con los estudios que estén definidos
     *      que no estén cerrados y que estén aprobados.
     *      2.- Buscamos el analito (abreviatura), que este asociado al estudio
     */
    private function validateFile($fileName)
    {
        $response = $this->explodeFile($fileName);
        $this->_Study   = $this->_Analyte = null;

        $query = $this->getEntityManager()->createQuery("
                SELECT s
                FROM Alae\Entity\Study s
                WHERE s.code LIKE  '%" . $response['study'] . "%' AND s.closeFlag = 0 AND s.approve = 1
                ORDER BY s.code DESC")
            ->setMaxResults(1);
        $elements = $query->getResult();

        if (count($elements) > 0)
        {
            foreach ($elements as $Study)
            {
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->select("h")
                    ->from("Alae\Entity\AnalyteStudy", "h")
                    ->innerJoin("Alae\Entity\Analyte", "a", "WITH", "h.fkAnalyte = a.pkAnalyte AND a.shortening = '" . $response['analyte'] . "' AND h.fkStudy = " . $Study->getPkStudy());
                $anaStudy = $qb->getQuery()->getResult();

                if (count($anaStudy) && $anaStudy[0]->getFkStudy()->getApprove())
                {
                    $this->_Study   = $Study;
                    $this->_Analyte = $anaStudy[0]->getFkAnalyte();
                }
            }
        }
    }

    /**
     * Leemos el fichero y hacemos las siguientes comprobaciones:
     *      1.- Descartamos aquellos ficheros que estén repetidos
     *      2.- Comprobamos que se cumpla la estructura del nombre del fichero
     *      3.- Insertamos el lote (si el fichero cumple con los puntos 1 y 2, asignamos el lote al analito y
     *      estudio que corresponde, de lo contrario, pasar a ser un lote sin asignar)
     */
    public function readAction()
    {
        $files = scandir(Helper::getVarsConfig("batch_directory"), 1);

        foreach ($files as $file)
        {
            $this->_other = array();
            if (!is_dir($file))
            {
                if(!$this->isRepeatedBatch($file))
                {
                    if (preg_match("/^([a-zA-Z0-9]+-\d{4}(-[0-9]+)?)\+(M|O|R)[0-9]*\_[a-zA-Z0-9]+\.txt$/i", $file))
                    {
                        $this->validateFile($file);
                    }
                }

                $this->insertBatch($file, $this->_Study, $this->_Analyte);
                rename(Helper:: getVarsConfig("batch_directory") . "/" . $file, Helper:: getVarsConfig("batch_directory_older") . "/" . $file);

                if (file_exists(Helper:: getVarsConfig("batch_directory") . "/" . $file))
                {
                    unlink(Helper:: getVarsConfig("batch_directory") . "/" . $file);
                }
            }
        }
    }

    /**
     * Extraemos la información del fichero, obteniendo:
     *      Lote
     *      Código de estudio
     *      Abrv. Analito
     * @param string $file
     * @return array
     */
    protected function explodeFile($file)
    {
        $string = substr($file, 0, -4);
        $array = explode("-", $string);

        if (count($array) == 3)
        {
            $return = array(
                "batch"   => $array[0],
                "study"   => $array[1],
                "analyte" => preg_replace("/[0-9]+\+(M|O|R)[0-9]*\_/", "", $array[2])
            );
        }
        else
        {
            $return = array(
                "batch"   => $array[0],
                "study"   => preg_replace("/\+(M|O|R)[0-9]*\_[a-zA-Z0-9]+/", "", $array[1]),
                "analyte" => preg_replace("/[0-9]+\+(M|O|R)[0-9]*\_/", "", $array[1])
            );
        }

        return $return;
    }

    /**
     * Ingreso del lote:
     *      NOTA: si el lote se asigna a un estudio y analito, comprobamos las verificaciones 2,3
     */
    private function insertBatch($fileName, $Study, $Analyte)
    {
        $data  = $this->getData(Helper::getVarsConfig("batch_directory") . "/" . $fileName, $Study, $Analyte);
        $Batch = $this->saveBatch($fileName);

        if(count($data["data"]) > 0)
        {
            $this->saveSampleBatch($data["headers"], $data['data'], $Batch);

            if (!is_null($Analyte) && !is_null($Study))
            {
                $this->batchVerify($Batch, $Analyte, $fileName);
                $this->updateBatch($Batch, $Analyte, $Study);
            }
            else
            {
                $this->execute(\Alae\Service\Verification::updateBatch("b.pkBatch = " . $Batch->getPkBatch(), "V1"));
            }
        }
        else
        {
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
            if (preg_match("/Calculated Concentration/i", $header) && !preg_match("/Calculated Concentration for DAD/i", $header))
            {
                $this->_calculatedConcentrationUnits = preg_replace(array("/Calculated Concentration\s/", "/\(/", "/\)/"), "", $header);
            }

            $newsHeaders[] = preg_replace('/\s\(([a-zA-Z]|\s|\/|%)+\)/i', '', trim($header));
        }
        return $newsHeaders;
    }

    /**
     * Leemos el fichero
     */
    private function getData($filename)
    {
        $fp      = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        fclose($fp);

        $lines    = explode("\n", $content);
        $continue = false;
        $data     = $headers  = $other    = array();

        foreach ($lines as $line)
        {
            if ($continue)
            {
                $data[] = explode("\t", $line);
            }

            if (strstr($line, "Sample Name"))
            {
                $headers  = $this->cleanHeaders(explode("\t", $line));
                $continue = true;
            }

            if (!$continue)
            {
                $this->_other[] = $line;
            }
        }

        return array(
            "headers" => $headers,
            "data"    => $data
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

    /*
     * Función que se encarga del almacenamiento del batch
     */
    private function saveBatch($fileName)
    {
        $response = $this->explodeFile($fileName);

        $Batch = new \Alae\Entity\Batch();
        $Batch->setSerial((string) $response['batch']);
        $Batch->setFileName($fileName);
        $Batch->setFkUser($this->_getSystem());
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();

        return $Batch;
    }

    /*
     * Actualiza la tabla bacth
     */
    private function updateBatch($Batch, $Analyte, $Study)
    {
        $header = $this->getHeaderInfo($Analyte);
        if (count($header) > 0)
        {
            $Batch->setIntercept($header['intercept']);
            $Batch->setCorrelationCoefficient($header['correlationCoefficient']);
            $Batch->setSlope($header['slope']);
        }

        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(e.fkParameter)
            FROM Alae\Entity\Error e, Alae\Entity\SampleBatch s
            WHERE s.pkSampleBatch = e.fkSampleBatch
                AND s.fkBatch = " . $Batch->getPkBatch());
        $errors = $query->getSingleScalarResult();

        if ($errors > 0)
        {
            $Batch->setValidFlag(false);
            $Batch->setValidationDate(new \DateTime('now'));
        }

        $Batch->setAnalyteConcentrationUnits($this->_analyteConcentrationUnits);
        $Batch->setCalculatedConcentrationUnits($this->_calculatedConcentrationUnits);
        $Batch->setFkAnalyte($Analyte);
        $Batch->setFkStudy($Study);
        $this->getEntityManager()->persist($Batch);
        $this->getEntityManager()->flush();
    }

    //obtención de información de cabeceras del fichero
    private function getHeaderInfo($Analyte)
    {
        $header   = array();
        $continue = false;
        $count    = 0;
        foreach ($this->_other as $line)
        {
            if (strstr($line, $Analyte->getShortening()))
            {
                $continue = true;
                continue;
            }
            if ($continue)
            {
                if (strstr($line, "Intercept"))
                {
                    $aux                 = explode("\t", $line);
                    $header['intercept'] = $aux[1];
                    $count++;
                    continue;
                }
                if (strstr($line, "Slope"))
                {
                    $aux             = explode("\t", $line);
                    $header['slope'] = $aux[1];
                    $count++;
                    continue;
                }
                if (strstr($line, "Correlation coefficient"))
                {
                    $aux                              = explode("\t", $line);
                    $header['correlationCoefficient'] = $aux[1];
                    $count++;
                    continue;
                }
            }

            if ($count == 3)
                break;
        }

        return $header;
    }

    /*
     * Registra errores detectados en el fichero export
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

    /*
     * Verifica que el fichero haya superado las verificaciones 2 y 3
     */
    private function batchVerify($Batch, $Analyte, $fileName)
    {
        $response = $this->explodeFile($fileName);

        $fkParameter = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V2"));
        $where = "s.analytePeakName <> '" . $Analyte->getShortening() . "' AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $fkParameter[0], array(), false);

        $fkParameter = $this->getRepository("\\Alae\\Entity\\Parameter")->findBy(array("rule" => "V3"));
        $fileName = $response['batch'] . "-" . $response['study'];
        $where = "s.fileName NOT LIKE '$fileName%' AND s.fkBatch = " . $Batch->getPkBatch();
        $this->error($where, $fkParameter[0], array(), false);
    }

    /*
     * Función para almacenar información en la tabla sampleBatch
     */
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
            }
        }
    }

    /*
     * Tabla de correspondencia de columnas del fichero export
     */
    private function getSampleBatch()
    {
        return array(
            "Sample Name"                      => "setSampleName",
            "Analyte Peak Name"                => "setAnalytePeakName",
            "Sample Type"                      => "setSampleType",
            "File Name"                        => "setFileName",
            "Dilution Factor"                  => "setDilutionFactor",
            "Analyte Peak Area"                => "setAnalytePeakArea",
            "IS Peak Name"                     => "setIsPeakName",
            "IS Peak Area"                     => "setIsPeakArea",
            "Analyte Concentration"            => "setAnalyteConcentration",
            "Calculated Concentration"         => "setCalculatedConcentration",
            "Accuracy"                         => "setAccuracy",
            "Use Record"                       => "setUseRecord",
            "Acquisition Date"                 => "setAcquisitionDate",
            "Analyte Integration Type"         => "setAnalyteIntegrationType",
            "IS Integration Type"              => "setIsIntegrationType",
            "Record Modified"                  => "setRecordModified",
            "Sample ID"                        => "setSampleId",
            "Sample Comment"                   => "setSampleComment",
            "Set Number"                       => "setSetNumber",
            "Acquisition Method"               => "setAcquisitionMethod",
            "Rack Type"                        => "setRackType",
            "Rack Position"                    => "setRackPosition",
            "Vial Position"                    => "setVialPosition",
            "Plate Type"                       => "setPlateType",
            "Plate Position"                   => "setPlatePosition",
            "Weight To Volume Ratio"           => "setWeightToVolumeRatio",
            "Sample Annotation"                => "setSampleAnnotation",
            "Disposition"                      => "setDisposition",
            "Analyte Units"                    => "setAnalyteUnits",
            "Analyte Peak Area for DAD"        => "setAnalytePeakAreaForDad",
            "Analyte Peak Height"              => "setAnalytePeakHeight",
            "Analyte Peak Height for DAD"      => "setAnalytePeakHeightForDad",
            "Analyte Retention Time"           => "setAnalyteRetentionTime",
            "Analyte Expected RT"              => "setAnalyteExpectedRt",
            "Analyte RT Window"                => "setAnalyteRtWindow",
            "Analyte Centroid Location"        => "setAnalyteCentroidLocation",
            "Analyte Start Scan"               => "setAnalyteStartScan",
            "Analyte Start Time"               => "setAnalyteStartTime",
            "Analyte Stop Scan"                => "setAnalyteStopScan",
            "Analyte Stop Time"                => "setAnalyteStopTime",
            "Analyte Signal To Noise"          => "setAnalyteSignalToNoise",
            "Analyte Peak Width"               => "setAnalytePeakWidth",
            "Standard Query Status"            => "setAnalyteStandarQueryStatus",
            "Analyte Mass Ranges"              => "setAnalyteMassRanges",
            "Analyte Wavelength Ranges"        => "setAnalyteWavelengthRanges",
            "Height Ratio"                     => "setHeightRatio",
            "Analyte Annotation"               => "setAnalyteAnnotation",
            "Analyte Channel"                  => "setAnalyteChannel",
            "Analyte Peak Width at 50% Height" => "setAnalytePeakWidthAt50Height",
            "Analyte Slope of Baseline"        => "setAnalyteSlopeOfBaseline",
            "Analyte Processing Alg."          => "setAnalyteProcessingAlg",
            "Analyte Peak Asymmetry"           => "setAnalytePeakAsymmetry",
            "IS Units"                         => "setIsUnits",
            "IS Peak Area for DAD"             => "setIsPeakAreaForDad",
            "IS Peak Height"                   => "setIsPeakHeight",
            "IS Peak Height for DAD"           => "setIsPeakHeightForDad",
            "IS Concentration"                 => "setIsConcentration",
            "IS Retention Time"                => "setIsRetentionTime",
            "IS Expected RT"                   => "setIsExpectedRt",
            "IS RT Window"                     => "setIsRtWindows",
            "IS Centroid Location"             => "setIsCentroidLocation",
            "IS Start Scan"                    => "setIsStartScan",
            "IS Start Time"                    => "setIsStartTime",
            "IS Stop Scan"                     => "setIsStopScan",
            "IS Stop Time"                     => "setIsStopTime",
            "IS Signal To Noise"               => "setIsSignalToNoise",
            "IS Peak Width"                    => "setIsPeakWidth",
            "IS Mass Ranges"                   => "setIsMassRanges",
            "IS Wavelength Ranges"             => "setIsWavelengthRanges",
            "IS Channel"                       => "setIsChannel",
            "IS Peak Width at 50% Height"      => "setIsPeakWidthAl50Height",
            "IS Slope of Baseline"             => "setIsSlopeOfBaseline",
            "IS Processing Alg."               => "setIsProcessingAlg",
            "IS Peak Asymmetry"                => "setIsPeakAsymemtry",
            "Area Ratio"                       => "setAreaRatio",
            "Calculated Concentration for DAD" => "setCalculatedConcentrationForDad",
            "Relative Retention Time"          => "setRelativeRetentionTime",
            "Response Factor"                  => "setResponseFactor",
        );
    }
}
