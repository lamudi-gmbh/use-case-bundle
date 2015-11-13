<?php

namespace Lamudi\UseCaseBundle\Request;

class DefaultInputConverter implements InputConverterInterface
{
    /**
     * Always returns an empty request.
     *
     * @param mixed $inputData Any object that contains input data.
     * @param array $options An array of options used to create the request object.
     * @return Request
     */
    public function createRequest($inputData, $options = array())
    {
        return new Request();
    }
}