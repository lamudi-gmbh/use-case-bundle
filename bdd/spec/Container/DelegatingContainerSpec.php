<?php

namespace spec\Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\ServiceNotFoundException;
use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DelegatingContainerSpec extends ObjectBehavior
{
    public function let(ContainerInterface $symfonyContainer)
    {
        $this->beConstructedWith($symfonyContainer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\DelegatingContainer');
    }

    function it_is_a_container_that_accept_references()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\ContainerInterface');
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\ReferenceAcceptingContainerInterface');
    }

    public function it_sets_a_service_reference_in_the_container(
        UseCaseInterface $useCase, InputProcessorInterface $inputProcessor, ContainerInterface $symfonyContainer
    )
    {
        $symfonyContainer->get('lamudi_use_case.some_service')->willReturn($useCase);
        $symfonyContainer->get('lamudi_use_case.input_processor.holy_magic')->willReturn($inputProcessor);

        $this->set('use_case', 'lamudi_use_case.some_service');
        $this->set('input_processor', 'lamudi_use_case.input_processor.holy_magic');

        $this->get('use_case')->shouldBe($useCase);
        $this->get('input_processor')->shouldBe($inputProcessor);
    }

    public function it_throws_an_exception_if_reference_was_not_set()
    {
        $this->shouldThrow(new ServiceNotFoundException('Service "no_such_service_here" not found.'))
            ->duringGet('no_such_service_here');
    }

    public function it_throws_an_exception_if_service_was_not_found(ContainerInterface $symfonyContainer)
    {
        $this->set('some_service', 'no_such_service_in_container');
        $symfonyContainer->get('no_such_service_in_container')
            ->willThrow(\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException::class);

        $this->shouldThrow(new ServiceNotFoundException('Reference "some_service" points to a non-existent service "no_such_service_in_container".'))
            ->duringGet('some_service');
    }
}
