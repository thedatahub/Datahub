<?php

namespace VKC\DataHub\OAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use VKC\DataHub\OAuthBundle\DependencyInjection\Compiler\OAuthCompilerPass;

class VKCDataHubOAuthBundle extends Bundle
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
