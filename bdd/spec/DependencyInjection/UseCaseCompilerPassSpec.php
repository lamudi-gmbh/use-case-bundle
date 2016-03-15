<?php

namespace spec\Lamudi\UseCaseBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
use Lamudi\UseCaseBundle\Container\Container;
use Lamudi\UseCaseBundle\Container\ReferenceAcceptingContainerInterface;
use Lamudi\UseCaseBundle\Request\RequestResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class UseCaseCompilerPassSpec
 * @mixin \Lamudi\UseCaseBundle\DependencyInjection\UseCaseCompilerPass
 */
class UseCaseCompilerPassSpec extends ObjectBehavior
{
    public function let(
        AnnotationReader $annotationReader, RequestResolver $requestResolver,
        ContainerBuilder $containerBuilder, Definition $useCaseExecutorDefinition,
        Definition $useCaseContainerDefinition, Definition $inputProcessorContainerDefinition,
        Definition $responseProcessorContainerDefinition
    )
    {
        $this->beConstructedWith($annotationReader, $requestResolver);

        $containerBuilder->findDefinition('lamudi_use_case.executor')->willReturn($useCaseExecutorDefinition);
        $containerBuilder->findDefinition('lamudi_use_case.container.use_case')->willReturn($useCaseContainerDefinition);
        $containerBuilder->findDefinition('lamudi_use_case.container.input_processor')->willReturn($inputProcessorContainerDefinition);
        $containerBuilder->findDefinition('lamudi_use_case.container.response_processor')->willReturn($responseProcessorContainerDefinition);
        $containerBuilder->has('lamudi_use_case.executor')->willReturn(true);
        $useCaseContainerDefinition->getClass()->willReturn(Container::class);
        $inputProcessorContainerDefinition->getClass()->willReturn(Container::class);
        $responseProcessorContainerDefinition->getClass()->willReturn(Container::class);

        $containerBuilder->findTaggedServiceIds(Argument::any())->willReturn([]);
        $containerBuilder->getDefinitions()->willReturn([]);
        $useCaseExecutorDefinition->addMethodCall(Argument::cetera())->willReturn();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\DependencyInjection\UseCaseCompilerPass');
    }

    public function it_does_nothing_if_use_case_executor_is_not_registered(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->has('lamudi_use_case.executor')->willReturn(false);
        $containerBuilder->findDefinition('lamudi_use_case.executor')->shouldNotBeCalled();
        $containerBuilder->findTaggedServiceIds('use_case')->shouldNotBeCalled();
        $this->process($containerBuilder);
    }

    public function it_adds_annotated_services_to_the_use_case_container(
        ContainerBuilder $containerBuilder, AnnotationReader $annotationReader, Definition $useCaseContainerDefinition,
        Definition $useCaseExecutorDefinition
    )
    {
        $containerBuilder->getDefinitions()->willReturn([
            'uc1' => new Definition('\\stdClass'),
            'uc2' => new Definition('\\DateTime'),
            'uc3' => new Definition('\\Exception')
        ]);

        $useCase1Annotation = new UseCaseAnnotation([
            'value' => 'use_case_1',
            'input' => ['type' => 'form', 'name' => 'registration_form']
        ]);
        $useCase2Annotation1 = new UseCaseAnnotation([
            'value'  => 'use_case_2',
            'output' => ['type' => 'twig', 'template' => 'AppBundle:hello:index.html.twig']
        ]);
        $useCase2Annotation2 = new UseCaseAnnotation([
            'value'  => 'use_case_2_alias',
            'output' => ['type' => 'twig', 'template' => 'AppBundle:goodbye:index.html.twig']
        ]);
        $useCase3Annotation = new UseCaseAnnotation([
            'value' => 'use_case_3',
            'input' => 'http',
            'output' => ['type' => 'twig', 'template' => 'AppBundle:hello:index.html.twig']
        ]);

        $annotationReader->getClassAnnotations(new \ReflectionClass('\\stdClass'))->willReturn([$useCase1Annotation]);
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\DateTime'))->willReturn([$useCase2Annotation1, $useCase2Annotation2]);
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\Exception'))->willReturn([$useCase3Annotation]);

        $useCaseContainerDefinition->addMethodCall('set', ['use_case_1', new Reference('uc1')])->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', ['use_case_2', new Reference('uc2')])->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', ['use_case_2_alias', new Reference('uc2')])->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', ['use_case_3', new Reference('uc3')])->shouldBeCalled();

        $useCaseExecutorDefinition
            ->addMethodCall('assignInputProcessor', ['use_case_1', 'form', ['name' => 'registration_form']])
            ->shouldBeCalled();
        $useCaseExecutorDefinition
            ->addMethodCall('assignResponseProcessor', ['use_case_2', 'twig', ['template' => 'AppBundle:hello:index.html.twig']])
            ->shouldBeCalled();
        $useCaseExecutorDefinition
            ->addMethodCall('assignResponseProcessor', ['use_case_2_alias', 'twig', ['template' => 'AppBundle:goodbye:index.html.twig']])
            ->shouldBeCalled();

        $useCaseExecutorDefinition
            ->addMethodCall('assignInputProcessor', ['use_case_3', 'http', []])->shouldBeCalled();
        $useCaseExecutorDefinition
            ->addMethodCall('assignResponseProcessor', ['use_case_3', 'twig', ['template' => 'AppBundle:hello:index.html.twig']])
            ->shouldBeCalled();

        $this->process($containerBuilder);
    }

    public function it_adds_input_processors_to_container_under_an_alias(
        ContainerBuilder $containerBuilder, Definition $inputProcessorContainerDefinition
    )
    {
        $inputProcessorsWithTags = [
            'input_processor_1' => [['alias' => 'foo']],
            'input_processor_2' => [['alias' => 'bar']]
        ];
        $containerBuilder->findTaggedServiceIds('use_case_input_processor')->willReturn($inputProcessorsWithTags);

        $inputProcessorContainerDefinition->addMethodCall('set', ['foo', new Reference('input_processor_1')])->shouldBeCalled();
        $inputProcessorContainerDefinition->addMethodCall('set', ['bar', new Reference('input_processor_2')])->shouldBeCalled();

        $this->process($containerBuilder);
    }

    public function it_adds_response_processors_to_container_under_an_alias(
        ContainerBuilder $containerBuilder, Definition $responseProcessorContainerDefinition
    )
    {
        $responseProcessorsWithTags = [
            'response_processor_1' => [['alias' => 'faz']],
            'response_processor_2' => [['alias' => 'baz']]
        ];
        $containerBuilder->findTaggedServiceIds('use_case_response_processor')->willReturn($responseProcessorsWithTags);

        $responseProcessorContainerDefinition
            ->addMethodCall('set', ['faz', new Reference('response_processor_1')])
            ->shouldBeCalled();
        $responseProcessorContainerDefinition
            ->addMethodCall('set', ['baz', new Reference('response_processor_2')])
            ->shouldBeCalled();

        $this->process($containerBuilder);
    }

    public function it_uses_request_resolver_to_add_use_case_request_class_config_to_the_container(
        ContainerBuilder $containerBuilder, AnnotationReader $annotationReader, RequestResolver $requestResolver,
        Definition $useCaseExecutorDefinition, Definition $useCaseContainerDefinition
    )
    {
        $useCase1Annotation = new UseCaseAnnotation(['value' => 'use_case_1']);
        $useCase2Annotation = new UseCaseAnnotation(['value' => 'use_case_2']);
        $useCase3Annotation = new UseCaseAnnotation(['value' => 'use_case_3']);

        $containerBuilder->getDefinitions()->willReturn([
            'uc1' => new Definition('\\stdClass'),
            'uc2' => new Definition('\\DateTime'),
            'uc3' => new Definition('\\Exception')
        ]);

        $annotationReader->getClassAnnotations(new \ReflectionClass('\\stdClass'))->willReturn([$useCase1Annotation]);
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\DateTime'))->willReturn([$useCase2Annotation]);
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\Exception'))->willReturn([$useCase3Annotation]);

        $requestResolver->resolve('\\stdClass')->willReturn('\StdClassRequest');
        $requestResolver->resolve('\\DateTime')->willReturn('Foo\Bar\DateTimeRequest');
        $requestResolver->resolve('\\Exception')->willReturn('Ohnoes\FunnyRequest');

        $useCaseContainerDefinition->addMethodCall('set', ['use_case_1', new Reference('uc1')])->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', ['use_case_2', new Reference('uc2')])->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', ['use_case_3', new Reference('uc3')])->shouldBeCalled();

        $useCaseExecutorDefinition->addMethodCall('assignRequestClass', ['use_case_1', '\StdClassRequest'])->shouldBeCalled();
        $useCaseExecutorDefinition->addMethodCall('assignRequestClass', ['use_case_2', 'Foo\Bar\DateTimeRequest'])->shouldBeCalled();
        $useCaseExecutorDefinition->addMethodCall('assignRequestClass', ['use_case_3', 'Ohnoes\FunnyRequest'])->shouldBeCalled();

        $this->process($containerBuilder);
    }

    public function it_adds_service_names_instead_of_references_to_container_that_accepts_references(
        AnnotationReader $annotationReader, ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition,
        Definition $inputProcessorContainerDefinition, Definition $responseProcessorContainerDefinition
    )
    {
        $useCaseContainerDefinition->getClass()->willReturn(ContainerThatAcceptsReferences::class);
        $inputProcessorContainerDefinition->getClass()->willReturn(ContainerThatAcceptsReferences::class);
        $responseProcessorContainerDefinition->getClass()->willReturn(ContainerThatAcceptsReferences::class);

        $containerBuilder->getDefinitions()->willReturn(['service.use_case_1' => new Definition('\\stdClass')]);
        $useCaseAnnotation = new UseCaseAnnotation(['value' => 'use_case_1']);
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\stdClass'))->willReturn([$useCaseAnnotation]);

        $inputProcessorsWithTags = ['service.input_processor' => [['alias' => 'input']]];
        $responseProcessorsWithTags = ['service.response_processor' => [['alias' => 'output']]];
        $containerBuilder->findTaggedServiceIds('use_case_input_processor')->willReturn($inputProcessorsWithTags);
        $containerBuilder->findTaggedServiceIds('use_case_response_processor')->willReturn($responseProcessorsWithTags);

        $useCaseContainerDefinition->addMethodCall('set', Argument::is(['use_case_1', 'service.use_case_1']))->shouldBeCalled();
        $inputProcessorContainerDefinition->addMethodCall('set', Argument::is(['input', 'service.input_processor']))->shouldBeCalled();
        $responseProcessorContainerDefinition->addMethodCall('set', Argument::is(['output', 'service.response_processor']))->shouldBeCalled();

        $this->process($containerBuilder);
    }
}

class ContainerThatAcceptsReferences implements ReferenceAcceptingContainerInterface {
    public function set($name, $service) { }
    public function get($name) { }
}
