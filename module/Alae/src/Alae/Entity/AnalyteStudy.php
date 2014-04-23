<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnalyteStudy
 *
 * @ORM\Table(name="alae_analyte_study", indexes={@ORM\Index(name="fk_analyte", columns={"fk_analyte"}), @ORM\Index(name="fk_analyte_is", columns={"fk_analyte_is"}), @ORM\Index(name="fk_unit", columns={"fk_unit"}), @ORM\Index(name="IDX_26E654AD2792B43C", columns={"fk_study"})})
 * @ORM\Entity
 */
class AnalyteStudy
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_analyte_study", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkAnalyteStudy;

    /**
     * @var integer
     *
     * @ORM\Column(name="cs_number", type="integer", nullable=false)
     */
    protected $csNumber = '8';

    /**
     * @var integer
     *
     * @ORM\Column(name="qc_number", type="integer", nullable=false)
     */
    protected $qcNumber = '4';

    /**
     * @var string
     *
     * @ORM\Column(name="cs_values", type="string", length=100, nullable=true)
     */
    protected $csValues;

    /**
     * @var string
     *
     * @ORM\Column(name="hdqc_values", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $hdqcValues;

    /**
     * @var string
     *
     * @ORM\Column(name="ldqc_values", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $ldqcValues;

    /**
     * @var string
     *
     * @ORM\Column(name="qc_values", type="string", length=100, nullable=true)
     */
    protected $qcValues;

    /**
     * @var string
     *
     * @ORM\Column(name="internal_standard", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $internalStandard;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    protected $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_used", type="boolean", nullable=false)
     */
    protected $isUsed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var \Alae\Entity\Study
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Study")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_study", referencedColumnName="pk_study")
     * })
     */
    protected $fkStudy;

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
     * @var \Alae\Entity\Analyte
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Analyte")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_analyte_is", referencedColumnName="pk_analyte")
     * })
     */
    protected $fkAnalyteIs;

    /**
     * @var \Alae\Entity\Unit
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Unit")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_unit", referencedColumnName="pk_unit")
     * })
     */
    protected $fkUnit;

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
     * @var \Alae\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_user_approve", referencedColumnName="pk_user")
     * })
     */
    protected $fkUserApprove;

    public function __construct()
    {
        $this->updatedAt = new \DateTime('now');
    }

    public function getPkAnalyteStudy()
    {
        return $this->pkAnalyteStudy;
    }

    public function setPkAnalyteStudy($pkAnalyteStudy)
    {
        $this->pkAnalyteStudy = $pkAnalyteStudy;
    }

    public function getCsNumber()
    {
        return $this->csNumber;
    }

    public function setCsNumber($csNumber)
    {
        $this->csNumber = $csNumber;
    }

    public function getQcNumber()
    {
        return $this->qcNumber;
    }

    public function setQcNumber($qcNumber)
    {
        $this->qcNumber = $qcNumber;
    }

    public function getCsValues()
    {
        return $this->csValues;
    }

    public function setCsValues($csValues)
    {
        $this->csValues = $csValues;
    }

    public function getQcValues()
    {
        return $this->qcValues;
    }

    public function setQcValues($qcValues)
    {
        $this->qcValues = $qcValues;
    }

    public function getInternalStandard()
    {
        return (float) $this->internalStandard;
    }

    public function setInternalStandard($internalStandard)
    {
        $this->internalStandard = $internalStandard;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getIsUsed()
    {
        return $this->isUsed;
    }

    public function setIsUsed($isUsed)
    {
        $this->isUsed = $isUsed;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt->format('d.m.Y H:i:s');
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getFkStudy()
    {
        return $this->fkStudy;
    }

    public function setFkStudy(\Alae\Entity\Study $fkStudy)
    {
        $this->fkStudy = $fkStudy;
    }

    public function getFkAnalyte()
    {
        return $this->fkAnalyte;
    }

    public function setFkAnalyte(\Alae\Entity\Analyte $fkAnalyte)
    {
        $this->fkAnalyte = $fkAnalyte;
    }

    public function getFkAnalyteIs()
    {
        return $this->fkAnalyteIs;
    }

    public function setFkAnalyteIs(\Alae\Entity\Analyte $fkAnalyteIs)
    {
        $this->fkAnalyteIs = $fkAnalyteIs;
    }

    public function getFkUnit()
    {
        return $this->fkUnit;
    }

    public function setFkUnit(\Alae\Entity\Unit $fkUnit)
    {
        $this->fkUnit = $fkUnit;
    }

    public function getFkUser()
    {
        return $this->fkUser;
    }

    public function setFkUser(\Alae\Entity\User $fkUser)
    {
        $this->fkUser = $fkUser;
    }

    public function getFkUserApprove()
    {
        return $this->fkUserApprove;
    }

    public function setFkUserApprove(\Alae\Entity\User $fkUser)
    {
        $this->fkUserApprove = $fkUser;
    }

    public function setLdqcValues($ldqcValues)
    {
        $this->ldqcValues = $ldqcValues;
    }

    public function getLdqcValues()
    {
        return $this->ldqcValues;
    }

    public function setHdqcValues($hdqcValues)
    {
        $this->hdqcValues = $hdqcValues;
    }

    public function getHdqcValues()
    {
        return $this->hdqcValues;
    }
}
