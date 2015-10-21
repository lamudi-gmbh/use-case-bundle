<?php

namespace spec\Lamudi\UseCaseBundle\Factory;

use Lamudi\UseCaseBundle\Factory\RequestInitializer;
use Lamudi\UseCaseBundle\Factory\RequestNormalizer;
use Lamudi\UseCaseBundle\Factory\RequestResolver;
use Lamudi\UseCaseBundle\Factory\UseCaseRequestFactory;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * @mixin UseCaseRequestFactory
 */
class UseCaseRequestFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Factory\UseCaseRequestFactory');
    }

    public function let(RequestResolver $resolver, RequestNormalizer $normalizer, RequestInitializer $initializer)
    {
        $this->beConstructedWith($resolver, $normalizer, $initializer);
    }

    public function it_resolves_request_class_using_use_case_object(
        UseCaseInterface $useCase, RequestResolver $resolver
    )
    {
        $resolver->resolve($useCase)->shouldBeCalled();
        $this->createRequest($useCase, null);
    }

    public function it_normalizes_the_request_data_and_uses_it_to_initialize(
        UseCaseInterface $useCase, RequestResolver $resolver, RequestNormalizer $normalizer,
        RequestInitializer $initializer, Request $request, HttpRequest $httpRequest
    )
    {
        $requestData = array('key' => 'value', 'yes' => 'no');
        $resolver->resolve($useCase)->willReturn($request);
        $normalizer->normalize($httpRequest)->willReturn($requestData);
        $initializer->initialize($request, $requestData)->shouldBeCalled();

        $this->createRequest($useCase, $httpRequest)->shouldReturn($request);
    }
}
