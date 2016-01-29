<?php

namespace Lamudi\UseCaseBundle\Request\Converter;

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
    public function initializeRequest($request, $inputData, $options = array('order' => 'GPFCSHA'))
    {
        if ($inputData instanceof HttpFoundation\Request) {
            $httpRequestData = array(
                'G' => $inputData->query->all(),
                'P' => $inputData->request->all(),
                'F' => $inputData->files->all(),
                'C' => $inputData->cookies->all(),
                'S' => $inputData->server->all(),
                'H' => $inputData->headers->all(),
                'A' => $inputData->attributes->all()
            );

            $mergedData = array();
            for ($i = 0; $i < strlen($options['order']); $i++) {
                $mergedData = array_merge($mergedData, $httpRequestData[$options['order'][$i]]);
            }

            foreach ($request as $key => &$property) {
                if (isset($mergedData[$key])) {
                    $property = $mergedData[$key];
                }
            }
        }

        return $request;
    }
}
