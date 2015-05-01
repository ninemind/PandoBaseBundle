<?php
namespace BlackBoxCode\Pando\Bundle\BaseBundle\Command;

use BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityGenerator;
use BlackBoxCode\Pando\Bundle\BaseBundle\Tools\EntityMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEntitiesCommand extends ContainerAwareCommand
{
    /** @var string */
    private $outputDir = 'src';

    public function configure()
    {
        $this
            ->setName('pando:generate:entities')
            ->setDescription('Generates pando entities from traits and interfaces in Bundle/Model directories.')
            ->addArgument('outputDir', InputArgument::OPTIONAL, 'Where do you want to save the generated entities?', $this->outputDir)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $outputDir = $input->getArgument('outputDir');
        $entityGenerator = $this->getEntityGenerator();

        $classMap = $this->generateClassMap();
        foreach ($this->parseClassMap($classMap) as $entityName => $dependencies) {
            try {
                $meta = $entityGenerator->createEntityMetadata($entityName, $dependencies);
                $entityGenerator->writeEntityToFile($meta, $outputDir);
                $output->writeln('Generated entity: ' . $entityName);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        $output->writeln('Finished!');
    }

    /**
     * @return EntityGenerator
     */
    public function getEntityGenerator()
    {
        return $this->getContainer()->get('pando_base_bundle.entity_generator');
    }

    /**
     * @return array
     */
    public function generateClassMap()
    {
        return array_keys(array_merge(
            ClassMapGenerator::createMap('vendor/blackboxcode'),
            ClassMapGenerator::createMap('src')
        ));
    }

    /**
     * @param array
     * @return array
     */
    private function parseClassMap($classMap)
    {
        $map = array();
        foreach ($classMap as $ns) {
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
            if (array_key_exists('Interface', $dependencies)) {
                foreach($dependencies['Interface'] as $interface) {
                    $r = new \ReflectionClass($interface);
                    $removals = array_merge($removals, $r->getInterfaceNames());
                }
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

