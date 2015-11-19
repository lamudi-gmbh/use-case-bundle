<?php

namespace Lamudi\UseCaseBundle\Request\Converter;

use Lamudi\UseCaseBundle\Request\Converter\InputConverterInterface;
use Lamudi\UseCaseBundle\Request\Request;
use Symfony\Component\HttpFoundation;

class HttpInputConverter implements InputConverterInterface
{
    /**
     * Creates a use case request based on the input data received. Additional options may help
     * determine the way to create the use case request object.
     *
     * @param Request $request The use case object to initialize.
     * @param HttpFoundation\Request $inputData Symfony HTTP request object.
     * @param array $options An array of options used to create the request object.
     */
    public function initializeRequest($request, $inputData, $options = array())
    {
        if ($inputData instanceof HttpFoundation\Request) {
            $httpRequestData = array_merge(
                $inputData->query->all(),
                $inputData->request->all(),
                $inputData->files->all(),
                $inputData->cookies->all(),
                $inputData->server->all(),
                $inputData->headers->all(),
                $inputData->attributes->all()
            );

            foreach ($request as $key => &$property) {
                if (isset($httpRequestData[$key])) {
                    $property = $httpRequestData[$key];
                }
            }
        }

        return $request;
    }
}
