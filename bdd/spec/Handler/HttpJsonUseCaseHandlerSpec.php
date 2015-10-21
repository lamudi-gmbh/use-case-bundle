<?php

namespace spec\Lamudi\UseCaseBundle\Handler;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Handler\HttpJsonUseCaseHandler;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Response;
use Lamudi\UseCaseBundle\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @mixin HttpJsonUseCaseHandler
 */
class HttpJsonUseCaseHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Handler\HttpJsonUseCaseHandler');
    }

    public function let(EncoderInterface $encoder)
    {
        $this->beConstructedWith($encoder);
    }

    public function it_returns_json_response(
        UseCaseInterface $useCase, Request $request
    )
    {
        $useCase->execute($request)->willReturn(Response::class);
        $this->handle($useCase, $request)->shouldReturnAnInstanceOf(JsonResponse::class);
    }

    public function it_encodes_the_returned_response(
        UseCaseInterface $useCase, Request $request, Response $response, EncoderInterface $encoder
    )
    {
        $useCase->execute($request)->willReturn($response);
        $encoder->encode($response, 'json')->shouldBeCalled();

        $this->handle($useCase, $request);
    }

    public function it_returns_json_with_code_200_if_response_is_empty(
        UseCaseInterface $useCase, Request $request, EncoderInterface $encoder
    )
    {
        $encoder->encode(Argument::cetera())->willReturn('{}');
        $this->handle($useCase, $request)->getContent()->shouldReturn('{"code":200}');
    }

    public function it_adds_code_200_to_a_non_empty_response(
        UseCaseInterface $useCase, Request $request, EncoderInterface $encoder
    )
    {
        $encoder->encode(Argument::cetera())->willReturn(json_encode(array('foo' => 'bar', 'x' => 123, 'is' => true)));
        $this->handle($useCase, $request)->getContent()->shouldReturn('{"foo":"bar","x":123,"is":true,"code":200}');
    }

    public function it_returns_json_response_with_code_and_message_from_the_exception_upon_failure(
        UseCaseInterface $useCase, Request $request
    )
    {
        $useCase->execute($request)->willThrow(new UseCaseException('nasty error', 500));
        $this->handle($useCase, $request)->getContent()->shouldReturn('{"code":500,"message":"nasty error"}');
    }
}
