<?php

namespace spec\Lamudi\UseCaseBundle\UseCase
{

    use Lamudi\UseCaseBundle\UseCase\RequestClassNotFoundException;
    use Lamudi\UseCaseBundle\UseCase\RequestResolver;
    use PhpSpec\ObjectBehavior;
    use Prophecy\Argument;
    use spec\Lamudi\UseCaseBundle\UseCase\Request\SomeRequest;

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

        public function it_uses_the_use_case_namespace_plus_request()
        {
            $useCaseNamespace = 'spec\Lamudi\UseCaseBundle\UseCase';
            $this->resolve(new SomeUseCase())->shouldReturn($useCaseNamespace . '\Request\SomeRequest');
        }

        public function it_throws_an_exception_if_request_class_does_not_exist()
        {
            $this->shouldThrow(RequestClassNotFoundException::class)->duringResolve(new WrongUseCase());
        }
    }

    class TypeHintedUseCase
    {
        public function execute(SpecificRequest $request)
        {
        }
    }

    class SomeUseCase
    {
        /**
         * @param SomeRequest $request
         */
        public function execute($request)
        {
        }
    }

    class WrongUseCase
    {
        /**
         * @param IntentionallyWrongRequest $request
         */
        public function execute($request)
        {
        }
    }

    class SpecificRequest
    {
    }
}

namespace spec\Lamudi\UseCaseBundle\UseCase\Request
{
    class SomeRequest
    {
    }
}
