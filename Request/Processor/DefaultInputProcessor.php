<?php

namespace Lamudi\UseCaseBundle\Request\Processor;

use Lamudi\UseCaseBundle\Request\Request;

class DefaultInputProcessor implements InputProcessorInterface
{
    /**
     * Does nothing to the passed request.
     *
     * @param Request $request
     * @param mixed $input
     * @param array $options
     */
    public function initializeRequest($request, $input, $options = [])
    {
    }
}
