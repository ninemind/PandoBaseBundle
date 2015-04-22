<?php

namespace BlackBoxCode\Pando\Bundle\BaseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('black_box_code_pando_bundle_base');

        $rootNode
            ->children()
                ->variableNode('entity_namespace')
                    ->defaultValue($this->entityNamespace)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
