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
     * @ORM\Column(name="qc_values", type="string", length=100, nullable=true)
     */
    protected $qcValues;

    /**
     * @var string
     *
     * @ORM\Column(name="internal_standard", type="decimal", precision=19, scale=4, nullable=false)
     */
    protected $internalStandard = '0.0000';

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_flag", type="boolean", nullable=false)
     */
    protected $isFlag = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="imported_flag", type="boolean", nullable=false)
     */
    protected $importedFlag = '0';

    /**
     * @var \Alae\Entity\Study
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Alae\Entity\Study")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_study", referencedColumnName="pk_study")
     * })
     */
    protected $fkStudy;

    /**
     * @var \Alae\Entity\Analyte
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Alae\Entity\Analyte")
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
        return $this->internalStandard;
    }

    public function setInternalStandard($internalStandard)
    {
        $this->internalStandard = $internalStandard;
    }

    public function getIsFlag()
    {
        return $this->isFlag;
    }

    public function setIsFlag($isFlag)
    {
        $this->isFlag = $isFlag;
    }

    public function getImportedFlag()
    {
        return $this->importedFlag;
    }

    public function setImportedFlag($importedFlag)
    {
        $this->importedFlag = $importedFlag;
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

}
