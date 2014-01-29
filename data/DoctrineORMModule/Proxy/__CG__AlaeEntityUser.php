<?php

namespace DoctrineORMModule\Proxy\__CG__\Alae\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class User extends \Alae\Entity\User implements \Doctrine\ORM\Proxy\Proxy
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
            return array('__isInitialized__', 'pkUser', 'name', 'username', 'email', 'password', 'activeCode', 'activeFlag', 'fkProfile');
        }

        return array('__isInitialized__', 'pkUser', 'name', 'username', 'email', 'password', 'activeCode', 'activeFlag', 'fkProfile');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (User $proxy) {
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
    public function getPkUser()
    {
        if ($this->__isInitialized__ === false) {
            return  parent::getPkUser();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPkUser', array());

        return parent::getPkUser();
    }

    /**
     * {@inheritDoc}
     */
    public function setPkUser($pkUser)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPkUser', array($pkUser));

        return parent::setPkUser($pkUser);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUsername', array());

        return parent::getUsername();
    }

    /**
     * {@inheritDoc}
     */
    public function setUsername($username)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUsername', array($username));

        return parent::setUsername($username);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', array());

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', array($name));

        return parent::setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEmail', array());

        return parent::getEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEmail', array($email));

        return parent::setEmail($email);
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPassword', array());

        return parent::getPassword();
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($password)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPassword', array($password));

        return parent::setPassword($password);
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getActiveCode', array());

        return parent::getActiveCode();
    }

    /**
     * {@inheritDoc}
     */
    public function setActiveCode($activeCode)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setActiveCode', array($activeCode));

        return parent::setActiveCode($activeCode);
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveFlag()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getActiveFlag', array());

        return parent::getActiveFlag();
    }

    /**
     * {@inheritDoc}
     */
    public function setActiveFlag($activeFlag)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setActiveFlag', array($activeFlag));

        return parent::setActiveFlag($activeFlag);
    }

    /**
     * {@inheritDoc}
     */
    public function getFkProfile()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFkProfile', array());

        return parent::getFkProfile();
    }

    /**
     * {@inheritDoc}
     */
    public function setFkProfile(\Alae\Entity\Profile $fkProfile)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFkProfile', array($fkProfile));

        return parent::setFkProfile($fkProfile);
    }

    /**
     * {@inheritDoc}
     */
    public function isSustancias()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isSustancias', array());

        return parent::isSustancias();
    }

    /**
     * {@inheritDoc}
     */
    public function isLaboratorio()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isLaboratorio', array());

        return parent::isLaboratorio();
    }

    /**
     * {@inheritDoc}
     */
    public function isDirectorEstudio()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isDirectorEstudio', array());

        return parent::isDirectorEstudio();
    }

    /**
     * {@inheritDoc}
     */
    public function isUGC()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isUGC', array());

        return parent::isUGC();
    }

    /**
     * {@inheritDoc}
     */
    public function isAdministrador()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isAdministrador', array());

        return parent::isAdministrador();
    }

    /**
     * {@inheritDoc}
     */
    public function isCron()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isCron', array());

        return parent::isCron();
    }

}
