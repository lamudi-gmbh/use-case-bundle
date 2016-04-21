<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

/**
 * Use this interface to mark the response processors that should have access to input data.
 */
interface InputAwareResponseProcessor extends ResponseProcessorInterface
{
    /**
     * Passes the input to the response processor.
     *
     * @param mixed $input
     */
    public function setInput($input = null);
}
