<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

class IdentityResponseProcessor implements ResponseProcessorInterface
{
    /**
     * Returns the Use Case Response exactly as it was returned by the Use Case.
     *
     * @param object $response
     * @param array  $options
     *
     * @return object
     */
    public function processResponse($response, $options = [])
    {
        return $response;
    }

    /**
     * Always rethrows the exception.
     *
     * @param \Exception $exception
     * @param array      $options
     *
     * @throws \Exception
     */
    public function handleException(\Exception $exception, $options = [])
    {
        throw $exception;
    }
}
