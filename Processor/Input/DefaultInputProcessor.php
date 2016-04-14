<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

class DefaultInputProcessor implements InputProcessorInterface
{
    /**
     * Does nothing to the use case request.
     *
     * @param object $request
     * @param mixed  $input
     * @param array  $options
     */
    public function initializeRequest($request, $input, $options = [])
    {
    }
}
