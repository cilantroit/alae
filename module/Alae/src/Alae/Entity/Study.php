<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Study
 *
 * @ORM\Table(name="alae_study", indexes={@ORM\Index(name="fk_user", columns={"fk_user"})})
 * @ORM\Entity
 */
class Study
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_study", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkStudy;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20, nullable=true)
     */
    protected $code;

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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="observation", type="text", nullable=true)
     */
    protected $observation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="close_flag", type="boolean", nullable=false)
     */
    protected $closeFlag;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    protected $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="approve", type="boolean", nullable=false)
     */
    protected $approve;

    /**
     * @var boolean
     *
     * @ORM\Column(name="duplicate", type="boolean", nullable=false)
     */
    protected $duplicate;

    /**
     * @var integer
     *
     * @ORM\Column(name="fk_dilution_tree", type="bigint", nullable=false)
     */
    protected $fkDilutionTree = '1';

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

    /**
     * @var \Alae\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_user_close", referencedColumnName="pk_user")
     * })
     */
    protected $fkUserClose;

    public function __construct()
    {
        $this->updatedAt = new \DateTime('now');
    }

    public function getPkStudy()
    {
        return $this->pkStudy;
    }

    public function setPkStudy($pkStudy)
    {
        $this->pkStudy = $pkStudy;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCreatedAt()
    {
        return $this->createdAt->format('Y-m-d');
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = new \DateTime($createdAt);//$createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt->format('d.m.Y H:i:s');
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getObservation()
    {
        return $this->observation;
    }

    public function setObservation($observation)
    {
        $this->observation = $observation;
    }

    public function getCloseFlag()
    {
        return $this->closeFlag;
    }

    public function setCloseFlag($closeFlag)
    {
        $this->closeFlag = $closeFlag;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getApprove()
    {
        return $this->approve;
    }

    public function setApprove($approve)
    {
        $this->approve = $approve;
    }

    public function getDuplicate()
    {
        return $this->duplicate;
    }

    public function setDuplicate($duplicate)
    {
        $this->duplicate = $duplicate;
    }

    public function getFkDilutionTree()
    {
        return $this->fkDilutionTree;
    }

    public function setFkDilutionTree($fkDilutionTree)
    {
        $this->fkDilutionTree = $fkDilutionTree;
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

    public function getFkUserClose()
    {
        return $this->fkUserClose;
    }

    public function setFkUserClose(\Alae\Entity\User $fkUser)
    {
        $this->fkUserClose = $fkUser;
    }

}
