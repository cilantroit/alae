<?php

namespace Alae\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ErrorDoublecheck
 *
 * @ORM\Table(name="alae_error_doublecheck", indexes={@ORM\Index(name="fk_sample_batch_doublecheck", columns={"fk_sample_batch_doublecheck"}), @ORM\Index(name="fk_parameter", columns={"fk_parameter"})})
 * @ORM\Entity
 */
class ErrorDoublecheck
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk_error_doublecheck", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkErrorDoublecheck;

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
     * @var \Alae\Entity\SampleBatchDoublecheck
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\SampleBatchDoublecheck")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_sample_batch_doublecheck", referencedColumnName="pk_sample_batch_doublecheck")
     * })
     */
    protected $fkSampleBatchDoublecheck;
    
    /**
     * @var \Alae\Entity\SampleBatch
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\SampleBatch")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_sample_batch", referencedColumnName="pk_sample_batch")
     * })
     */
    protected $fkSampleBatchDoublecheck;

    public function getPkErrorDoubleCheck()
    {
        return $this->pkErrorDoublecheck;
    }

    public function setPkErrorDoubleCheck($pkErrorDoublecheck)
    {
        $this->pkErrorDoublecheck = $pkErrorDoublecheck;
    }

    public function getFkParameter()
    {
        return $this->fkParameter;
    }

    public function setFkParameter(\Alae\Entity\Parameter $fkParameter)
    {
        $this->fkParameter = $fkParameter;
    }

    public function getFkSampleBatchDoubleCheck()
    {
        return $this->fkSampleBatchDoublecheck;
    }

    public function setFkSampleBatchDoubleCheck(\Alae\Entity\SampleBatchDoublecheck $fkSampleBatchDoubleCheck)
    {
        $this->fkSampleBatchDoublecheck = $fkSampleBatchDoubleCheck;
    }
    
    public function getFkSampleBatch()
    {
        return $this->fkSampleBatch;
    }

    public function setFkSampleBatch(\Alae\Entity\SampleBatch $fkSampleBatch)
    {
        $this->fkSampleBatch = $fkSampleBatch;
    }

}