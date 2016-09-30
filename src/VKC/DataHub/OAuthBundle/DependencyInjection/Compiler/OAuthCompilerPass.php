<?php

namespace VKC\DataHub\OAuthBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Simple compiler pass for overriding OAuth client managers.
 *
 * @author Kalman Olah <kalman@inuitS.eu>
 */
class OAuthCompilerPass implements CompilerPassInterface
{
    /**
     * Process container building.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('fos_oauth_server.client_manager.default');
        $definition->setClass('VKC\DataHub\OAuthBundle\Document\ClientManager');
    }
}
