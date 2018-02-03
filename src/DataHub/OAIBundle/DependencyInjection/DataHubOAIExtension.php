<?php

namespace DataHub\OAIBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DataHubOAIExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        foreach (array(
            'datahub_oai_repo_name',
            'datahub_oai_contact_email',
            'datahub_oai_pagination_num_records'
        ) as $cfgKey) {
            $container->setParameter("data_hub_oai.{$cfgKey}", $mergedConfig[$cfgKey]);
        }
    }
}
