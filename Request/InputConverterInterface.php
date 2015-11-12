<?php

namespace Lamudi\UseCaseBundle\Request;

interface InputConverterInterface
{
    /**
     * Creates a use case request based on the input data received. Additional options may help
     * determine the way to create the use case request object.
     *
     * @param mixed $inputData Any object that contains input data.
     * @param array $options An array of options used to create the request object.
     * @return Request
     */
    public function createRequest($inputData, $options = array());
}
