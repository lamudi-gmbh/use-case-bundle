<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Input {

    use Foo\Bar\Request\DataFromHttpRequest;
    use Foo\Bar\Request\SpecificRequest;
    use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
    use PhpSpec\ObjectBehavior;
    use Prophecy\Argument;
    use Prophecy\Prophet;
    use Symfony\Component\HttpFoundation\FileBag;
    use Symfony\Component\HttpFoundation\HeaderBag;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use Symfony\Component\HttpFoundation\Request as HttpRequest;
    use Symfony\Component\HttpFoundation\ServerBag;

    /**
     * @mixin \Lamudi\UseCaseBundle\Processor\Input\HttpInputProcessor
     */
    class HttpInputProcessorSpec extends ObjectBehavior
    {
        function it_is_initializable()
        {
            $this->shouldHaveType('Lamudi\UseCaseBundle\Processor\Input\HttpInputProcessor');
        }

        public function it_is_an_input_processor()
        {
            $this->shouldHaveType(InputProcessorInterface::class);
        }

        public function it_collects_data_from_http_request(HttpRequest $httpRequest)
        {
            $httpRequestData = [
                'GET'     => ['query' => 'query_value'],
                'POST'    => ['request' => 'request_value'],
                'FILES'   => ['file' => 'file_value'],
                'COOKIE'  => ['cookie' => 'cookie_value'],
                'SERVER'  => ['server' => 'server_value'],
                'headers' => ['header' => 'header_value'],
                'attrs'   => ['attribute' => 'attribute_value'],
            ];
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
            $this->initializeHttpRequest($httpRequest, [
                'GET'     => ['var1' => 'G_value_1', 'var2' => 'G_value_2', 'var3' => 'G_value_3'],
                'POST'    => ['var1' => 'P_value_1', 'var2' => 'P_value_2'],
                'FILES'   => [                       'var2' => 'F_value_2', 'var3' => 'F_value_3'],
                'COOKIE'  => ['var1' => 'C_value_1'],
                'SERVER'  => ['var1' => 'S_value_1',                        'var3' => 'S_value_3'],
                'headers' => [                       'var2' => 'H_value_2'],
                'attrs'   => [                                              'var3' => 'A_value_3'],
            ]
            );

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, ['order' => 'GPC']);
            $request->var1->shouldBe('C_value_1');
            $request->var2->shouldBe('P_value_2');
            $request->var3->shouldBe('G_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, ['order' => 'PCG']);
            $request->var1->shouldBe('G_value_1');
            $request->var2->shouldBe('G_value_2');
            $request->var3->shouldBe('G_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, ['order' => 'GCP']);
            $request->var1->shouldBe('P_value_1');
            $request->var2->shouldBe('P_value_2');
            $request->var3->shouldBe('G_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, ['order' => 'PSA']);
            $request->var1->shouldBe('S_value_1');
            $request->var2->shouldBe('P_value_2');
            $request->var3->shouldBe('A_value_3');

            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, ['order' => 'FSCAGH']);
            $request->var1->shouldBe('G_value_1');
            $request->var2->shouldBe('H_value_2');
            $request->var3->shouldBe('G_value_3');
        }

        public function it_maps_fields_from_array_to_object_using_custom_mappings(HttpRequest $httpRequest)
        {
            $this->initializeHttpRequest($httpRequest, [
                'GET' => ['q' => 'cheap hotels', 'p' => 3],
                'COOKIE' => ['PHPSESSID' => 'asd123'],
                'SERVER' => ['REMOTE_ADDR' => '127.0.0.1']
            ]);

            $options = [
                'map' => [
                    'q'           => 'searchQuery',
                    'p'           => 'pageNumber',
                    'PHPSESSID'   => 'sessionId',
                    'REMOTE_ADDR' => 'ipAddress'
                ]
            ];

            /** @var SpecificRequest $request */
            $request = $this->initializeRequest(new SpecificRequest(), $httpRequest, $options);
            $request->searchQuery->shouldBe('cheap hotels');
            $request->pageNumber->shouldBe(3);
            $request->sessionId->shouldBe('asd123');
            $request->ipAddress->shouldBe('127.0.0.1');
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

            $attributesBag->all()->willReturn(isset($data['attrs']) ? $data['attrs'] : []);
            $requestBag->all()->willReturn(isset($data['POST']) ? $data['POST'] : []);
            $queryBag->all()->willReturn(isset($data['GET']) ? $data['GET'] : []);
            $serverBag->all()->willReturn(isset($data['SERVER']) ? $data['SERVER'] : []);
            $filesBag->all()->willReturn(isset($data['FILES']) ? $data['FILES'] : []);
            $cookiesBag->all()->willReturn(isset($data['COOKIE']) ? $data['COOKIE'] : []);
            $headersBag->all()->willReturn(isset($data['headers']) ? $data['headers'] : []);

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
            $httpRequestData = [
                'GET'     => ['var' => 'query_value'],
                'POST'    => ['var' => 'request_value'],
                'FILES'   => ['var' => 'file_value'],
                'COOKIE'  => ['var' => 'cookie_value'],
                'SERVER'  => ['var' => 'server_value'],
                'headers' => ['var' => 'header_value'],
                'attrs'   => ['var' => 'attribute_value'],
            ];
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest, ['options' => 'foo']);
            $request->var->shouldBe('attribute_value');
        }

        /**
         * @param HttpRequest $httpRequest
         */
        private function headersOverrideGetPostFilesCookiesAndServer(HttpRequest $httpRequest)
        {
            $httpRequestData = [
                'GET'     => ['var' => 'query_value'],
                'POST'    => ['var' => 'request_value'],
                'FILES'   => ['var' => 'file_value'],
                'COOKIE'  => ['var' => 'cookie_value'],
                'SERVER'  => ['var' => 'server_value'],
                'headers' => ['var' => 'header_value'],
            ];
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
            $httpRequestData = [
                'GET'    => ['var' => 'query_value'],
                'POST'   => ['var' => 'request_value'],
                'FILES'  => ['var' => 'file_value'],
                'COOKIE' => ['var' => 'cookie_value'],
                'SERVER' => ['var' => 'server_value'],
            ];
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
            $httpRequestData = [
                'GET'    => ['var' => 'query_value'],
                'POST'   => ['var' => 'request_value'],
                'FILES'  => ['var' => 'file_value'],
                'COOKIE' => ['var' => 'cookie_value'],
            ];
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
            $httpRequestData = [
                'GET'   => ['var' => 'query_value'],
                'POST'  => ['var' => 'request_value'],
                'FILES' => ['var' => 'file_value'],
            ];
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
            $httpRequestData = [
                'GET'  => ['var' => 'query_value'],
                'POST' => ['var' => 'request_value'],
            ];
            $this->initializeHttpRequest($httpRequest, $httpRequestData);

            /** @var DataFromHttpRequest $request */
            $request = $this->initializeRequest(new DataFromHttpRequest(), $httpRequest);
            $request->var->shouldBe('request_value');
        }
    }
}

namespace Foo\Bar\Request {

    class SomeRequest {}

    class DataFromHttpRequest
    {
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

    class SpecificRequest
    {
        public $searchQuery;
        public $pageNumber;
        public $sessionId;
        public $ipAddress;
    }
}
