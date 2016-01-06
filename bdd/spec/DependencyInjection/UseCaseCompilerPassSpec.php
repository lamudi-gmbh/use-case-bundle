<?php

namespace spec\Lamudi\UseCaseBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
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
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition,
        AnnotationReader $annotationReader, RequestResolver $requestResolver
    )
    {
        $this->beConstructedWith($annotationReader, $requestResolver);

        $containerBuilder->findDefinition('lamudi_use_case.container')->willReturn($useCaseContainerDefinition);
        $containerBuilder->findTaggedServiceIds(Argument::any())->willReturn(array());
        $containerBuilder->getDefinitions()->willReturn(array());
        $containerBuilder->has('lamudi_use_case.container')->willReturn(true);
        $useCaseContainerDefinition->addMethodCall(Argument::cetera())->willReturn();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\DependencyInjection\UseCaseCompilerPass');
    }

    public function it_does_nothing_if_use_case_container_is_not_registered(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->has('lamudi_use_case.container')->willReturn(false);
        $containerBuilder->findDefinition('lamudi_use_case.container')->shouldNotBeCalled();
        $containerBuilder->findTaggedServiceIds('use_case')->shouldNotBeCalled();
        $this->process($containerBuilder);
    }

    public function it_adds_annotated_services_to_the_use_case_container(
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition, AnnotationReader $annotationReader
    )
    {
        $containerBuilder->getDefinitions()->willReturn(array(
            'uc1' => new Definition('\\stdClass'),
            'uc2' => new Definition('\\DateTime'),
            'uc3' => new Definition('\\Exception')
        ));

        $useCase1Annotation = new UseCaseAnnotation(array(
            'value' => 'use_case_1',
            'input' => array('type' => 'form', 'name' => 'registration_form')
        ));
        $useCase2Annotation1 = new UseCaseAnnotation(array(
            'value'  => 'use_case_2',
            'output' => array('type' => 'twig', 'template' => 'AppBundle:hello:index.html.twig')
        ));
        $useCase2Annotation2 = new UseCaseAnnotation(array(
            'value'  => 'use_case_2_alias',
            'output' => array('type' => 'twig', 'template' => 'AppBundle:goodbye:index.html.twig')
        ));
        $useCase3Annotation = new UseCaseAnnotation(array(
            'value' => 'use_case_3',
            'input' => 'http',
            'output' => array('type' => 'twig', 'template' => 'AppBundle:hello:index.html.twig')
        ));

        $annotationReader->getClassAnnotations(new \ReflectionClass('\\stdClass'))->willReturn(array($useCase1Annotation));
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\DateTime'))->willReturn(array($useCase2Annotation1, $useCase2Annotation2));
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\Exception'))->willReturn(array($useCase3Annotation));

        $useCaseContainerDefinition->addMethodCall('set', array('use_case_1', new Reference('uc1')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('assignInputConverter', array('use_case_1', 'form', array('name' => 'registration_form')))->shouldBeCalled();

        $useCaseContainerDefinition->addMethodCall('set', array('use_case_2', new Reference('uc2')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('assignResponseProcessor', array('use_case_2', 'twig', array('template' => 'AppBundle:hello:index.html.twig')))->shouldBeCalled();

        $useCaseContainerDefinition->addMethodCall('set', array('use_case_2_alias', new Reference('uc2')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('assignResponseProcessor', array('use_case_2_alias', 'twig', array('template' => 'AppBundle:goodbye:index.html.twig')))->shouldBeCalled();

        $useCaseContainerDefinition->addMethodCall('set', array('use_case_3', new Reference('uc3')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('assignInputConverter', array('use_case_3', 'http', array()))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('assignResponseProcessor', array('use_case_3', 'twig', array('template' => 'AppBundle:hello:index.html.twig')))->shouldBeCalled();

        $this->process($containerBuilder);
    }

    public function it_adds_input_converters_to_container_under_an_alias(
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition
    )
    {
        $inputConvertersWithTags = array(
            'input_converter_1' => array(array('alias' => 'foo')),
            'input_converter_2' => array(array('alias' => 'bar'))
        );
        $containerBuilder->findTaggedServiceIds('use_case_input_converter')->willReturn($inputConvertersWithTags);

        $useCaseContainerDefinition->addMethodCall('setInputConverter', array('foo', new Reference('input_converter_1')))
            ->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('setInputConverter', array('bar', new Reference('input_converter_2')))
            ->shouldBeCalled();

        $this->process($containerBuilder);
    }

    public function it_adds_response_processors_to_container_under_an_alias(
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition
    )
    {
        $inputConvertersWithTags = array(
            'response_processor_1' => array(array('alias' => 'faz')),
            'response_processor_2' => array(array('alias' => 'baz'))
        );
        $containerBuilder->findTaggedServiceIds('use_case_response_processor')->willReturn($inputConvertersWithTags);

        $useCaseContainerDefinition->addMethodCall('setResponseProcessor', array('faz', new Reference('response_processor_1')))
            ->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('setResponseProcessor', array('baz', new Reference('response_processor_2')))
            ->shouldBeCalled();

        $this->process($containerBuilder);
    }

    public function it_uses_request_resolver_to_add_use_case_request_class_config_to_the_container(
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition,
        AnnotationReader $annotationReader, RequestResolver $requestResolver
    )
    {
        $useCase1Annotation = new UseCaseAnnotation(array('value' => 'use_case_1'));
        $useCase2Annotation = new UseCaseAnnotation(array('value' => 'use_case_2'));
        $useCase3Annotation = new UseCaseAnnotation(array('value' => 'use_case_3'));

        $containerBuilder->getDefinitions()->willReturn(array(
            'uc1' => new Definition('\\stdClass'),
            'uc2' => new Definition('\\DateTime'),
            'uc3' => new Definition('\\Exception')
        ));

        $annotationReader->getClassAnnotations(new \ReflectionClass('\\stdClass'))->willReturn(array($useCase1Annotation));
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\DateTime'))->willReturn(array($useCase2Annotation));
        $annotationReader->getClassAnnotations(new \ReflectionClass('\\Exception'))->willReturn(array($useCase3Annotation));

        $requestResolver->resolve('\\stdClass')->willReturn('\StdClassRequest');
        $requestResolver->resolve('\\DateTime')->willReturn('Foo\Bar\DateTimeRequest');
        $requestResolver->resolve('\\Exception')->willReturn('Ohnoes\FunnyRequest');

        $useCaseContainerDefinition->addMethodCall('set', array('use_case_1', new Reference('uc1')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', array('use_case_2', new Reference('uc2')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', array('use_case_3', new Reference('uc3')))->shouldBeCalled();

        $useCaseContainerDefinition->addMethodCall('assignRequestClass', array('use_case_1', '\StdClassRequest'))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('assignRequestClass', array('use_case_2', 'Foo\Bar\DateTimeRequest'))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('assignRequestClass', array('use_case_3', 'Ohnoes\FunnyRequest'))->shouldBeCalled();

        $this->process($containerBuilder);
    }
}
