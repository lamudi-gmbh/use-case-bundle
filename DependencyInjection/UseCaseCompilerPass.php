<?php

namespace Lamudi\UseCaseBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
use Lamudi\UseCaseBundle\Request\RequestResolver;
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
     * @var RequestResolver
     */
    private $requestResolver;

    /**
     * @param AnnotationReader $annotationReader
     * @param RequestResolver  $requestResolver
     */
    public function __construct(AnnotationReader $annotationReader = null, RequestResolver $requestResolver = null)
    {
        $this->annotationReader = $annotationReader ?: new AnnotationReader();
        $this->requestResolver = $requestResolver ?: new RequestResolver();
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
        if (!$container->has('lamudi_use_case.executor')) {
            return;
        }

        $executorDefinition = $container->findDefinition('lamudi_use_case.executor');
        $inputProcessorContainerDefinition = $container->findDefinition('lamudi_use_case.container.input_processor');
        $responseProcessorContainerDefinition = $container->findDefinition('lamudi_use_case.container.response_processor');

        $this->addInputProcessorsToContainer($container, $inputProcessorContainerDefinition);
        $this->addResponseProcessorsToContainer($container, $responseProcessorContainerDefinition);
        $this->addUseCasesToContainer($container, $executorDefinition);
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $executorDefinition
     * @return array
     */
    private function addUseCasesToContainer(ContainerBuilder $container, $executorDefinition)
    {
        $useCaseContainerDefinition = $container->findDefinition('lamudi_use_case.container.use_case');
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
                    $this->registerUseCase($id, $serviceClass, $annotation, $executorDefinition, $useCaseContainerDefinition);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $definition
     */
    private function addInputProcessorsToContainer(ContainerBuilder $container, $definition)
    {
        $inputProcessors = $container->findTaggedServiceIds('use_case_input_processor');
        foreach ($inputProcessors as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('set', [$attributes['alias'], new Reference($id)]);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $definition
     */
    private function addResponseProcessorsToContainer(ContainerBuilder $container, $definition)
    {
        $responseProcessors = $container->findTaggedServiceIds('use_case_response_processor');
        foreach ($responseProcessors as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('set', [$attributes['alias'], new Reference($id)]);
            }
        }
    }

    /**
     * @param string            $serviceId
     * @param string            $serviceClass
     * @param UseCaseAnnotation $annotation
     * @param Definition        $executorDefinition
     * @param Definition        $containerDefinition
     */
    private function registerUseCase($serviceId, $serviceClass, $annotation, $executorDefinition, $containerDefinition)
    {
        $containerDefinition->addMethodCall('set', [$annotation->getName(), new Reference($serviceId)]);

        if ($annotation->getInputType()) {
            $executorDefinition->addMethodCall(
                'assignInputProcessor',
                [$annotation->getName(), $annotation->getInputType(), $annotation->getInputOptions()]
            );
        }

        if ($annotation->getOutputType()) {
            $executorDefinition->addMethodCall(
                'assignResponseProcessor',
                [$annotation->getName(), $annotation->getOutputType(), $annotation->getOutputOptions()]
            );
        }

        $requestClass = $this->requestResolver->resolve($serviceClass);
        $executorDefinition->addMethodCall('assignRequestClass', [$annotation->getName(), $requestClass]);
    }
}
