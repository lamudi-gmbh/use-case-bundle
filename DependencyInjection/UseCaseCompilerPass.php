<?php

namespace Lamudi\UseCaseBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
use Lamudi\UseCaseBundle\Container\ReferenceAcceptingContainerInterface;
use Lamudi\UseCaseBundle\UseCase\RequestResolver;
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
     * @throws \Exception
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('lamudi_use_case.executor')) {
            return;
        }

        $this->addInputProcessorsToContainer($container);
        $this->addResponseProcessorsToContainer($container);
        $this->addUseCasesToContainer($container);
        $this->addContextsToResolver($container);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     * @throws \Exception
     */
    private function addUseCasesToContainer(ContainerBuilder $container)
    {
        $executorDefinition = $container->findDefinition('lamudi_use_case.executor');
        $useCaseContainerDefinition = $container->findDefinition('lamudi_use_case.container.use_case');
        $services = $container->getDefinitions();

        foreach ($services as $id => $serviceDefinition) {
            $serviceClass = $serviceDefinition->getClass();
            if (!class_exists($serviceClass)) {
                continue;
            }

            $useCaseReflection = new \ReflectionClass($serviceClass);
            try {
                $annotations = $this->annotationReader->getClassAnnotations($useCaseReflection);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception(sprintf('Could not load annotations for class %s: %s', $serviceClass, $e->getMessage()));
            }

            foreach ($annotations as $annotation) {
                if ($annotation instanceof UseCaseAnnotation && $this->validateUseCase($useCaseReflection)) {
                    $this->registerUseCase($id, $serviceClass, $annotation, $executorDefinition, $useCaseContainerDefinition);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    private function addInputProcessorsToContainer(ContainerBuilder $containerBuilder)
    {
        $processorContainerDefinition = $containerBuilder->findDefinition('lamudi_use_case.container.input_processor');
        $inputProcessors = $containerBuilder->findTaggedServiceIds('use_case_input_processor');
        foreach ($inputProcessors as $id => $tags) {
            foreach ($tags as $attributes) {
                if ($this->containerAcceptsReferences($processorContainerDefinition)) {
                    $processorContainerDefinition->addMethodCall('set', [$attributes['alias'], $id]);
                } else {
                    $processorContainerDefinition->addMethodCall('set', [$attributes['alias'], new Reference($id)]);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    private function addResponseProcessorsToContainer(ContainerBuilder $containerBuilder)
    {
        $processorContainerDefinition = $containerBuilder->findDefinition(
            'lamudi_use_case.container.response_processor'
        );
        $responseProcessors = $containerBuilder->findTaggedServiceIds('use_case_response_processor');

        foreach ($responseProcessors as $id => $tags) {
            foreach ($tags as $attributes) {
                if ($this->containerAcceptsReferences($processorContainerDefinition)) {
                    $processorContainerDefinition->addMethodCall('set', [$attributes['alias'], $id]);
                } else {
                    $processorContainerDefinition->addMethodCall('set', [$attributes['alias'], new Reference($id)]);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    private function addContextsToResolver(ContainerBuilder $containerBuilder)
    {
        $resolverDefinition = $containerBuilder->findDefinition('lamudi_use_case.context_resolver');
        $defaultContextName = $containerBuilder->getParameter('lamudi_use_case.default_context');
        $contexts = (array)$containerBuilder->getParameter('lamudi_use_case.contexts');

        $resolverDefinition->addMethodCall('setDefaultContextName', [$defaultContextName]);
        foreach ($contexts as $context) {
            if (isset($context['name'])) {
                $name = $context['name'];
                $input = isset($context['input']) ? $context['input'] : null;
                $response = isset($context['response']) ? $context['response'] : null;
                $resolverDefinition->addMethodCall('addContextDefinition', [$name, $input, $response]);
            }
        }
    }

    /**
     * @param \ReflectionClass $useCase
     *
     * @return bool
     * @throws InvalidUseCase
     */
    private function validateUseCase($useCase)
    {
        if ($useCase->hasMethod('execute')) {
            return true;
        } else {
            throw new InvalidUseCase(sprintf(
                'Class "%s" has been annotated as a use case, but does not contain execute() method.', $useCase->getName()
            ));
        }
    }

    /**
     * @param string            $serviceId
     * @param string            $serviceClass
     * @param UseCaseAnnotation $annotation
     * @param Definition        $executorDefinition
     * @param Definition        $containerDefinition
     *
     * @throws \Lamudi\UseCaseBundle\UseCase\RequestClassNotFoundException
     */
    private function registerUseCase($serviceId, $serviceClass, $annotation, $executorDefinition, $containerDefinition)
    {
        $useCaseName = $annotation->getName() ?: $this->fqnToUseCaseName($serviceClass);
        if ($this->containerAcceptsReferences($containerDefinition)) {
            $containerDefinition->addMethodCall('set', [$useCaseName, $serviceId]);
        } else {
            $containerDefinition->addMethodCall('set', [$useCaseName, new Reference($serviceId)]);
        }

        if ($annotation->getInputType()) {
            $executorDefinition->addMethodCall(
                'assignInputProcessor',
                [$useCaseName, $annotation->getInputType(), $annotation->getInputOptions()]
            );
        }

        if ($annotation->getResponseType()) {
            $executorDefinition->addMethodCall(
                'assignResponseProcessor',
                [$useCaseName, $annotation->getResponseType(), $annotation->getResponseOptions()]
            );
        }

        $requestClassName = $this->requestResolver->resolve($serviceClass);
        $executorDefinition->addMethodCall('assignRequestClass', [$useCaseName, $requestClassName]);
    }

    /**
     * @param Definition $containerDefinition
     *
     * @return bool
     */
    private function containerAcceptsReferences($containerDefinition)
    {
        $interfaces = class_implements($containerDefinition->getClass());
        if (is_array($interfaces)) {
            return in_array(ReferenceAcceptingContainerInterface::class, $interfaces);
        } else {
            return false;
        }
    }

    /**
     * @param string $fqn
     *
     * @return string
     */
    private function fqnToUseCaseName($fqn)
    {
        $unqualifiedName = substr($fqn, strrpos($fqn, '\\') + 1);
        return ltrim(strtolower(preg_replace('/[A-Z0-9]/', '_$0', $unqualifiedName)), '_');
    }
}
