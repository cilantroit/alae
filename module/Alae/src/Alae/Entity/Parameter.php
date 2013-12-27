<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parameter
 *
 * @ORM\Table(name="alae_parameter")
 * @ORM\Entity
 */
class Parameter
{

    /**
     * @var integer
     *
     * @ORM\Column(name="pk_parameter", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $pkParameter;

    /**
     * @var string
     *
     * @ORM\Column(name="rule", type="string", length=10, nullable=true)
     */
    protected $rule;

    /**
     * @var string
     *
     * @ORM\Column(name="verification", type="text", nullable=true)
     */
    protected $verification;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_value", type="integer", nullable=false)
     */
    protected $minValue = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="max_value", type="integer", nullable=false)
     */
    protected $maxValue = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="code_error", type="string", length=10, nullable=true)
     */
    protected $codeError;

    /**
     * @var string
     *
     * @ORM\Column(name="message_error", type="text", nullable=true)
     */
    protected $messageError;

    /**
     * @var string
     *
     * @ORM\Column(name="type_param", type="boolean", nullable=false)
     */
    protected $typeParam;

    /**
     * @var \Alae\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Alae\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_user", referencedColumnName="pk_user")
     * })
     */
    protected $fkUser;

    public function getPkParameter()
    {
        return $this->pkParameter;
    }

    public function setPkParameter($pkParameter)
    {
        $this->pkParameter = $pkParameter;
    }

    public function getRule()
    {
        return $this->rule;
    }

    public function setRule($rule)
    {
        $this->rule = $rule;
    }

    public function getVerification()
    {
        return $this->verification;
    }

    public function setVerification($verification)
    {
        $this->verification = $verification;
    }

    public function getMinValue()
    {
        return $this->minValue;
    }

    public function setMinValue($minValue)
    {
        $this->minValue = $minValue;
    }

    public function getMaxValue()
    {
        return $this->maxValue;
    }

    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;
    }

    public function getCodeError()
    {
        return $this->codeError;
    }

    public function setCodeError($codeError)
    {
        $this->codeError = $codeError;
    }

    public function getMessageError()
    {
        return $this->messageError;
    }

    public function setMessageError($messageError)
    {
        $this->messageError = $messageError;
    }

    public function getTypeParam()
    {
        return $this->typeParam;
    }

    public function setTypeParam($typeParam)
    {
        $this->typeParam = $typeParam;
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
