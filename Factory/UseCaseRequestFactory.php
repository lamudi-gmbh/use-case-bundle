<?php

namespace Lamudi\UseCaseBundle\Factory;

use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\UseCaseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class UseCaseRequestFactory
{
    /**
     * @var RequestResolver
     */
    private $requestResolver;

    /**
     * @var RequestInitializer
     */
    private $requestInitializer;

    /**
     * @var RequestNormalizer
     */
    private $requestNormalizer;

    /**
     * @param RequestResolver $requestResolver
     * @param RequestNormalizer $requestNormalizer
     * @param RequestInitializer $requestInitializer
     */
    public function __construct(
        RequestResolver $requestResolver, RequestNormalizer $requestNormalizer, RequestInitializer $requestInitializer
    )
    {
        $this->requestResolver = $requestResolver;
        $this->requestInitializer = $requestInitializer;
        $this->requestNormalizer = $requestNormalizer;
    }

    /**
     * @param UseCaseInterface $useCase
     * @param HttpRequest $httpRequest
     * @return Request
     */
    public function createRequest(UseCaseInterface $useCase, $httpRequest)
    {
        $request = $this->requestResolver->resolve($useCase);
        $data = $this->requestNormalizer->normalize($httpRequest);
        $this->requestInitializer->initialize($request, $data);

        return $request;
    }
}