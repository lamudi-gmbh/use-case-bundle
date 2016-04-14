<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

interface InputProcessorInterface
{
    /**
     * Initializes a Use Case Request object fields based on the input data received. Additional, processor-specific
     * options may be required. Refer to the documentation of the Processor of choice.
     *
     * @param object $request The Use Case Request object to be initialized.
     * @param mixed  $input   Any object that contains input data.
     * @param array  $options An array of configuration options to the Input Processor.
     */
    public function initializeRequest($request, $input, $options = []);
}
