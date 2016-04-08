<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

use Lamudi\UseCaseBundle\UseCase\Request;

interface InputProcessorInterface
{
    /**
     * Initializes a use case request based on the input data received. Additional options may help
     * determine the way to initialize the use case request object.
     *
     * @param Request $request The use case request object to be initialized.
     * @param mixed $input Any object that contains input data.
     * @param array $options An array of options to the input processor.
     */
    public function initializeRequest($request, $input, $options = []);
}
