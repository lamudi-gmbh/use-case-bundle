<?php

namespace spec\Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Container\UseCaseContainer
 */
class UseCaseContainerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\UseCaseContainer');
    }

    public function it_stores_use_cases_identified_by_name(
        UseCaseInterface $useCase1, UseCaseInterface $useCase2
    )
    {
        $this->set('login', $useCase1);
        $this->set('logout', $useCase2);

        $this->get('login')->shouldBe($useCase1);
        $this->get('logout')->shouldBe($useCase2);
    }

    public function it_throws_exception_when_no_use_case_by_given_name_exists(
        UseCaseInterface $useCase
    )
    {
        $this->set('a_use_case', $useCase);
        $this->shouldThrow(UseCaseNotFoundException::class)->duringGet('no_such_use_case_here');
    }
}
