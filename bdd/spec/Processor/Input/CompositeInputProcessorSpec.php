<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Input;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Processor\Exception\EmptyCompositeProcessorException;
use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
use PhpSpec\Exception\Example\MatcherException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Processor\Input\CompositeInputProcessor
 */
class CompositeInputProcessorSpec extends ObjectBehavior
{
    public function let(
        InputProcessorInterface $inputProcessor1, InputProcessorInterface $inputProcessor2,
        ContainerInterface $inputProcessorContainer
    )
    {
        $this->beConstructedWith($inputProcessorContainer);
        $inputProcessorContainer->get('processor_1')->willReturn($inputProcessor1);
        $inputProcessorContainer->get('processor_2')->willReturn($inputProcessor2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Processor\Input\CompositeInputProcessor');
    }

    public function it_is_an_input_processor()
    {
        $this->shouldHaveType(InputProcessorInterface::class);
    }

    public function it_calls_input_processors_in_chain_resolving_them_from_options(
        InputProcessorInterface $inputProcessor1, InputProcessorInterface $inputProcessor2
    )
    {
        $request = new UseCaseRequest();
        $input = ['some' => 'input'];

        $inputProcessor1->initializeRequest($request, $input, [])->will(function($args) {
            $args[0]->initialized1 = true;
        });
        $inputProcessor2->initializeRequest(Argument::which('getInitialized1', true), $input, [])->will(function($args) {
            $args[0]->initialized2 = true;
        });

        $this->initializeRequest($request, $input, ['processor_1', 'processor_2']);

        if ($request->initialized2 !== true) {
            throw new MatcherException('"initialized2" should be "true".');
        }
    }

    public function it_calls_input_processors_in_chain_with_options(
        InputProcessorInterface $inputProcessor1, InputProcessorInterface $inputProcessor2
    )
    {
        $request = new UseCaseRequest();
        $input = ['some' => 'input'];

        $inputProcessor1->initializeRequest($request, $input, ['option1' => 'value1'])->will(function($args) {
            $args[0]->initialized1 = 20;
        });
        $inputProcessor2->initializeRequest(Argument::which('getInitialized1', 20), $input, ['foo' => 'bar'])->will(function($args) {
            $args[0]->initialized2 = true;
        });

        $this->initializeRequest($request, $input, ['processor_1' => ['option1' => 'value1'], 'processor_2' => ['foo' => 'bar']]);

        if ($request->initialized2 !== true) {
            throw new MatcherException('"initialized2" should be "true".');
        }
    }

    public function it_throws_an_exception_if_no_processors_have_been_added()
    {
        $this->shouldThrow(EmptyCompositeProcessorException::class)->duringInitializeRequest('this is', 'irrelevant here', []);
    }
}

class UseCaseRequest
{
    public $initialized1 = false;
    public $initialized2 = false;
    public $initialized3 = false;

    /**
     * @return boolean
     */
    public function getInitialized1()
    {
        return $this->initialized1;
    }

    /**
     * @return boolean
     */
    public function getInitialized2()
    {
        return $this->initialized2;
    }

    /**
     * @return boolean
     */
    public function getInitialized3()
    {
        return $this->initialized3;
    }
}
