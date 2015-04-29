<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Command;

use BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityGenerator;
use BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityModel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEntitiesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('pando:generate:entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $namespace = $container->getParameter('pando_entity.namespace');

        $outputDir = 'src';

        $entityGenerator = new EntityGenerator();
        foreach ($this->generateEntityMap() as $entityName => $classTypes) {
            $model = new EntityModel();
            $model
                ->setClassName($entityName)
                ->setNamespace($namespace)
            ;

            foreach ($classTypes as $classType => $classes) {
                $method = 'add' . $classType;
                foreach ($classes as $class) {
                    $model->$method('\\' . $class);
                }
            }

            try {
                $entityGenerator->writeEntityToFile($model, $outputDir);
                $output->writeln('Generated entity: ' . $entityName);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        $output->writeln('Finished!');
    }

    private function generateEntityMap()
    {
        $map = array();
        $interfacesToRemove = array();

        $classMap = array_merge(
            ClassMapGenerator::createMap('vendor/blackboxcode'),
            ClassMapGenerator::createMap('src')
        );
        foreach ($classMap as $ns => $file) {
            $parts = explode('\\', $ns);

            $className = array_pop($parts);
            if (array_pop($parts) == 'Model') {
                if (substr($className, -5) == 'Trait') {
                    $map[substr($className, 0, -5)]['Trait'][] = $ns;
                } else if (substr($className, -9) == 'Interface') {

                    $class = new \ReflectionClass($ns);
                    foreach ($class->getInterfaceNames() as $interface) {
                        $interfaceParts = explode('\\', $interface);
                        if (array_pop($interfaceParts) === $className) {
                            $interfacesToRemove[$interface] = null;
                        }
                    }

                    $map[substr($className, 0, -9)]['Interface'][] = $ns;
                }
            }
        }

        foreach ($map as $entity => $dependencies) {
            foreach (array_keys($interfacesToRemove) as $remove) {
                if ($key = array_search($remove, $dependencies['Interface'])) {
                    unset($map[$entity]['Interface'][$key]);
                }
            }
        }

        ksort($map);
        return $map;
    }
}

