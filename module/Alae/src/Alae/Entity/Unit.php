<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Unit
 *
 * @ORM\Table(name="alae_unit")
 * @ORM\Entity
 */
class Unit
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_unit", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=25, nullable=true)
     */
    protected $name;

    public function getPkUnit()
    {
        return $this->pkUnit;
    }

    public function setPkUnit($pkUnit)
    {
        $this->pkUnit = $pkUnit;
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
