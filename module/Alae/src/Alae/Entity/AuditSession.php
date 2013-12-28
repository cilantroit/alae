<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuditSession
 *
 * @ORM\Table(name="alae_audit_session", indexes={@ORM\Index(name="fk_user", columns={"fk_user"})})
 * @ORM\Entity
 */
class AuditSession
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_audit_session", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkAuditSession;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

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
	$this->createdAt = new \DateTime('now');
    }

    public function getPkAuditSession()
    {
	return $this->pkAuditSession;
    }

    public function setPkAuditSession($pkAuditSession)
    {
	$this->pkAuditSession = $pkAuditSession;
    }

    public function getCreatedAt()
    {
	return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
	$this->createdAt = $createdAt;
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
