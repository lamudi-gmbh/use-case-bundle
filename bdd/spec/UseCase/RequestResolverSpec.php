<?php

namespace spec\Lamudi\UseCaseBundle\UseCase;

use Lamudi\UseCaseBundle\UseCase\RequestClassNotFoundException;
use Lamudi\UseCaseBundle\UseCase\RequestResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin RequestResolver
 */
class RequestResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\UseCase\RequestResolver');
    }

    public function it_tries_to_use_type_hint_in_execute_method()
    {
        $this->resolve(new TypeHintedUseCase())->shouldReturn(SpecificRequest::class);
    }

    public function it_throws_an_exception_if_execute_method_is_not_hinted()
    {
        $this->shouldThrow(RequestClassNotFoundException::class)->duringResolve(new UseCaseWithoutTypeHint());
    }

    public function it_uses_std_class_if_request_has_no_arguments()
    {
        $this->resolve(new UseCaseWithoutRequest())->shouldReturn(\stdClass::class);
    }

    public function it_throws_an_exception_if_use_case_does_not_have_execute_method()
    {
        $this->shouldThrow(RequestClassNotFoundException::class)->duringResolve(new NotAUseCase());
    }
}

class TypeHintedUseCase
{
    public function execute(SpecificRequest $request)
    {
    }
}

class UseCaseWithoutRequest
{
    public function execute()
    {
    }
}

class UseCaseWithoutTypeHint
{
    public function execute($request)
    {
    }
}

class NotAUseCase
{
    public function doSomething()
    {
    }
}

class SpecificRequest
{
}

