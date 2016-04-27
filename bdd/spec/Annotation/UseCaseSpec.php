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

    public function it_sets_input_options()
    {
        $this->beConstructedWith(
            [
                'value' => 'uc',
                'input' => [
                    'type' => 'form',
                    'name' => 'search_form',
                    'class' => 'DumbForm',
                    'method' => 'DELETE'
                ]
            ]
        );

        $this->getInputType()->shouldBe('form');
        $this->getInputOptions()->shouldBe([
            'name'   => 'search_form',
            'class'  => 'DumbForm',
            'method' => 'DELETE'
        ]);
    }

    public function it_sets_response_options()
    {
        $this->beConstructedWith([
            'value' => 'uc',
            'response' => [
                'type' => 'twig',
                'template' => 'base.html.twig',
                'form' => 'DumberForm',
                'css' => 'none'
            ]
        ]);

        $this->getResponseType()->shouldBe('twig');
        $this->getResponseOptions()->shouldBe([
            'template' => 'base.html.twig',
            'form'     => 'DumberForm',
            'css'      => 'none'
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
