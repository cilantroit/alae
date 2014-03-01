<?php

namespace Alae\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Error
 *
 * @ORM\Table(name="alae_error", indexes={@ORM\Index(name="fk_sample_batch", columns={"fk_sample_batch"}), @ORM\Index(name="fk_parameter", columns={"fk_parameter"})})
 * @ORM\Entity
 */
class Error
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk_error", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkError;

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
     * @var \Alae\Entity\SampleBatch
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\SampleBatch")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_sample_batch", referencedColumnName="pk_sample_batch")
     * })
     */
    protected $fkSampleBatch;

    public function getPkError()
    {
        return $this->pkError;
    }

    public function setPkError($pkError)
    {
        $this->pkError = $pkError;
    }

    public function getFkParameter()
    {
        return $this->fkParameter;
    }

    public function setFkParameter(\Alae\Entity\Parameter $fkParameter)
    {
        $this->fkParameter = $fkParameter;
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