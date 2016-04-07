<?php

namespace Lamudi\UseCaseBundle\Request\Processor;

use Lamudi\UseCaseBundle\Request\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\HttpFoundation;

class JsonInputProcessor extends ArrayInputProcessor implements InputProcessorInterface
{
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(DecoderInterface $jsonDecoder)
    {
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Decodes the body of the HTTP request as JSON and uses the result to populate the use case request.
     * Available options:
     * - map - optional. This option allows to specify custom mapping from the fields found in the JSON object
     *     to the fields in the use case request. Use an associative array with input array keys as keys
     *     and use case request field names as values.
     *
     * @param Request $request The use case request object to be initialized.
     * @param HttpFoundation\Request $input Symfony HTTP request.
     * @param array $options An array of options to the input processor.
     * @return Request returned for testability purposes
     */
    public function initializeRequest($request, $input, $options = [])
    {
        if (!$input instanceof HttpFoundation\Request) {
            return $request;
        }

        $decoded = $this->jsonDecoder->decode($input->getContent(), 'json');

        parent::initializeRequest($request, $decoded, $options);

        return $request;
    }
}
