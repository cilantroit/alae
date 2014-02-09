<?php

namespace DoctrineORMModule\Proxy\__CG__\Alae\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Study extends \Alae\Entity\Study implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', 'pkStudy', 'code', 'createdAt', 'updatedAt', 'description', 'observation', 'closeFlag', 'status', 'approve', 'duplicate', 'fkDilutionTree', 'fkUser', 'fkUserApprove', 'fkUserClose');
        }

        return array('__isInitialized__', 'pkStudy', 'code', 'createdAt', 'updatedAt', 'description', 'observation', 'closeFlag', 'status', 'approve', 'duplicate', 'fkDilutionTree', 'fkUser', 'fkUserApprove', 'fkUserClose');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Study $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getPkStudy()
    {
        if ($this->__isInitialized__ === false) {
            return  parent::getPkStudy();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPkStudy', array());

        return parent::getPkStudy();
    }

    /**
     * {@inheritDoc}
     */
    public function setPkStudy($pkStudy)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPkStudy', array($pkStudy));

        return parent::setPkStudy($pkStudy);
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCode', array());

        return parent::getCode();
    }

    /**
     * {@inheritDoc}
     */
    public function setCode($code)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCode', array($code));

        return parent::setCode($code);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCreatedAt', array());

        return parent::getCreatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCreatedAt', array($createdAt));

        return parent::setCreatedAt($createdAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedAt', array());

        return parent::getUpdatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdatedAt', array($updatedAt));

        return parent::setUpdatedAt($updatedAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', array());

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDescription', array($description));

        return parent::setDescription($description);
    }

    /**
     * {@inheritDoc}
     */
    public function getObservation()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getObservation', array());

        return parent::getObservation();
    }

    /**
     * {@inheritDoc}
     */
    public function setObservation($observation)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setObservation', array($observation));

        return parent::setObservation($observation);
    }

    /**
     * {@inheritDoc}
     */
    public function getCloseFlag()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCloseFlag', array());

        return parent::getCloseFlag();
    }

    /**
     * {@inheritDoc}
     */
    public function setCloseFlag($closeFlag)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCloseFlag', array($closeFlag));

        return parent::setCloseFlag($closeFlag);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStatus', array());

        return parent::getStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setStatus', array($status));

        return parent::setStatus($status);
    }

    /**
     * {@inheritDoc}
     */
    public function getApprove()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getApprove', array());

        return parent::getApprove();
    }

    /**
     * {@inheritDoc}
     */
    public function setApprove($approve)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setApprove', array($approve));

        return parent::setApprove($approve);
    }

    /**
     * {@inheritDoc}
     */
    public function getDuplicate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDuplicate', array());

        return parent::getDuplicate();
    }

    /**
     * {@inheritDoc}
     */
    public function setDuplicate($duplicate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDuplicate', array($duplicate));

        return parent::setDuplicate($duplicate);
    }

    /**
     * {@inheritDoc}
     */
    public function getFkDilutionTree()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFkDilutionTree', array());

        return parent::getFkDilutionTree();
    }

    /**
     * {@inheritDoc}
     */
    public function setFkDilutionTree($fkDilutionTree)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFkDilutionTree', array($fkDilutionTree));

        return parent::setFkDilutionTree($fkDilutionTree);
    }

    /**
     * {@inheritDoc}
     */
    public function getFkUser()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFkUser', array());

        return parent::getFkUser();
    }

    /**
     * {@inheritDoc}
     */
    public function setFkUser(\Alae\Entity\User $fkUser)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFkUser', array($fkUser));

        return parent::setFkUser($fkUser);
    }

    /**
     * {@inheritDoc}
     */
    public function getFkUserApprove()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFkUserApprove', array());

        return parent::getFkUserApprove();
    }

    /**
     * {@inheritDoc}
     */
    public function setFkUserApprove(\Alae\Entity\User $fkUser)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFkUserApprove', array($fkUser));

        return parent::setFkUserApprove($fkUser);
    }

    /**
     * {@inheritDoc}
     */
    public function getFkUserClose()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFkUserClose', array());

        return parent::getFkUserClose();
    }

    /**
     * {@inheritDoc}
     */
    public function setFkUserClose(\Alae\Entity\User $fkUser)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFkUserClose', array($fkUser));

        return parent::setFkUserClose($fkUser);
    }

}
