<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

interface ResponseProcessorInterface
{
    /**
     * Processes the successful outcome of a Use Case execution. Returns the appropriate Output object.
     *
     * @param object $response The Use Case Response object.
     * @param array  $options
     *
     * @return mixed
     */
    public function processResponse($response, $options = []);

    /**
     * When an exception is thrown during Use Case execution, this method is invoked. It should return an Output
     * appropriate for alternative execution course of the Use Case, or rethrow the exception if it was not the result
     * of such course.
     *
     * @param \Exception $exception
     * @param array      $options
     *
     * @return mixed
     */
    public function handleException(\Exception $exception, $options = []);
}
