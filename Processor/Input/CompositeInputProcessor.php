<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Processor\Exception\EmptyCompositeProcessorException;

class CompositeInputProcessor implements InputProcessorInterface
{
    /**
     * @var ContainerInterface
     */
    private $inputProcessorContainer;

    /**
     * @param ContainerInterface $inputProcessorContainer
     */
    public function __construct(ContainerInterface $inputProcessorContainer)
    {
        $this->inputProcessorContainer = $inputProcessorContainer;
    }

    /**
     * Uses a chain of Input Processor to initialize the Use Case Request.
     *
     * @param object $request The Use Case Request object to be initialized.
     * @param mixed  $input   Any object that contains input data.
     * @param array  $options An associative array where keys are processor names and values are arrays of options.
     */
    public function initializeRequest($request, $input, $options = [])
    {
        if (count($options) == 0) {
            throw new EmptyCompositeProcessorException('No Input Processors have been configured.');
        }

        foreach ($options as $inputProcessorName => $inputProcessorOptions) {
            if (is_int($inputProcessorName) && is_string($inputProcessorOptions)) {
                $inputProcessorName = $inputProcessorOptions;
                $inputProcessorOptions = [];
            }

            $inputProcessor = $this->inputProcessorContainer->get($inputProcessorName);
            $inputProcessor->initializeRequest($request, $input, $inputProcessorOptions);
        }
    }
}
