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
            $httpRequestData = array(
                'GET'        => array('query'     => 'query_value'),
                'POST'       => array('request'   => 'request_value'),
                'FILES'      => array('file'      => 'file_value'),
                'COOKIE'     => array('cookie'    => 'cookie_value'),
                'SERVER'     => array('server'    => 'server_value'),
                'headers'    => array('header'    => 'header_value'),
                'attributes' => array('attribute' => 'attribute_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);

            $request->attribute->shouldBe('attribute_value');
            $request->request->shouldBe('request_value');
            $request->query->shouldBe('query_value');
            $request->server->shouldBe('server_value');
            $request->file->shouldBe('file_value');
            $request->cookie->shouldBe('cookie_value');
            $request->header->shouldBe('header_value');
        }

        public function it_reads_data_from_http_request_with_certain_default_priority(HttpRequest $httpRequest)
        {
            $this->attributesOverrideAll($httpRequest);
            $this->headersOverrideGetPostFilesCookiesAndServer($httpRequest);
            $this->serverOverridesGetPostFilesAndCookies($httpRequest);
            $this->cookiesOverrideGetPostAndFiles($httpRequest);
            $this->filesOverrideGetAndPost($httpRequest);
            $this->postOverridesGet($httpRequest);
        }

        private function initializeHttpRequest(HttpRequest $httpRequest, $data)
        {
            $prophet = new Prophet();
            $attributesBag = $prophet->prophesize(ParameterBag::class);
            $requestBag = $prophet->prophesize(ParameterBag::class);
            $queryBag = $prophet->prophesize(ParameterBag::class);
            $serverBag = $prophet->prophesize(ServerBag::class);
            $filesBag = $prophet->prophesize(FileBag::class);
            $cookiesBag = $prophet->prophesize(ParameterBag::class);
            $headersBag = $prophet->prophesize(HeaderBag::class);

            $attributesBag->all()->willReturn(isset($data['attributes']) ? $data['attributes'] : array());
            $requestBag->all()->willReturn(isset($data['POST']) ? $data['POST'] : array());
            $queryBag->all()->willReturn(isset($data['GET']) ? $data['GET'] : array());
            $serverBag->all()->willReturn(isset($data['SERVER']) ? $data['SERVER'] : array());
            $filesBag->all()->willReturn(isset($data['FILES']) ? $data['FILES'] : array());
            $cookiesBag->all()->willReturn(isset($data['COOKIE']) ? $data['COOKIE'] : array());
            $headersBag->all()->willReturn(isset($data['headers']) ? $data['headers'] : array());

            $httpRequest->attributes = $attributesBag;
            $httpRequest->request = $requestBag;
            $httpRequest->query = $queryBag;
            $httpRequest->server = $serverBag;
            $httpRequest->files = $filesBag;
            $httpRequest->cookies = $cookiesBag;
            $httpRequest->headers = $headersBag;
        }

        /**
         * @param HttpRequest $httpRequest
         * @return array
         */
        private function attributesOverrideAll(HttpRequest $httpRequest)
        {
            $httpRequestData = array(
                'GET' => array('var' => 'query_value'),
                'POST' => array('var' => 'request_value'),
                'FILES' => array('var' => 'file_value'),
                'COOKIE' => array('var' => 'cookie_value'),
                'SERVER' => array('var' => 'server_value'),
                'headers' => array('var' => 'header_value'),
                'attributes' => array('var' => 'attribute_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);
            $request->var->shouldBe('attribute_value');
            return array($httpRequestData, $request);
        }

        /**
         * @param HttpRequest $httpRequest
         */
        private function headersOverrideGetPostFilesCookiesAndServer(HttpRequest $httpRequest)
        {
            $httpRequestData = array(
                'GET' => array('var' => 'query_value'),
                'POST' => array('var' => 'request_value'),
                'FILES' => array('var' => 'file_value'),
                'COOKIE' => array('var' => 'cookie_value'),
                'SERVER' => array('var' => 'server_value'),
                'headers' => array('var' => 'header_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);
            $request->var->shouldBe('header_value');
        }

        /**
         * @param HttpRequest $httpRequest
         */
        private function serverOverridesGetPostFilesAndCookies(HttpRequest $httpRequest)
        {
            $httpRequestData = array(
                'GET' => array('var' => 'query_value'),
                'POST' => array('var' => 'request_value'),
                'FILES' => array('var' => 'file_value'),
                'COOKIE' => array('var' => 'cookie_value'),
                'SERVER' => array('var' => 'server_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);
            $request->var->shouldBe('server_value');
        }

        /**
         * @param HttpRequest $httpRequest
         */
        private function cookiesOverrideGetPostAndFiles(HttpRequest $httpRequest)
        {
            $httpRequestData = array(
                'GET' => array('var' => 'query_value'),
                'POST' => array('var' => 'request_value'),
                'FILES' => array('var' => 'file_value'),
                'COOKIE' => array('var' => 'cookie_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);
            $request->var->shouldBe('cookie_value');
        }

        /**
         * @param HttpRequest $httpRequest
         */
        private function filesOverrideGetAndPost(HttpRequest $httpRequest)
        {
            $httpRequestData = array(
                'GET' => array('var' => 'query_value'),
                'POST' => array('var' => 'request_value'),
                'FILES' => array('var' => 'file_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);
            $request->var->shouldBe('file_value');
        }

        /**
         * @param HttpRequest $httpRequest
         */
        private function postOverridesGet(HttpRequest $httpRequest)
        {
            $httpRequestData = array(
                'GET' => array('var' => 'query_value'),
                'POST' => array('var' => 'request_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);
            $request->var->shouldBe('request_value');
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
        public $var;
    }
}
