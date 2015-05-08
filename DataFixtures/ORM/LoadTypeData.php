<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\DataFixtures;

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

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $entityParams = $this->container->getParameter('pando_entity');
        $classMap = ClassMapGenerator::createMap($entityParams['base_dir'] . DIRECTORY_SEPARATOR . $entityParams['namespace']);

        foreach ($classMap as $ns => $file) {
            if (substr($ns, -4) === 'Type') {
                $r = new \ReflectionClass($ns);
                foreach ($r->getConstants() as $constant) {
                    $type = new $ns;
                    $type->setName($constant);
                    $manager->persist($type);
                }
            }
        }

        $manager->flush();
    }
}
