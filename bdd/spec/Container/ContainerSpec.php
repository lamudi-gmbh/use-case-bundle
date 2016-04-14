<?php

namespace spec\Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Container\ItemNotFoundException;
use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
use Lamudi\UseCaseBundle\UseCase\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContainerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\Container');
    }

    public function it_stores_an_item_in_the_container(UseCaseInterface $useCase, InputProcessorInterface $inputProcessor)
    {
        $this->set('use_case', $useCase);
        $this->set('input_processor', $inputProcessor);

        $this->get('use_case')->shouldBe($useCase);
        $this->get('input_processor')->shouldBe($inputProcessor);
    }

    public function it_throws_an_exception_if_service_was_not_found()
    {
        $this->shouldThrow(new ItemNotFoundException('Item "no_such_service_here" not found.'))
            ->duringGet('no_such_service_here');
    }
}
