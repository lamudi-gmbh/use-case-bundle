<?php

namespace Lamudi\UseCaseBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class UseCaseCompilerPass implements CompilerPassInterface
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @param AnnotationReader $annotationReader
     */
    public function __construct(AnnotationReader $annotationReader = null)
    {
        $this->annotationReader = $annotationReader ?: new AnnotationReader();
    }

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

        $this->addInputConvertersToContainer($container, $definition);
        $this->addResponseProcessorsToContainer($container, $definition);
        $this->addUseCasesToContainer($container, $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $containerDefinition
     * @return array
     */
    private function addUseCasesToContainer(ContainerBuilder $container, $containerDefinition)
    {
        $services = $container->getServiceIds();
        foreach ($services as $id) {
            $useCaseDefinition = $container->getDefinition($id);
            $useCaseClass = $useCaseDefinition->getClass();
            $reflection = new \ReflectionClass($useCaseClass);
            $annotations = $this->annotationReader->getClassAnnotations($reflection);

            foreach ($annotations as $annotation) {
                if ($annotation instanceof UseCaseAnnotation) {
                    $containerDefinition->addMethodCall('set', array($annotation->getAlias(), new Reference($id)));

                    if ($annotation->getInputType()) {
                        $containerDefinition->addMethodCall('assignInputConverter', array(
                            $annotation->getAlias(), $annotation->getInputType(), $annotation->getInputOptions()
                        ));
                    }

                    if ($annotation->getOutputType()) {
                        $containerDefinition->addMethodCall('assignResponseProcessor', array(
                            $annotation->getAlias(), $annotation->getOutputType(), $annotation->getOutputOptions()
                        ));
                    }
                }
            }
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
