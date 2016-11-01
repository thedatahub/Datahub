<?php

namespace DataHub\ResourceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use DataHub\ResourceBundle\DependencyInjection\Compiler\ResourceCompilerPass;

class DataHubResourceBundle extends Bundle
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
