<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\UseCase\Response;

class IdentityResponseProcessor implements ResponseProcessorInterface
{
    /**
     * Returns the use case response as it was returned by the use case.
     *
     * @param Response $response
     * @param array $options
     * @return Response
     */
    public function processResponse($response, $options = [])
    {
        return $response;
    }

    /**
     * Always rethrows the exception.
     *
     * @param \Exception $exception
     * @param array $options
     */
    public function handleException($exception, $options = [])
    {
        throw $exception;
    }
}
