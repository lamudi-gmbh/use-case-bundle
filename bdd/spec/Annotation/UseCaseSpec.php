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
        $this->beConstructedWith(array('value' => 'use_case'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Annotation\UseCase');
    }

    public function it_requires_value()
    {
        $this->beConstructedWith(array('input' => 'http', 'output' => 'json'));
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_sets_input()
    {
        $this->setInput(array(
            'type'   => 'form',
            'name'   => 'search_form',
            'class'  => 'DumbForm',
            'method' => 'DELETE'
        ));

        $this->getInputType()->shouldBe('form');
        $this->getInputOptions()->shouldBe(array(
            'name'   => 'search_form',
            'class'  => 'DumbForm',
            'method' => 'DELETE'
        ));
    }

    public function it_sets_output()
    {
        $this->setOutput(array(
            'type'     => 'twig',
            'template' => 'base.html.twig',
            'form'     => 'DumberForm',
            'css'      => 'none'
        ));

        $this->getOutputType()->shouldBe('twig');
        $this->getOutputOptions()->shouldBe(array(
            'template' => 'base.html.twig',
            'form'     => 'DumberForm',
            'css'      => 'none'
        ));
    }
}
