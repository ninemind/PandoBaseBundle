<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Factory;

class EntityFactory
{
    /**
     * @var array
     */
    private $entityParams;

    /**
     * @param array $entityParams
     */
    public function __construct(array $entityParams)
    {
        $this->entityParams = $entityParams;
    }

    /**
     * Returns a new instance of the Entity found with the given name
     *
     * @param string $entityName
     * @return mixed
     */
    public function create($entityName)
    {
        $fullNamespace = '\\' . $this->entityParams['namespace'] . '\\' . $entityName;
        return new $fullNamespace();
    }
}
