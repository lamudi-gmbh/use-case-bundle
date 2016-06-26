<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Input;

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
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Processor\Input\CompositeInputProcessor');
    }

    public function it_is_an_input_processor()
    {
        $this->shouldHaveType(InputProcessorInterface::class);
    }

    public function it_calls_input_processors_in_chain(
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

        $this->addInputProcessor($inputProcessor1);
        $this->addInputProcessor($inputProcessor2);

        $this->initializeRequest($request, $input);

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
        $inputProcessor1->initializeRequest(Argument::which('getInitialized1', 20), $input, ['option1' => 'another_value'])->will(function($args) {
            $args[0]->initialized2 = 50;
        });
        $inputProcessor2->initializeRequest(Argument::which('getInitialized2', 50), $input, ['foo' => 'bar'])->will(function($args) {
            $args[0]->initialized3 = true;
        });

        $this->addInputProcessor($inputProcessor1, ['option1' => 'value1']);
        $this->addInputProcessor($inputProcessor1, ['option1' => 'another_value']);
        $this->addInputProcessor($inputProcessor2, ['foo' => 'bar']);

        $this->initializeRequest($request, $input);

        if ($request->initialized3 !== true) {
            throw new MatcherException('"initialized3" should be "true".');
        }
    }

    public function it_calls_input_processors_in_chain_with_options_merged_with_global_options(
        InputProcessorInterface $inputProcessor1, InputProcessorInterface $inputProcessor2
    )
    {
        $request = new UseCaseRequest();
        $input = ['some' => 'input'];

        $inputProcessor1->initializeRequest(
            $request, $input, ['option1' => 'value1', 'global' => 'option']
        )->will(function($args) {
            $args[0]->initialized1 = 20;
        });
        $inputProcessor1->initializeRequest(
            Argument::which('getInitialized1', 20), $input, ['option1' => 'another_value', 'global' => 'option']
        )->will(function($args) {
            $args[0]->initialized2 = 50;
        });
        $inputProcessor2->initializeRequest(
            Argument::which('getInitialized2', 50), $input, ['foo' => 'bar', 'global' => 'option']
        )->will(function($args) {
            $args[0]->initialized3 = true;
        });

        $this->addInputProcessor($inputProcessor1, ['option1' => 'value1', 'global' => 'is overridden']);
        $this->addInputProcessor($inputProcessor1, ['option1' => 'another_value']);
        $this->addInputProcessor($inputProcessor2, ['foo' => 'bar']);

        $this->initializeRequest($request, $input, ['global' => 'option']);

        if ($request->initialized3 !== true) {
            throw new MatcherException('"initialized3" should be "true".');
        }
    }

    public function it_throws_an_exception_if_no_processors_have_been_added()
    {
        $this->shouldThrow(EmptyCompositeProcessorException::class)->duringInitializeRequest('this is', 'irrelevant here');
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
