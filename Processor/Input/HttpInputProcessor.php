<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

use Lamudi\UseCaseBundle\UseCase\Request;
use Symfony\Component\HttpFoundation;

class HttpInputProcessor extends ArrayInputProcessor implements InputProcessorInterface
{
    const DEFAULT_ORDER = 'GPFCSHA';

    /**
     * Populates the request object by data from the Symfony HTTP request. By default, the variables in the HTTP
     * request are matched to the use case request fields by their names in the following order, later values
     * overriding the older: GET, POST, FILES, COOKIES, SESSION, Headers, Attributes.
     * Available options:
     * - priority - optional, default value: GPFCSHA. Use this option to apply different priority than described above.
     *     The letters correspond to the first letters in the aforementioned variable names. It is possible to omit letters.
     * - map - optional. This option allows to specify custom mapping from the fields found in the HTTP request
     *     to the fields in the use case request. Use an associative array with HTTP request variables names as keys
     *     and use case request field names as values.
     *
     * @param Request $request The use case object to initialize.
     * @param HttpFoundation\Request $input Symfony HTTP request object.
     * @param array $options An array of options to the input processor.
     * @return Request returned for testability purposes
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

            parent::initializeRequest($request, $mergedData, $options);
        }

        return $request;
    }
}
