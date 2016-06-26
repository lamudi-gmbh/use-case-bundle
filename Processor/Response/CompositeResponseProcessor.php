<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Processor\Exception\EmptyCompositeProcessorException;

class CompositeResponseProcessor implements ResponseProcessorInterface
{
    /**
     * @var array
     */
    private $responseProcessors;

    /**
     * Executes a chain of Response Processors, passing the result of the previous processing
     * as a Response to the next processor.
     *
     * @param object $response The Use Case Response object.
     * @param array  $options
     *
     * @return mixed
     */
    public function processResponse($response, $options = [])
    {
        $this->throwIfNoProcessorsAdded();

        foreach ($this->responseProcessors as $responseProcessorWithOptions) {
            /** @var ResponseProcessorInterface $responseProcessor */
            list($responseProcessor, $processorOptions) = $responseProcessorWithOptions;
            $processorOptions = array_merge($processorOptions, $options);
            $response = $responseProcessor->processResponse($response, $processorOptions);
        }

        return $response;
    }

    /**
     * Uses the first Response Processor to handle the Exception thrown by the Use Case,
     * then processes the Output using remaining Processors.
     *
     * @param \Exception $exception
     * @param array      $options
     *
     * @return mixed
     */
    public function handleException(\Exception $exception, $options = [])
    {
        $this->throwIfNoProcessorsAdded();

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

    private function throwIfNoProcessorsAdded()
    {
        if (count($this->responseProcessors) == 0) {
            throw new EmptyCompositeProcessorException('No Response Processors have been added.');
        }
    }
}
