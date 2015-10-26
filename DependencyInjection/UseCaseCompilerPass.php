<?php

namespace Lamudi\UseCaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class UseCaseCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('lamudi_use_case.container')) {
            return;
        }

        $definition = $container->findDefinition('lamudi_use_case.container');
        $services = $container->findTaggedServiceIds('use_case');

        foreach ($services as $id => $tags) {
            $definition->addMethodCall('set', array($id, new Reference($id)));
        }
    }
}