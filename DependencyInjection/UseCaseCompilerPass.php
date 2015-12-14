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
        $services = $container->getDefinitions();
        foreach ($services as $id => $serviceDefinition) {
            $serviceClass = $serviceDefinition->getClass();
            if (!class_exists($serviceClass)) {
                continue;
            }

            $reflection = new \ReflectionClass($serviceClass);
            try {
                $annotations = $this->annotationReader->getClassAnnotations($reflection);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception(sprintf('Could not load annotations for class %s: %s', $serviceClass, $e->getMessage()));
            }

            foreach ($annotations as $annotation) {
                if ($annotation instanceof UseCaseAnnotation) {
                    $this->registerUseCase($id, $annotation, $containerDefinition);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $definition
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
     * @param Definition       $definition
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

    /**
     * @param string            $serviceId
     * @param UseCaseAnnotation $annotation
     * @param Definition        $containerDefinition
     */
    private function registerUseCase($serviceId, $annotation, $containerDefinition)
    {
        $containerDefinition->addMethodCall('set', array($annotation->getName(), new Reference($serviceId)));

        if ($annotation->getInputType()) {
            $containerDefinition->addMethodCall(
                'assignInputConverter',
                array(
                    $annotation->getName(),
                    $annotation->getInputType(),
                    $annotation->getInputOptions()
                )
            );
        }

        if ($annotation->getOutputType()) {
            $containerDefinition->addMethodCall(
                'assignResponseProcessor',
                array(
                    $annotation->getName(),
                    $annotation->getOutputType(),
                    $annotation->getOutputOptions()
                )
            );
        }
    }
}
