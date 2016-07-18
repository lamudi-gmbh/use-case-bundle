<?php

namespace spec\Lamudi\UseCaseBundle\Annotation;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Annotation\UseCase
 */
class UseCaseSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['value' => 'use_case']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Annotation\UseCase');
    }

    public function it_sets_input_processor()
    {
        $this->beConstructedWith([
            'value' => 'uc',
            'input' => 'form',
            'response' => 'json'
        ]);

        $this->getName()->shouldBe('uc');
        $this->getInputProcessorName()->shouldBe('form');
        $this->getInputProcessorOptions()->shouldBe([]);
        $this->getResponseProcessorName()->shouldBe('json');
        $this->getResponseProcessorOptions()->shouldBe([]);
    }

    public function it_uses_composite_processors_if_options_are_arrays()
    {
        $this->beConstructedWith([
            'value' => 'uc',
            'input' => [
                'form' => [
                    'name' => 'search_form',
                    'method' => 'DELETE'
                ]
            ],
            'response' => [
                'twig' => [
                    'template' => 'base.html.twig',
                    'form' => 'DumberForm',
                    'css' => 'none'
                ],
                'cookies' => [
                    'some' => 'cookie'
                ]
            ]
        ]);

        $this->getInputProcessorName()->shouldBe('composite');
        $this->getInputProcessorOptions()->shouldBe([
            'form' => [
                'name' => 'search_form',
                'method' => 'DELETE'
            ]
        ]);
        $this->getResponseProcessorName()->shouldBe('composite');
        $this->getResponseProcessorOptions()->shouldBe([
            'twig' => [
                'template' => 'base.html.twig',
                'form' => 'DumberForm',
                'css' => 'none'
            ],
            'cookies' => [
                'some' => 'cookie'
            ]
        ]);
    }


    public function it_throws_an_exception_if_an_unsupported_option_was_used()
    {
        $this->beConstructedWith([
            'value' => 'use_case',
            'input' => 'http',
            'response' => 'twig',
            'output' => 'this is deprecated',
            'foo' => 'this is just silly'
        ]);
        $this->shouldThrow(new \InvalidArgumentException('Unsupported options on UseCase annotation: output, foo'))
            ->duringInstantiation();
    }
}
