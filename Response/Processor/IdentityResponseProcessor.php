<?php

namespace Lamudi\UseCaseBundle\Response\Processor;

use Lamudi\UseCaseBundle\Response\Response;

class IdentityResponseProcessor implements ResponseProcessorInterface
{
    /**
     * Always returns an empty response.
     *
     * @param Response $response
     * @param array $options
     * @return mixed
     */
    public function processResponse($response, $options = array())
    {
        return $response;
    }

    /**
     * Always rethrows the exception.
     *
     * @param \Exception $exception
     * @param array $options
     * @return mixed
     */
    public function handleException($exception, $options = array())
    {
        throw $exception;
    }
}