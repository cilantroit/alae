<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuditTransaction
 *
 * @ORM\Table(name="alae_audit_transaction", indexes={@ORM\Index(name="fk_user", columns={"fk_user"})})
 * @ORM\Entity
 */
class AuditTransaction
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
     * @var string
     *
     * @ORM\Column(name="section", type="string", length=50, nullable=false)
     */
    protected $section;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=250, nullable=false)
     */
    protected $description;

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

    public function __prepare($_method = false, $section = false, $description = false)
    {
        switch ($_method)
        {
            case "Alae\Controller\IndexController::indexAction":
                $section     = "Sección de prueba";
                $description = "Mi primera descripcion";
                break;
        }

        if ($section)
            $this->setSection($section);

        if ($description)
            $this->setDescription($description);
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
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setSection($section)
    {
        $this->section = $section;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
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
