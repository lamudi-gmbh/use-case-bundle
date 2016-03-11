<?php

namespace spec\Lamudi\UseCaseBundle\Request\Processor {

    use Foo\Bar\Request\DataFromHttpRequest;
    use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
    use PhpSpec\ObjectBehavior;
    use Prophecy\Argument;
    use Prophecy\Prophet;
    use Symfony\Component\HttpFoundation\FileBag;
    use Symfony\Component\HttpFoundation\HeaderBag;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use Symfony\Component\HttpFoundation\Request as HttpRequest;
    use Symfony\Component\HttpFoundation\ServerBag;

    /**
     * @mixin \Lamudi\UseCaseBundle\Request\Processor\HttpInputProcessor
     */
    class HttpInputProcessorSpec extends ObjectBehavior
    {
        function it_is_initializable()
        {
            $this->shouldHaveType('Lamudi\UseCaseBundle\Request\Processor\HttpInputProcessor');
        }

        public function it_is_an_input_processor()
        {
            $this->shouldHaveType(InputProcessorInterface::class);
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
                'attrs'      => array('attribute' => 'attribute_value'),
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

        public function it_reads_data_from_http_request_with_given_priority(HttpRequest $httpRequest)
        {
            $this->initializeHttpRequest($httpRequest, array(
                'GET'     => array('var1' => 'G_value_1', 'var2' => 'G_value_2', 'var3' => 'G_value_3'),
                'POST'    => array('var1' => 'P_value_1', 'var2' => 'P_value_2'),
                'FILES'   => array(                       'var2' => 'F_value_2', 'var3' => 'F_value_3'),
                'COOKIE'  => array('var1' => 'C_value_1'),
                'SERVER'  => array('var1' => 'S_value_1',                        'var3' => 'S_value_3'),
                'headers' => array(                       'var2' => 'H_value_2'),
                'attrs'   => array(                                              'var3' => 'A_value_3'),
            ));

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, array('order' => 'GPC'));
            $request->var1->shouldBe('C_value_1');
            $request->var2->shouldBe('P_value_2');
            $request->var3->shouldBe('G_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, array('order' => 'PCG'));
            $request->var1->shouldBe('G_value_1');
            $request->var2->shouldBe('G_value_2');
            $request->var3->shouldBe('G_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, array('order' => 'GCP'));
            $request->var1->shouldBe('P_value_1');
            $request->var2->shouldBe('P_value_2');
            $request->var3->shouldBe('G_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, array('order' => 'PSA'));
            $request->var1->shouldBe('S_value_1');
            $request->var2->shouldBe('P_value_2');
            $request->var3->shouldBe('A_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, array('order' => 'FSCAGH'));
            $request->var1->shouldBe('G_value_1');
            $request->var2->shouldBe('H_value_2');
            $request->var3->shouldBe('G_value_3');
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

            $attributesBag->all()->willReturn(isset($data['attrs']) ? $data['attrs'] : array());
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
                'attrs' => array('var' => 'attribute_value'),
            );
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, array('options' => 'foo'));
            $request->var->shouldBe('attribute_value');
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
        public $var1;
        public $var2;
        public $var3;
    }
}
