<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

use Symfony\Component\HttpFoundation;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

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
     * Decodes the body of the HTTP request as JSON and uses the result to populate the Use Case Request fields.
     * Available options:
     * - map - optional. Allows to specify custom mapping from JSON object fields to Use Case Request fields.
     *     Use an associative array with input array keys as keys and Use Case Request field names as values.
     *
     * @param object                 $request The Use Case Request object to be initialized.
     * @param HttpFoundation\Request $input   Symfony HTTP request.
     * @param array                  $options An array of configuration options.
     *
     * @return object the Use Case Request object is returned for testability purposes.
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
