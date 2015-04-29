<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Factory;

class EntityFactory
{
    /**
     * @var string
     */
    private $entityNamespace;

    /**
     * @param string $entityNamespace
     */
    public function __construct($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;
    }

    /**
     * Returns a new instance of the Entity found with the given name
     *
     * @param string $entityName
     * @return mixed
     */
    public function create($entityName)
    {
        $fullNamespace = '\\' . $this->entityNamespace . '\\' . $entityName;
        return new $fullNamespace();
    }
}
