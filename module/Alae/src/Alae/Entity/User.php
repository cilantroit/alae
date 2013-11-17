<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="alae_user", indexes={@ORM\Index(name="fk_profile", columns={"fk_profile"})})
 * @ORM\Entity
 */
class User
{

    const USER_INACTIVE_FLAG = 0;
    const USER_ACTIVE_FLAG = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_user", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkUser;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=25, nullable=false)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=50, nullable=false)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=50, nullable=false)
     */
    protected $password;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active_flag", type="boolean", nullable=false)
     */
    protected $activeFlag = '0';

    /**
     * @var \Alae\Entity\Profile
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_profile", referencedColumnName="pk_profile")
     * })
     */
    protected $fkProfile;

    public function getPkUser()
    {
	return $this->pkUser;
    }

    public function setPkUser($pkUser)
    {
	$this->pkUser = $pkUser;
    }

    public function getUsername()
    {
	return $this->username;
    }

    public function setUsername($username)
    {
	$this->username = $username;
    }

    public function getEmail()
    {
	return $this->email;
    }

    public function setEmail($email)
    {
	$this->email = $email;
    }

    public function getPassword()
    {
	return $this->password;
    }

    public function setPassword($password)
    {
	$this->password = md5(sha1($password));
    }

    public function getActiveFlag()
    {
	return $this->activeFlag;
    }

    public function setActiveFlag($activeFlag)
    {
	$this->activeFlag = $activeFlag;
    }

    public function getFkProfile()
    {
	return $this->fkProfile;
    }

    public function setFkProfile(\Alae\Entity\Profile $fkProfile)
    {
	$this->fkProfile = $fkProfile;
    }

}

