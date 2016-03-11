<?php

namespace Lamudi\UseCaseBundle\Request\Processor;

use Lamudi\UseCaseBundle\Request\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\HttpFoundation;

class JsonInputProcessor implements InputProcessorInterface
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
     * @param HttpFoundation\Request $input Any object that contains input data.
     * @param array $options An array of options used to create the request object.
     */
    public function initializeRequest($request, $input, $options = array())
    {
        if (!$input instanceof HttpFoundation\Request) {
            return;
        }

        $decoded = $this->jsonDecoder->decode($input->getContent(), 'json');

        foreach ($request as $key => &$property) {
            if (isset($decoded[$key])) {
                $property = $decoded[$key];
            }
        }
    }
}
