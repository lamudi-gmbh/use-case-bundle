<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

class CompositeResponseProcessor implements ResponseProcessorInterface
{
    /**
     * @var array
     */
    private $responseProcessors;

    /**
     * Processes the successful outcome of a Use Case execution. Returns the appropriate Output object.
     *
     * @param object $response The Use Case Response object.
     * @param array  $options
     *
     * @return mixed
     */
    public function processResponse($response, $options = [])
    {
        foreach ($this->responseProcessors as $responseProcessorWithOptions) {
            /** @var ResponseProcessorInterface $responseProcessor */
            list($responseProcessor, $processorOptions) = $responseProcessorWithOptions;
            $processorOptions = array_merge($processorOptions, $options);
            $response = $responseProcessor->processResponse($response, $processorOptions);
        }

        return $response;
    }

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
    public function handleException(\Exception $exception, $options = [])
    {
        $output = null;
        foreach ($this->responseProcessors as $responseProcessorWithOptions) {
            /** @var ResponseProcessorInterface $responseProcessor */
            list($responseProcessor, $processorOptions) = $responseProcessorWithOptions;
            if ($exception !== null) {
                $output = $responseProcessor->handleException($exception, $processorOptions);
                $exception = null;
            } else {
                $output = $responseProcessor->processResponse($output, $processorOptions);
            }
        }

        return $output;
    }

    /**
     * @param ResponseProcessorInterface $responseProcessor
     * @param array                      $options
     */
    public function addResponseProcessor(ResponseProcessorInterface $responseProcessor, $options = [])
    {
        $this->responseProcessors[] = [$responseProcessor, $options];
    }
}
