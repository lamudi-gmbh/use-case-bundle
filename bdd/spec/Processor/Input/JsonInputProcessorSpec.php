<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Input;

use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
use Lamudi\UseCaseBundle\Processor\Input\JsonInputProcessor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * @mixin JsonInputProcessor
 */
class JsonInputProcessorSpec extends ObjectBehavior
{
    public function let(DecoderInterface $jsonDecoder)
    {
        $this->beConstructedWith($jsonDecoder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Processor\Input\JsonInputProcessor');
    }

    public function it_is_an_input_processor()
    {
        $this->shouldHaveType(InputProcessorInterface::class);
    }

    public function it_does_nothing_if_input_is_not_a_symfony_http_request(DecoderInterface $jsonDecoder)
    {
        $jsonDecoder->decode(Argument::cetera())->shouldNotBeCalled();
        $this->initializeRequest(new \stdClass(), new \stdClass());
    }

    public function it_populates_the_request_with_json_body_data(HttpRequest $httpRequest, DecoderInterface $jsonDecoder)
    {
        $data = ['stringField' => 'asd', 'numberField' => 123, 'booleanField' => true, 'arrayField' => [3, 2, 1]];
        $jsonDecoder->decode(Argument::any(), 'json')->willReturn($data);

        /** @var JsonRequest $request */
        $request = $this->initializeRequest(new JsonRequest(), $httpRequest);
        $request->stringField->shouldBe($data['stringField']);
        $request->numberField->shouldBe($data['numberField']);
        $request->booleanField->shouldBe($data['booleanField']);
        $request->arrayField->shouldBe($data['arrayField']);
    }

    public function it_populates_the_request_by_mapping_json_fields_to_request_object(
        HttpRequest $httpRequest, DecoderInterface $jsonDecoder
    )
    {
        $data = ['foo' => 'qwe', 'n' => 999, 'opt' => true, 'nums' => [3, 2, 1]];
        $jsonDecoder->decode(Argument::any(), 'json')->willReturn($data);

        $options = ['map' => [
            'foo'  => 'stringField',
            'n'    => 'numberField',
            'opt'  => 'booleanField',
            'nums' => 'arrayField'
        ]];
        /** @var JsonRequest $request */
        $request = $this->initializeRequest(new JsonRequest(), $httpRequest, $options);
        $request->stringField->shouldBe($data['foo']);
        $request->numberField->shouldBe($data['n']);
        $request->booleanField->shouldBe($data['opt']);
        $request->arrayField->shouldBe($data['nums']);
    }
}

class JsonRequest
{
    public $stringField;
    public $numberField;
    public $booleanField;
    public $arrayField;
}
