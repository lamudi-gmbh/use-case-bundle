<?php

namespace Lamudi\UseCaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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

        $this->addUseCasesToContainer($container, $definition);
        $this->addInputConvertersToContainer($container, $definition);
        $this->addResponseProcessorsToContainer($container, $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $definition
     * @return array
     */
    private function addUseCasesToContainer(ContainerBuilder $container, $definition)
    {
        $services = $container->findTaggedServiceIds('use_case');
        foreach ($services as $id => $tags) {
            $definition->addMethodCall('set', array($id, new Reference($id)));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $definition
     */
    private function addInputConvertersToContainer(ContainerBuilder $container, $definition)
    {
        $inputConverters = $container->findTaggedServiceIds('use_case_input_converter');
        foreach ($inputConverters as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('setInputConverter', array($attributes['alias'], new Reference($id)));
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $definition
     */
    private function addResponseProcessorsToContainer(ContainerBuilder $container, $definition)
    {
        $inputConverters = $container->findTaggedServiceIds('use_case_response_processor');
        foreach ($inputConverters as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('setResponseProcessor', array($attributes['alias'], new Reference($id)));
            }
        }
    }
}