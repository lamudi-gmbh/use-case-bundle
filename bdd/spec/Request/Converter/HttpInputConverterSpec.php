<?php

namespace spec\Lamudi\UseCaseBundle\Request\Converter {

    use Foo\Bar\Request\DataFromHttpRequest;
    use Lamudi\UseCaseBundle\Request\Converter\InputConverterInterface;
    use PhpSpec\ObjectBehavior;
    use Prophecy\Argument;
    use Prophecy\Prophet;
    use Symfony\Component\HttpFoundation\FileBag;
    use Symfony\Component\HttpFoundation\HeaderBag;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use Symfony\Component\HttpFoundation\Request as HttpRequest;
    use Symfony\Component\HttpFoundation\ServerBag;

    /**
     * Class HttpInputConverterSpec
     * @mixin \Lamudi\UseCaseBundle\Request\Converter\HttpInputConverter
     */
    class HttpInputConverterSpec extends ObjectBehavior
    {
        function it_is_initializable()
        {
            $this->shouldHaveType('Lamudi\UseCaseBundle\Request\Converter\HttpInputConverter');
        }

        public function it_is_an_input_converter()
        {
            $this->shouldHaveType(InputConverterInterface::class);
        }

        public function it_collects_data_from_http_request(HttpRequest $httpRequest)
        {
            $request = new DataFromHttpRequest();

            $this->initializeHttpRequest($httpRequest);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest($request, $httpRequest, array('request_class' => DataFromHttpRequest::class));

            $request->attribute->shouldBe('attribute_value');
            $request->request->shouldBe('request_value');
            $request->query->shouldBe('query_value');
            $request->server->shouldBe('server_value');
            $request->file->shouldBe('file_value');
            $request->cookie->shouldBe('cookie_value');
            $request->header->shouldBe('header_value');
        }

        private function initializeHttpRequest(HttpRequest $httpRequest)
        {
            $prophet = new Prophet();
            $attributesBag = $prophet->prophesize(ParameterBag::class);
            $requestBag = $prophet->prophesize(ParameterBag::class);
            $queryBag = $prophet->prophesize(ParameterBag::class);
            $serverBag = $prophet->prophesize(ServerBag::class);
            $filesBag = $prophet->prophesize(FileBag::class);
            $cookiesBag = $prophet->prophesize(ParameterBag::class);
            $headersBag = $prophet->prophesize(HeaderBag::class);

            $attributesBag->all()->willReturn(array('attribute' => 'attribute_value'));
            $requestBag->all()->willReturn(array('request' => 'request_value'));
            $queryBag->all()->willReturn(array('query' => 'query_value'));
            $serverBag->all()->willReturn(array('server' => 'server_value'));
            $filesBag->all()->willReturn(array('file' => 'file_value'));
            $cookiesBag->all()->willReturn(array('cookie' => 'cookie_value'));
            $headersBag->all()->willReturn(array('header' => 'header_value'));

            $httpRequest->attributes = $attributesBag;
            $httpRequest->request = $requestBag;
            $httpRequest->query = $queryBag;
            $httpRequest->server = $serverBag;
            $httpRequest->files = $filesBag;
            $httpRequest->cookies = $cookiesBag;
            $httpRequest->headers = $headersBag;
        }
    }
}

namespace Foo\Bar\Request {

    use Lamudi\UseCaseBundle\Request\Request;

    class SomeRequest extends Request {}

    class NotARequest {}

    class DataFromHttpRequest extends Request {
        public $attribute;
        public $request;
        public $query;
        public $server;
        public $file;
        public $cookie;
        public $header;
    }

}