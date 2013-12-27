<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analyte
 *
 * @ORM\Table(name="alae_analyte", indexes={@ORM\Index(name="fk_user", columns={"fk_user"})})
 * @ORM\Entity
 */
class Analyte
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_analyte", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkAnalyte;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=30, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="shortening", type="string", length=15, nullable=true)
     */
    protected $shortening;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    protected $status = '1';

    /**
     * @var \Alae\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_user", referencedColumnName="pk_user")
     * })
     */
    protected $fkUser;

    public function __construct()
    {
        $this->updatedAt = new \DateTime('now');
    }

    public function getPkAnalyte()
    {
        return $this->pkAnalyte;
    }

    public function setPkAnalyte($pkAnalyte)
    {
        $this->pkAnalyte = $pkAnalyte;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getShortening()
    {
        return $this->shortening;
    }

    public function setShortening($shortening)
    {
        $this->shortening = $shortening;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getFkUser()
    {
        return $this->fkUser;
    }

    public function setFkUser(\Alae\Entity\User $fkUser)
    {
        $this->fkUser = $fkUser;
    }

}
