<?php

namespace spec\Lamudi\UseCaseBundle\Factory
{

    use Lamudi\UseCaseBundle\Exception\RequestClassNotFoundException;
    use Lamudi\UseCaseBundle\Factory\RequestResolver;
    use Lamudi\UseCaseBundle\Request\Request;
    use Lamudi\UseCaseBundle\Response\Response;
    use Lamudi\UseCaseBundle\UseCaseInterface;
    use PhpSpec\ObjectBehavior;
    use Prophecy\Argument;
    use spec\Lamudi\UseCaseBundle\Factory\Request\SomeRequest;

    /**
     * @mixin RequestResolver
     */
    class RequestResolverSpec extends ObjectBehavior
    {
        function it_is_initializable()
        {
            $this->shouldHaveType('Lamudi\UseCaseBundle\Factory\RequestResolver');
        }

        public function it_uses_the_use_case_namespace_plus_request()
        {
            $useCaseNamespace = 'spec\Lamudi\UseCaseBundle\Factory';
            $this->resolve(new SomeUseCase())->shouldReturnAnInstanceOf($useCaseNamespace . '\Request\SomeRequest');
        }

        public function it_resolves_the_default_use_case_request()
        {
            $this->resolve(new DefaultUseCase())->shouldReturnAnInstanceOf(Request::class);
        }

        public function it_throws_an_exception_if_request_class_does_not_exist()
        {
            $this->shouldThrow(RequestClassNotFoundException::class)->duringResolve(new WrongUseCase());
        }
    }

// helper class for testing purposes, will not be used outside this file

    class SomeUseCase implements UseCaseInterface
    {
        /**
         * @param SomeRequest $request
         */
        public function execute($request)
        {
        }
    }

    class WrongUseCase implements UseCaseInterface
    {
        /**
         * @param WrongRequest $request
         */
        public function execute($request)
        {
        }
    }

    class DefaultUseCase implements UseCaseInterface {
        /**
         * @param Request $request
         * @return Response
         */
        public function execute($request)
        {
        }
    }
}

namespace spec\Lamudi\UseCaseBundle\Factory\Request
{

    use Lamudi\UseCaseBundle\Request\Request;

    class SomeRequest extends Request
    {
    }
}