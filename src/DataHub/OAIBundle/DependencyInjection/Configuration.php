<?php

namespace DataHub\OAIBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('data_hub_oai');

        $rootNode
            ->children()
            ->scalarNode('datahub_oai_repo_name')->defaultValue('Datahub')->end()
            ->scalarNode('datahub_oai_contact_email')->defaultValue('example@example.com')->end()
            ->scalarNode('datahub_oai_pagination_num_records')->defaultValue(25)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
