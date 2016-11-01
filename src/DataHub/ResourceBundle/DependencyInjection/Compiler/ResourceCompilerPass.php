<?php

namespace DataHub\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Simple compiler pass for registering resource converters and the like.
 *
 * @author Kalman Olah <kalman@inuitS.eu>
 */
class ResourceCompilerPass implements CompilerPassInterface
{
    const DATA_CONVERTER_TAG = 'datahub.resource.data.converter';
    const DATA_CONVERTER_CONTAINER = 'datahub.resource.data_converters';
    const DATA_CONVERTER_CONTAINER_CALL = 'registerConverter';

    /**
     * Process container building.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $converterDefinitions = $container->findTaggedServiceIds(static::DATA_CONVERTER_TAG);
        $converterContainer = $container->getDefinition(static::DATA_CONVERTER_CONTAINER);

        foreach ($converterDefinitions as $id => $tags) {
            $converterDefinition = $container->getDefinition($id);

            if ($converterDefinition->isAbstract()) {
                continue;
            }

            $converterContainer->addMethodCall(static::DATA_CONVERTER_CONTAINER_CALL, [new Reference($id)]);
        }
    }
}
