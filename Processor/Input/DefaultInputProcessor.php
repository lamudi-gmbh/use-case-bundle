<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

use Lamudi\UseCaseBundle\UseCase\Request;

class DefaultInputProcessor implements InputProcessorInterface
{
    /**
     * Does nothing to the use case request.
     *
     * @param Request $request
     * @param mixed $input
     * @param array $options
     */
    public function initializeRequest($request, $input, $options = [])
    {
    }
}
