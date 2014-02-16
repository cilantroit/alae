<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Batch
 *
 * @ORM\Table(name="alae_batch", indexes={@ORM\Index(name="fk_parameter", columns={"fk_parameter"}), @ORM\Index(name="fk_analyte", columns={"fk_analyte"}), @ORM\Index(name="fk_user", columns={"fk_user"}), @ORM\Index(name="fk_study", columns={"fk_study"})})
 * @ORM\Entity
 */
class Batch
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_batch", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkBatch;

    /**
     * @var integer
     *
     * @ORM\Column(name="serial", type="integer", nullable=true)
     */
    protected $serial;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=100, nullable=true)
     */
    protected $fileName;

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
     * @var boolean
     *
     * @ORM\Column(name="valid_flag", type="boolean", nullable=true)
     */
    protected $validFlag;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accepted_flag", type="boolean", nullable=true)
     */
    protected $acceptedFlag;

    /**
     * @var string
     *
     * @ORM\Column(name="justification", type="string", length=250, nullable=true)
     */
    protected $justification;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="validation_date", type="datetime", nullable=false)
     */
    protected $validationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="code_error", type="string", length=10, nullable=true)
     */
    protected $codeError;

    /**
     * @var string
     *
     * @ORM\Column(name="intercept", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $intercept = '0.0000';

    /**
     * @var string
     *
     * @ORM\Column(name="slope", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $slope = '0.0000';

    /**
     * @var string
     *
     * @ORM\Column(name="correlation_coefficient", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $correlationCoefficient = '0.0000';

    /**
     * @var integer
     *
     * @ORM\Column(name="cs_total", type="integer", nullable=false)
     */
    protected $csTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="qc_total", type="integer", nullable=false)
     */
    protected $qcTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="ldqc_total", type="integer", nullable=false)
     */
    protected $ldqcTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="hdqc_total", type="integer", nullable=false)
     */
    protected $hdqcTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="cs_accepted_total", type="integer", nullable=false)
     */
    protected $csAcceptedTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="qc_accepted_total", type="integer", nullable=false)
     */
    protected $qcAcceptedTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="ldqc_accepted_total", type="integer", nullable=false)
     */
    protected $ldqcAcceptedTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="hdqc_accepted_total", type="integer", nullable=false)
     */
    protected $hdqcAcceptedTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="is_cs_qc_accepted_avg", type="integer", nullable=false)
     */
    protected $isCsQcAcceptedAvg = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_concentration_units", type="string", length=250, nullable=true)
     */
    protected $analyteConcentrationUnits;

    /**
     * @var string
     *
     * @ORM\Column(name="calculated_concentration_units", type="string", length=250, nullable=true)
     */
    protected $calculatedConcentrationUnits;

    /**
     * @var \Alae\Entity\Parameter
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Parameter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_parameter", referencedColumnName="pk_parameter")
     * })
     */
    protected $fkParameter;

    /**
     * @var \Alae\Entity\Analyte
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Analyte")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_analyte", referencedColumnName="pk_analyte")
     * })
     */
    protected $fkAnalyte;

    /**
     * @var \Alae\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_user", referencedColumnName="pk_user")
     * })
     */
    protected $fkUser;

    /**
     * @var \Alae\Entity\Study
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Study")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_study", referencedColumnName="pk_study")
     * })
     */
    protected $fkStudy;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
    }

    public function getPkBatch()
    {
        return $this->pkBatch;
    }

    public function setPkBatch($pkBatch)
    {
        $this->pkBatch = $pkBatch;
    }

    public function getSerial()
    {
        return $this->serial;
    }

    public function setSerial($serial)
    {
        $this->serial = $serial;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getCreatedAt()
    {
        return $this->createdAt->format('d.m.Y H:i:s');
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return !is_null($this->updatedAt) ? $this->updatedAt->format('d.m.Y H:i:s') : "";
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getValidFlag()
    {
        return $this->validFlag;
    }

    public function setValidFlag($validFlag)
    {
        $this->validFlag = $validFlag;
    }

    public function getAcceptedFlag()
    {
        return $this->acceptedFlag;
    }

    public function setAcceptedFlag($acceptedFlag)
    {
        $this->acceptedFlag = $acceptedFlag;
    }

    public function getJustification()
    {
        return $this->justification;
    }

    public function setJustification($justification)
    {
        $this->justification = $justification;
    }

    public function getValidationDate()
    {
        return !is_null($this->validationDate) ? $this->validationDate->format('d.m.Y H:i:s') : "";
    }

    public function setValidationDate(\DateTime $validationDate)
    {
        $this->validationDate = $validationDate;
    }

    public function getCodeError()
    {
        return $this->codeError;
    }

    public function setCodeError($codeError)
    {
        $this->codeError = $codeError;
    }

    public function getIntercept()
    {
        return $this->intercept;
    }

    public function setIntercept($intercept)
    {
        $this->intercept = $intercept;
    }

    public function getSlope()
    {
        return $this->slope;
    }

    public function setSlope($slope)
    {
        $this->slope = $slope;
    }

    public function getCorrelationCoefficient()
    {
        return $this->correlationCoefficient;
    }

    public function setCorrelationCoefficient($correlationCoefficient)
    {
        $this->correlationCoefficient = $correlationCoefficient;
    }

    public function getCsTotal()
    {
        return $this->csTotal;
    }

    public function setCsTotal($csTotal)
    {
        $this->csTotal = $csTotal;
    }

    public function getQcTotal()
    {
        return $this->qcTotal;
    }

    public function setQcTotal($qcTotal)
    {
        $this->qcTotal = $qcTotal;
    }

    public function getLdqcTotal()
    {
        return $this->ldqcTotal;
    }

    public function setLdqcTotal($ldqcTotal)
    {
        $this->ldqcTotal = $ldqcTotal;
    }

    public function getHdqcTotal()
    {
        return $this->hdqcTotal;
    }

    public function setHdqcTotal($hdqcTotal)
    {
        $this->hdqcTotal = $hdqcTotal;
    }

    public function getCsAcceptedTotal()
    {
        return $this->csAcceptedTotal;
    }

    public function setCsAcceptedTotal($csAcceptedTotal)
    {
        $this->csAcceptedTotal = $csAcceptedTotal;
    }

    public function getQcAcceptedTotal()
    {
        return $this->qcAcceptedTotal;
    }

    public function setQcAcceptedTotal($qcAcceptedTotal)
    {
        $this->qcAcceptedTotal = $qcAcceptedTotal;
    }

    public function getLdqcAcceptedTotal()
    {
        return $this->ldqcAcceptedTotal;
    }

    public function setLdqcAcceptedTotal($ldqcAcceptedTotal)
    {
        $this->ldqcAcceptedTotal = $ldqcAcceptedTotal;
    }

    public function getHdqcAcceptedTotal()
    {
        return $this->hdqcAcceptedTotal;
    }

    public function setHdqcAcceptedTotal($hdqcAcceptedTotal)
    {
        $this->hdqcAcceptedTotal = $hdqcAcceptedTotal;
    }

    public function getIsCsQcAcceptedAvg()
    {
        return $this->isCsQcAcceptedAvg;
    }

    public function setIsCsQcAcceptedAvg($isCsQcAcceptedAvg)
    {
        $this->isCsQcAcceptedAvg = $isCsQcAcceptedAvg;
    }

    public function getAnalyteConcentrationUnits()
    {
        return $this->analyteConcentrationUnits;
    }

    public function setAnalyteConcentrationUnits($analyteConcentrationUnits)
    {
        $this->analyteConcentrationUnits = $analyteConcentrationUnits;
    }

    public function getCalculatedConcentrationUnits()
    {
        return $this->calculatedConcentrationUnits;
    }

    public function setCalculatedConcentrationUnits($calculatedConcentrationUnits)
    {
        $this->calculatedConcentrationUnits = $calculatedConcentrationUnits;
    }

    public function getFkParameter()
    {
        return $this->fkParameter;
    }

    public function setFkParameter(\Alae\Entity\Parameter $fkParameter)
    {
        $this->fkParameter = $fkParameter;
    }

    public function getFkAnalyte()
    {
        return $this->fkAnalyte;
    }

    public function setFkAnalyte(\Alae\Entity\Analyte $fkAnalyte)
    {
        $this->fkAnalyte = $fkAnalyte;
    }

    public function getFkUser()
    {
        return $this->fkUser;
    }

    public function setFkUser(\Alae\Entity\User $fkUser)
    {
        $this->fkUser = $fkUser;
    }

    public function getFkStudy()
    {
        return $this->fkStudy;
    }

    public function setFkStudy(\Alae\Entity\Study $fkStudy)
    {
        $this->fkStudy = $fkStudy;
    }

}
