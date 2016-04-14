<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Processor\Response\JsonRenderer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @mixin JsonRenderer
 */
class JsonRendererSpec extends ObjectBehavior
{
    public function let(SerializerInterface $serializer)
    {
        $this->beConstructedWith($serializer);

        $serializer->serialize(Argument::any(), 'json')->will(
            function ($arguments) {
                return json_encode($arguments[0]);
            }
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Processor\Response\JsonRenderer');
    }

    public function it_returns_json_response_with_encoded_response_content(SerializerInterface $serializer)
    {
        $serializer->serialize(Argument::any(), 'json')->willReturn('{this is encoded json}');

        $this->processResponse(new \stdClass())->shouldBeAnInstanceOf(JsonResponse::class);
        $this->processResponse(new \stdClass())->getContent()->shouldBe('{this is encoded json}');
    }

    public function it_appends_specified_fields_to_the_output_on_success()
    {
        $extraFields = ['code' => 200, 'success' => true, 'praise' => 'u r awesome'];
        $result = $this->processResponse(['foo' => 'bar'], ['append_on_success' => $extraFields]);

        $result->getContent()->shouldMatch('/"foo":"bar"/');
        $result->getContent()->shouldMatch('/"code":200/');
        $result->getContent()->shouldMatch('/"success":true/');
        $result->getContent()->shouldMatch('/"praise":"u r awesome"/');
    }

    public function it_appends_specified_fields_to_the_output_on_error()
    {
        $exception = new UseCaseException('epic fail', 444);

        $extraFields = ['code' => 500, 'success' => false];
        $result = $this->handleException($exception, ['append_on_error' => $extraFields]);

        $result->getStatusCode()->shouldBe(JsonRenderer::DEFAULT_HTTP_STATUS_CODE);
        $result->getContent()->shouldMatch('/"code":444/');
        $result->getContent()->shouldMatch('/"message":"' . $exception->getMessage() . '"/');
        $result->getContent()->shouldMatch('/"success":false/');
    }

    public function it_uses_custom_http_status_code()
    {
        $exception = new UseCaseException('epic fail', 500);

        $result = $this->handleException($exception, ['http_status_code' => 403]);
        $result->getStatusCode()->shouldBe(403);
        $result->getContent()->shouldMatch('/"message":"' . $exception->getMessage() . '"/');
    }
}
