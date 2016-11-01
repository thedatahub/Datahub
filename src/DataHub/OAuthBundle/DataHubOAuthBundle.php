<?php

namespace DataHub\OAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use DataHub\OAuthBundle\DependencyInjection\Compiler\OAuthCompilerPass;

class DataHubOAuthBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OAuthCompilerPass());
    }
}
