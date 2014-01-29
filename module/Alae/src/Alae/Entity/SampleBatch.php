<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SampleBatch
 *
 * @ORM\Table(name="alae_sample_batch", indexes={@ORM\Index(name="fk_batch", columns={"fk_batch"})})
 * @ORM\Entity
 */
class SampleBatch
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_sample_batch", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkSampleBatch;

    /**
     * @var string
     *
     * @ORM\Column(name="sample_name", type="string", length=250, nullable=false)
     */
    protected $sampleName;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_peak_name", type="string", length=250, nullable=false)
     */
    protected $analytePeakName;

    /**
     * @var string
     *
     * @ORM\Column(name="sample_type", type="string", length=250, nullable=false)
     */
    protected $sampleType;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=250, nullable=false)
     */
    protected $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="dilution_factor", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $dilutionFactor;

    /**
     * @var integer
     *
     * @ORM\Column(name="analyte_peak_area", type="integer", nullable=false)
     */
    protected $analytePeakArea;

    /**
     * @var string
     *
     * @ORM\Column(name="is_peak_name", type="string", length=250, nullable=false)
     */
    protected $isPeakName;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_peak_area", type="integer", nullable=false)
     */
    protected $isPeakArea;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_concentration", type="decimal", precision=19, scale=2, nullable=true)
     */
    protected $analyteConcentration;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_concentration_units", type="string", length=250, nullable=false)
     */
    protected $analyteConcentrationUnits;

    /**
     * @var string
     *
     * @ORM\Column(name="calculated_concentration", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $calculatedConcentration;

    /**
     * @var string
     *
     * @ORM\Column(name="calculated_concentration_units", type="string", length=250, nullable=false)
     */
    protected $calculatedConcentrationUnits;

    /**
     * @var string
     *
     * @ORM\Column(name="accuracy", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $accuracy;

    /**
     * @var integer
     *
     * @ORM\Column(name="use_record", type="integer", nullable=true)
     */
    protected $useRecord = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="valid_flag", type="boolean", nullable=true)
     */
    protected $validFlag = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="code_error", type="string", length=50, nullable=true)
     */
    protected $codeError;

    /**
     * @var string
     *
     * @ORM\Column(name="parameters", type="string", length=50, nullable=true)
     */
    protected $parameters;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var \Alae\Entity\Batch
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Batch")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_batch", referencedColumnName="pk_batch")
     * })
     */
    protected $fkBatch;

    public function getPkSampleBatch()
    {
        return $this->pkSampleBatch;
    }

    public function setPkSampleBatch($pkSampleBatch)
    {
        $this->pkSampleBatch = $pkSampleBatch;
    }

    public function getSampleName()
    {
        return $this->sampleName;
    }

    public function setSampleName($sampleName)
    {
        $this->sampleName = $sampleName;
    }

    public function getAnalytePeakName()
    {
        return $this->analytePeakName;
    }

    public function setAnalytePeakName($analytePeakName)
    {
        $this->analytePeakName = $analytePeakName;
    }

    public function getSampleType()
    {
        return $this->sampleType;
    }

    public function setSampleType($sampleType)
    {
        $this->sampleType = $sampleType;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getDilutionFactor()
    {
        return $this->dilutionFactor;
    }

    public function setDilutionFactor($dilutionFactor)
    {
        $this->dilutionFactor = $dilutionFactor;
    }

    public function getAnalytePeakArea()
    {
        return $this->analytePeakArea;
    }

    public function setAnalytePeakArea($analytePeakArea)
    {
        $this->analytePeakArea = $analytePeakArea;
    }

    public function getIsPeakName()
    {
        return $this->isPeakName;
    }

    public function setIsPeakName($isPeakName)
    {
        $this->isPeakName = $isPeakName;
    }

    public function getIsPeakArea()
    {
        return $this->isPeakArea;
    }

    public function setIsPeakArea($isPeakArea)
    {
        $this->isPeakArea = $isPeakArea;
    }

    public function getAnalyteConcentration()
    {
        return $this->analyteConcentration;
    }

    public function setAnalyteConcentration($analyteConcentration)
    {
        $this->analyteConcentration = $analyteConcentration;
    }

    public function getAnalyteConcentrationUnits()
    {
        return $this->analyteConcentrationUnits;
    }

    public function setAnalyteConcentrationUnits($analyteConcentrationUnits)
    {
        $this->analyteConcentrationUnits = $analyteConcentrationUnits;
    }

    public function getCalculatedConcentration()
    {
        return $this->calculatedConcentration;
    }

    public function setCalculatedConcentration($calculatedConcentration)
    {
        $this->calculatedConcentration = $calculatedConcentration;
    }

    public function getCalculatedConcentrationUnits()
    {
        return $this->calculatedConcentrationUnits;
    }

    public function setCalculatedConcentrationUnits($calculatedConcentrationUnits)
    {
        $this->calculatedConcentrationUnits = $calculatedConcentrationUnits;
    }

    public function getAccuracy()
    {
        return $this->accuracy;
    }

    public function setAccuracy($accuracy)
    {
        $this->accuracy = $accuracy;
    }

    public function getUseRecord()
    {
        return $this->useRecord;
    }

    public function setUseRecord($useRecord)
    {
        $this->useRecord = $useRecord;
    }

    public function getValidFlag()
    {
        return $this->validFlag;
    }

    public function setValidFlag($validFlag)
    {
        $this->validFlag = $validFlag;
    }

    public function getCodeError()
    {
        return $this->codeError;
    }

    public function setCodeError($codeError)
    {
        $this->codeError = $codeError;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getFkBatch()
    {
        return $this->fkBatch;
    }

    public function setFkBatch(\Alae\Entity\Batch $fkBatch)
    {
        $this->fkBatch = $fkBatch;
    }

}
