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

        $kernel = $this->getContainer()->get('kernel');
        $output->writeln(sprintf('Generating entities for the <info>%s</info> environment with the %s namespace', $kernel->getEnvironment(), $namespace));

        $outputDir = $container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'blackboxcode' . DIRECTORY_SEPARATOR . 'pando';

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
                    $model->$method($class);
                }
            }

            try {
                $entityGenerator->writeEntityToFile($model, $outputDir);
                $output->writeln('Generated entity: ' . $entityName);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        $output->writeln(sprintf('Finished! Entities can be found in: %s', $outputDir));
    }

    private function generateEntityMap()
    {
        $map = array();

        $classMap = ClassMapGenerator::createMap('vendor/blackboxcode');
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
        return $map;
    }
}

