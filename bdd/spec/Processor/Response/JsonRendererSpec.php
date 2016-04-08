<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\UseCase\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @mixin \Lamudi\UseCaseBundle\Processor\Response\JsonRenderer
 */
class JsonRendererSpec extends ObjectBehavior
{
    public function let(SerializerInterface $serializer)
    {
        $this->beConstructedWith($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Processor\Response\JsonRenderer');
    }

    public function it_returns_json_response_with_encoded_response_content(SerializerInterface $serializer)
    {
        $serializer->serialize(Argument::any(), 'json')->willReturn('{this is encoded json}');

        $this->processResponse(new Response())->shouldBeAnInstanceOf(JsonResponse::class);
        $this->processResponse(new Response())->getContent()->shouldBe('{this is encoded json}');
    }

    public function it_appends_specified_fields_to_the_output_on_success(SerializerInterface $serializer)
    {
        $serializer->serialize(Argument::any(), 'json')->will(function($arguments) { return json_encode($arguments[0]); });

        $extraFields = ['code' => 200, 'success' => true, 'praise' => 'u r awesome'];
        $result = $this->processResponse(['foo' => 'bar'], ['append_on_success' => $extraFields]);

        $result->getContent()->shouldMatch('/"foo":"bar"/');
        $result->getContent()->shouldMatch('/"code":200/');
        $result->getContent()->shouldMatch('/"success":true/');
        $result->getContent()->shouldMatch('/"praise":"u r awesome"/');
    }

    public function it_appends_specified_fields_to_the_output_on_error(SerializerInterface $serializer)
    {
        $serializer->serialize(Argument::any(), 'json')->will(function($arguments) { return json_encode($arguments[0]); });
        $exception = new UseCaseException('epic fail', 500);

        $extraFields = ['code' => 500, 'success' => false];
        $result = $this->handleException($exception, ['append_on_error' => $extraFields]);

        $result->getStatusCode()->shouldBe(500);
        $result->getContent()->shouldMatch('/"code":' . $exception->getCode() . '/');
        $result->getContent()->shouldMatch('/"message":"' . $exception->getMessage() . '"/');
        $result->getContent()->shouldMatch('/"success":false/');
    }
}
