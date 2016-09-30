<?php

namespace VKC\DataHub\ResourceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use VKC\DataHub\ResourceBundle\DependencyInjection\Compiler\ResourceCompilerPass;

class VKCDataHubResourceBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ResourceCompilerPass());
    }
}
