<?php

namespace spec\Lamudi\UseCaseBundle\DependencyInjection;

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
    public function let(ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition)
    {
        $containerBuilder->has('lamudi_use_case.container')->willReturn(true);
        $containerBuilder->findDefinition('lamudi_use_case.container')->willReturn($useCaseContainerDefinition);
        $containerBuilder->findTaggedServiceIds(Argument::any())->willReturn(array());
        $useCaseContainerDefinition->addMethodCall(Argument::any())->willReturn();
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

    public function it_adds_tagged_services_to_the_use_case_container(
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition
    )
    {
        $containerBuilder->findTaggedServiceIds('use_case')->willReturn(array('uc1' => array(), 'uc2' => array()));

        $useCaseContainerDefinition->addMethodCall('set', array('uc1', new Reference('uc1')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', array('uc2', new Reference('uc2')))->shouldBeCalled();

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

    public function it_tells_to_load_settings_from_use_case_class_annotations(
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition
    )
    {
        $useCaseContainerDefinition->addMethodCall('loadSettingsFromAnnotations')->shouldBeCalled();
        $this->process($containerBuilder);
    }
}
