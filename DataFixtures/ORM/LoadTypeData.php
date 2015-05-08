<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTypeData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getEntityList()
    {
        $entityParams = $this->container->getParameter('pando_entity');
        return array_keys(ClassMapGenerator::createMap($entityParams['base_dir'] . DIRECTORY_SEPARATOR . $entityParams['namespace']));
    }

    public function createTypeEntity($namespace)
    {
        return new $namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getEntityList() as $ns) {
            if (substr($ns, -4) === 'Type') {
                $r = new \ReflectionClass($ns);
                foreach ($r->getConstants() as $constant) {
                    $type = $this->createTypeEntity($ns);
                    $type->setName($constant);
                    $manager->persist($type);
                }
            }
        }

        $manager->flush();
    }
}
