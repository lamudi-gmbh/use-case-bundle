<?php

namespace Lamudi\UseCaseBundle\Request\Processor;

use Lamudi\UseCaseBundle\Request\Request;
use Symfony\Component\HttpFoundation;

class HttpInputProcessor implements InputProcessorInterface
{
    const DEFAULT_ORDER = 'GPFCSHA';

    /**
     * Creates a use case request based on the input data received. Additional options may help
     * determine the way to create the use case request object.
     *
     * @param Request $request The use case object to initialize.
     * @param HttpFoundation\Request $input Symfony HTTP request object.
     * @param array $options An array of options used to create the request object.
     */
    public function initializeRequest($request, $input, $options = [])
    {
        if (!isset($options['order'])) {
            $options['order'] = self::DEFAULT_ORDER;
        }

        if ($input instanceof HttpFoundation\Request) {
            $httpRequestData = [
                'G' => $input->query->all(),
                'P' => $input->request->all(),
                'F' => $input->files->all(),
                'C' => $input->cookies->all(),
                'S' => $input->server->all(),
                'H' => $input->headers->all(),
                'A' => $input->attributes->all()
            ];

            $mergedData = [];
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
