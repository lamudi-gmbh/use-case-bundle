<?php

namespace Lamudi\UseCaseBundle\Request\Converter;

use Lamudi\UseCaseBundle\Request\InputConverterInterface;
use Lamudi\UseCaseBundle\Request\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\HttpFoundation;

class JsonBodyInputConverter implements InputConverterInterface
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
     * Initializes a use case request based on the input data received. Additional options may help
     * determine the way to initialize the use case request object.
     *
     * @param Request $request The use case request object to be initialized.
     * @param HttpFoundation\Request $inputData Any object that contains input data.
     * @param array $options An array of options used to create the request object.
     */
    public function initializeRequest($request, $inputData, $options = array())
    {
        if (!$inputData instanceof HttpFoundation\Request) {
            return;
        }

        $decoded = $this->jsonDecoder->decode($inputData->getContent(), 'json');

        foreach ($request as $key => &$property) {
            if (isset($decoded[$key])) {
                $property = $decoded[$key];
            }
        }
    }
}