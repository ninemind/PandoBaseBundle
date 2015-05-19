<?php
namespace BlackBoxCode\Pando\BaseBundle\Factory;

class EntityFactory
{
    /** @var string */
    private $namespace;

    /**
     * @param string $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Returns a new instance of the Entity found with the given name
     *
     * @param string $entityName
     * @return mixed
     */
    public function create($entityName)
    {
        $fullNamespace = '\\' . $this->namespace . '\\' . $entityName;
        return new $fullNamespace();
    }
}
