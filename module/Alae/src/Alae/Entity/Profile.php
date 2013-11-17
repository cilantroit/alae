<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 *
 * @ORM\Table(name="alae_profile")
 * @ORM\Entity
 */
class Profile
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_profile", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkProfile;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=25, nullable=false)
     */
    protected $name;

    public function getPkProfile()
    {
        return $this->pkProfile;
    }

    public function setPkProfile($pkProfile)
    {
        $this->pkProfile = $pkProfile;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

}
