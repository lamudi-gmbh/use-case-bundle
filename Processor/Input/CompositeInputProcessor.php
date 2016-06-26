<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

use Lamudi\UseCaseBundle\Processor\Exception\EmptyCompositeProcessorException;

class CompositeInputProcessor implements InputProcessorInterface
{
    /**
     * @var array
     */
    private $inputProcessors = [];

    /**
     * Uses a chain of Input Processor to initialize the Use Case Request.
     *
     * @param object $request The Use Case Request object to be initialized.
     * @param mixed  $input   Any object that contains input data.
     * @param array  $options An array of configuration options to the Input Processor.
     */
    public function initializeRequest($request, $input, $options = [])
    {
        if (count($this->inputProcessors) == 0) {
            throw new EmptyCompositeProcessorException('No Input Processors have been added.');
        }

        foreach ($this->inputProcessors as $inputProcessorWithOptions) {
            /** @var InputProcessorInterface $inputProcessor */
            list($inputProcessor, $processorOptions) = $inputProcessorWithOptions;
            $processorOptions = array_merge($processorOptions, $options);
            $inputProcessor->initializeRequest($request, $input, $processorOptions);
        }
    }

    /**
     * @param InputProcessorInterface $inputProcessor
     * @param array                   $options
     */
    public function addInputProcessor(InputProcessorInterface $inputProcessor, $options = [])
    {
        $this->inputProcessors[] = [$inputProcessor, $options];
    }
}
