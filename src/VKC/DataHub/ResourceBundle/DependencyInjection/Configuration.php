<?php

namespace VKC\DataHub\ResourceBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vkc_data_hub_resource');

        $rootNode
            ->children()
                ->scalarNode('work_data_collection')->defaultValue('WorkData')->end()
                ->scalarNode('catmandu_cli_path')->defaultValue('/usr/bin/catmandu')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
