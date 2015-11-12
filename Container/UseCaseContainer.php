<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Request\InputConverterInterface;
use Lamudi\UseCaseBundle\Response\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseContainer
{
    /**
     * @var UseCaseInterface[]
     */
    private $useCases = array();

    /**
     * @var InputConverterInterface[]
     */
    private $inputConverters = array();

    /**
     * @var ResponseProcessorInterface[]
     */
    private $responseProcessors = array();

    /**
     * @var array
     */
    private $useCaseInputConverters = array();

    /**
     * @var array
     */
    private $useCaseResponseProcessors = array();

    /**
     * @param string $useCaseName
     * @param mixed $inputData
     * @return mixed
     */
    public function execute($useCaseName, $inputData)
    {
        $useCase = $this->get($useCaseName);

        $inputConverter = $this->getInputConverterForUseCase($useCaseName);
        $converterOptions = $this->getInputConverterOptionsForUseCase($useCaseName);

        $processor = $this->getRequestProcessorForUseCase($useCaseName);
        $processorOptions = $this->getResponseProcessorOptionsForUseCase($useCaseName);

        try {
            $useCaseRequest = $inputConverter->createRequest($inputData, $converterOptions);
            $response = $useCase->execute($useCaseRequest);
            return $processor->processResponse($response, $processorOptions);
        } catch (\Exception $e) {
            return $processor->handleException($e, $processorOptions);
        }
    }

    /**
     * @param string $name
     * @return UseCaseInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->useCases)) {
            throw new UseCaseNotFoundException(sprintf('Use case "%s" not found.', $name));
        }

        return $this->useCases[$name];
    }

    /**
     * @param string $name
     * @param UseCaseInterface $useCase
     */
    public function set($name, UseCaseInterface $useCase)
    {
        $this->useCases[$name] = $useCase;
    }

    /**
     * @param string $name
     * @param InputConverterInterface $inputConverter
     */
    public function setInputConverter($name, InputConverterInterface $inputConverter)
    {
        $this->inputConverters[$name] = $inputConverter;
    }

    /**
     * @param string $name
     * @param ResponseProcessorInterface $responseProcessor
     */
    public function setResponseProcessor($name, ResponseProcessorInterface $responseProcessor)
    {
        $this->responseProcessors[$name] = $responseProcessor;
    }

    /**
     * @param string $useCaseName
     * @param string $converterName
     * @param array $options
     */
    public function assignInputConverter($useCaseName, $converterName, $options = array())
    {
        $this->useCaseInputConverters[$useCaseName] = array('name' => $converterName, 'options' => $options);
    }

    /**
     * @param string $useCaseName
     * @param string $processorName
     * @param array $options
     */
    public function assignResponseProcessor($useCaseName, $processorName, $options = array())
    {
        $this->useCaseResponseProcessors[$useCaseName] = array('name' => $processorName, 'options' => $options);
    }

    /**
     * @param string $useCaseName
     * @return InputConverterInterface
     */
    private function getInputConverterForUseCase($useCaseName)
    {
        if (isset($this->useCaseInputConverters[$useCaseName]['name'])) {
            $converterName = $this->useCaseInputConverters[$useCaseName]['name'];
        } else {
            $converterName = 'default';
        }

        return $this->inputConverters[$converterName];
    }

    /**
     * @param $useCaseName
     * @return mixed
     */
    private function getInputConverterOptionsForUseCase($useCaseName)
    {
        if (isset($this->useCaseInputConverters[$useCaseName])) {
            return $this->useCaseInputConverters[$useCaseName]['options'];
        } else {
            return array();
        }
    }

    /**
     * @param string $useCaseName
     * @return ResponseProcessorInterface
     */
    private function getRequestProcessorForUseCase($useCaseName)
    {
        if (isset($this->useCaseResponseProcessors[$useCaseName])) {
            $processorName = $this->useCaseResponseProcessors[$useCaseName]['name'];
        } else {
            $processorName = 'default';
        }

        return $this->responseProcessors[$processorName];
    }

    /**
     * @param string $useCaseName
     * @return array
     */
    private function getResponseProcessorOptionsForUseCase($useCaseName)
    {
        if (isset($this->useCaseResponseProcessors[$useCaseName])) {
            return $this->useCaseResponseProcessors[$useCaseName]['options'];
        } else {
            return array();
        }
    }
}
