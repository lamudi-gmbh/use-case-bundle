<?php

namespace spec\Lamudi\UseCaseBundle\Request\Processor;

use Lamudi\UseCaseBundle\Request\Processor\ArrayInputProcessor;
use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ArrayInputProcessor
 */
class ArrayInputProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Request\Processor\ArrayInputProcessor');
    }

    public function it_is_an_input_processor()
    {
        $this->shouldHaveType(InputProcessorInterface::class);
    }

    public function it_copies_the_data_from_the_array_to_the_request_object()
    {
        $data = [
            'stringField' => 'some string',
            'numberField' => 216,
            'booleanField' => true,
            'arrayField' => [1, 2, 3, 4],
            'noSuchField' => true
        ];
        /** @var MyRequest $request */
        $request = $this->initializeRequest(new MyRequest(), $data);

        $request->stringField->shouldBe($data['stringField']);
        $request->numberField->shouldBe($data['numberField']);
        $request->booleanField->shouldBe($data['booleanField']);
        $request->arrayField->shouldBe($data['arrayField']);
        $request->omittedField->shouldBe(null);
        $request->omittedFieldWithDefaultValue->shouldBe('asdf');
    }

    public function it_maps_fields_from_array_to_object_using_custom_mappings()
    {
        $data = [
            'q' => 'search',
            'pi' => 3.1415,
            'flag' => false,
            'data' => ['x', 'y', 'z']
        ];
        $options = [
            'map' => [
                'q' => 'stringField',
                'pi' => 'numberField',
                'flag' => 'booleanField',
                'data' => 'arrayField'
            ]
        ];
        /** @var MyRequest $request */
        $request = $this->initializeRequest(new MyRequest(), $data, $options);

        $request->stringField->shouldBe($data['q']);
        $request->numberField->shouldBe($data['pi']);
        $request->booleanField->shouldBe($data['flag']);
        $request->arrayField->shouldBe($data['data']);
        $request->omittedField->shouldBe(null);
        $request->omittedFieldWithDefaultValue->shouldBe('asdf');
    }
}

class MyRequest
{
    public $stringField;
    public $numberField;
    public $booleanField;
    public $arrayField;
    public $omittedField;
    public $omittedFieldWithDefaultValue = 'asdf';
}
