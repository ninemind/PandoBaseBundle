<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Command;

use BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityGenerator;
use BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEntitiesCommand extends ContainerAwareCommand
{
    /** @var string */
    protected static $outputDir = 'src';

    protected function configure()
    {
        $this->setName('pando:generate:entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityGenerator = new EntityGenerator($this->getContainer());
        foreach ($this->generateEntityMap() as $entityName => $classMap) {
            try {
                $entityGenerator->writeEntityToFile($this->createEntityMetadata($entityName, $classMap), self::$outputDir);
                $output->writeln('Generated entity: ' . $entityName);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        $output->writeln('Finished!');
    }

    /**
     * @param string $entityName
     * @param array $classMap
     * @return EntityMetadata
     */
    protected function createEntityMetadata($entityName, $classMap)
    {
        $meta = new EntityMetadata();
        $meta->setClassName($entityName);

        foreach ($classMap as $classType => $classes) {
            $method = 'add' . $classType;
            foreach ($classes as $class) {
                $meta->$method('\\' . $class);
            }
        }

        return $meta;
    }

    /**
     * @return array
     */
    private function generateEntityMap()
    {
        $classMap = array_merge(
            ClassMapGenerator::createMap('vendor/blackboxcode'),
            ClassMapGenerator::createMap('src')
        );

        $map = array();
        foreach ($classMap as $ns => $file) {
            $parts = explode('\\', $ns);

            $className = array_pop($parts);
            if (array_pop($parts) == 'Model') {
                if (substr($className, -5) == 'Trait') {
                    $map[substr($className, 0, -5)]['Trait'][] = $ns;
                } else if (substr($className, -9) == 'Interface') {
                    $map[substr($className, 0, -9)]['Interface'][] = $ns;
                }
            }
        }

        ksort($map);
        return $this->removeExtendedInterfaces($map);
    }

    /**
     * @param array $map
     * @return array
     */
    private function removeExtendedInterfaces($map)
    {
        $removals = array();

        foreach($map as $entityName => $dependencies) {
            foreach($dependencies['Interface'] as $interface) {
                $r = new \ReflectionClass($interface);
                $removals = array_merge($removals, $r->getInterfaceNames());
            }
        }

        foreach ($map as $entity => $dependencies) {
            foreach ($removals as $remove) {
                if ($key = array_search($remove, $dependencies['Interface'])) {
                    unset($map[$entity]['Interface'][$key]);
                }
            }
        }

        return $map;
    }
}

