<?php

namespace Lamudi\UseCaseBundle\Response\Processor;

use Lamudi\UseCaseBundle\Response\Response;

interface ResponseProcessorInterface
{
    /**
     * Processes the successful outcome of a use case execution. Returns any object that
     * satisfies the environment in which the use case is executed.
     *
     * @param Response $response
     * @param array $options
     * @return mixed
     */
    public function processResponse($response, $options = []);

    /**
     * When an exception is thrown during use case execution, this method is invoked
     *
     * @param \Exception $exception
     * @param array $options
     * @return mixed
     */
    public function handleException($exception, $options = []);
}
