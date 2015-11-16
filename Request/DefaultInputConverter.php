<?php

namespace Lamudi\UseCaseBundle\Request;

class DefaultInputConverter implements InputConverterInterface
{
    /**
     * Does nothing to the passed request.
     *
     * @param Request $request
     * @param mixed $inputData
     * @param array $options
     */
    public function initializeRequest($request, $inputData, $options = array())
    {
    }
}