<?php

namespace spec\Lamudi\UseCaseBundle\DependencyInjection;

use Lamudi\UseCaseBundle\UseCaseInterface;
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
        ContainerBuilder $containerBuilder, Definition $useCaseContainerDefinition,
        UseCaseInterface $useCase1, UseCaseInterface $useCase2
    )
    {
        $containerBuilder->has('lamudi_use_case.container')->willReturn(true);
        $containerBuilder->findDefinition('lamudi_use_case.container')->willReturn($useCaseContainerDefinition);
        $containerBuilder->findTaggedServiceIds('use_case')->willReturn(array('uc1' => $useCase1, 'uc2' => $useCase2));

        $useCaseContainerDefinition->addMethodCall('set', array('uc1', new Reference('uc1')))->shouldBeCalled();
        $useCaseContainerDefinition->addMethodCall('set', array('uc2', new Reference('uc2')))->shouldBeCalled();

        $this->process($containerBuilder);
    }
}
