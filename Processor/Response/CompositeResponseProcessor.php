<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Processor\Exception\EmptyCompositeProcessorException;

class CompositeResponseProcessor implements ResponseProcessorInterface
{
    /**
     * @var array
     */
    private $responseProcessors;

    /**
     * @var ContainerInterface
     */
    private $responseProcessorContainer;

    /**
     * @param ContainerInterface $responseProcessorContainer
     */
    public function __construct(ContainerInterface $responseProcessorContainer)
    {
        $this->responseProcessorContainer = $responseProcessorContainer;
    }

    /**
     * Executes a chain of Response Processors, passing the result of the previous processing
     * as a Response to the next processor.
     *
     * @param object $response The Use Case Response object.
     * @param array  $options  An associative array where keys are processor names and values are arrays of options.
     *
     * @return mixed
     */
    public function processResponse($response, $options = [])
    {
        $this->throwIfNoProcessorsAdded($options);

        foreach ($options as $responseProcessorName => $responseProcessorOptions) {
            if (is_int($responseProcessorName) && is_string($responseProcessorOptions)) {
                $responseProcessorName = $responseProcessorOptions;
                $responseProcessorOptions = [];
            }

            $responseProcessor = $this->responseProcessorContainer->get($responseProcessorName);
            $response = $responseProcessor->processResponse($response, $responseProcessorOptions);
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
        $this->throwIfNoProcessorsAdded($options);

        $output = null;
        foreach ($options as $responseProcessorName => $responseProcessorOptions) {
            if (is_int($responseProcessorName) && is_string($responseProcessorOptions)) {
                $responseProcessorName = $responseProcessorOptions;
                $responseProcessorOptions = [];
            }

            $responseProcessor = $this->responseProcessorContainer->get($responseProcessorName);
            if ($exception !== null) {
                try {
                    $output = $responseProcessor->handleException($exception, $responseProcessorOptions);
                    $exception = null;
                } catch (\Exception $e) {
                    $exception = $e;
                }
            } else {
                $output = $responseProcessor->processResponse($output, $responseProcessorOptions);
            }
        }

        if ($exception === null) {
            return $output;
        } else {
            throw $exception;
        }
    }

    /**
     * @param ResponseProcessorInterface $responseProcessor
     * @param array                      $options
     */
    public function addResponseProcessor(ResponseProcessorInterface $responseProcessor, $options = [])
    {
        $this->responseProcessors[] = [$responseProcessor, $options];
    }

    private function throwIfNoProcessorsAdded($options)
    {
        if (count($options) == 0) {
            throw new EmptyCompositeProcessorException('No Response Processors have been added.');
        }
    }
}
