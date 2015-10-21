<?php

namespace Lamudi\UseCaseBundle\Factory;

use Symfony\Component\HttpFoundation\Request;

class RequestNormalizer
{
    /**
     * @param Request $httpRequest
     * @return array
     */
    public function normalize($httpRequest)
    {
        return json_decode($httpRequest->getContent(), true);
    }
}