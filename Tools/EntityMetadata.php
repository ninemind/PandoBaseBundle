<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Tools;

class EntityMetadata
{
    /** @var string */
    protected $className;

    /** @var array */
    protected $interfaces = array();

    /** @var array */
    protected $traits = array();

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * @param string $interface
     * @return $this
     */
    public function addInterface($interface)
    {
        $this->interfaces[] = $interface;

        return $this;
    }

    /**
     * @return array
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @param string $trait
     * @return $this
     */
    public function addTrait($trait)
    {
        $this->traits[] = $trait;

        return $this;
    }
}
