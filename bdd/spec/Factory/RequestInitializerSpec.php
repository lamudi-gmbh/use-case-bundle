<?php

namespace spec\Lamudi\UseCaseBundle\Factory;

use Lamudi\UseCaseBundle\Factory\RequestInitializer;
use Lamudi\UseCaseBundle\Request\Request;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin RequestInitializer
 */
class RequestInitializerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Factory\RequestInitializer');
    }

    public function it_sets_request_object_values_from_array()
    {
        $request = new FlatRequest();
        $data = array('stringField' => 'some string', 'numberField' => 123, 'booleanField' => true);

        $this->initialize($request, $data)->stringField->shouldBe('some string');
        $this->initialize($request, $data)->numberField->shouldBe(123);
        $this->initialize($request, $data)->booleanField->shouldBe(true);
    }
}

class FlatRequest extends Request
{
    public $stringField;
    public $numberField;
    public $booleanField;
}
