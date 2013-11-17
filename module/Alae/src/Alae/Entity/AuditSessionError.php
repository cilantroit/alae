<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuditSessionError
 *
 * @ORM\Table(name="alae_audit_session_error")
 * @ORM\Entity
 */
class AuditSessionError
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
    protected $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=25, nullable=false)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=500, nullable=false)
     */
    protected $message;

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

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

}
