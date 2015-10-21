<?php

namespace spec\Lamudi\UseCaseBundle\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class RequestNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Factory\RequestNormalizer');
    }

    public function it_decodes_the_json_from_request_content(Request $request)
    {
        $data = array('data' => 'something', 'foo' => 123, 'pi' => 3.14);
        $request->getContent()->willReturn(json_encode($data));
        $this->normalize($request)->shouldReturn($data);
    }
}
