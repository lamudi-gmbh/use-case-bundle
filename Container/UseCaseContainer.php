<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\InputConverterNotFoundException;
use Lamudi\UseCaseBundle\Exception\RequestClassNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Request\Converter\DefaultInputConverter;
use Lamudi\UseCaseBundle\Request\Converter\InputConverterInterface;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Processor\IdentityResponseProcessor;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseContainer
{
    /**
     * @var UseCaseDefinition[]
     */
    private $useCaseDefinitions = array();

    /**
     * @var array
     */
    private $inputConverters = array();

    /**
     * @var ResponseProcessorInterface[]
     */
    private $responseProcessors = array();

    /**
     * @var UseCaseConfiguration
     */
    private $defaultConfiguration;

    /**
     * @param InputConverterInterface    $defaultInputConverter
     * @param ResponseProcessorInterface $defaultResponseProcessor
     */
    public function __construct(
        InputConverterInterface $defaultInputConverter = null,
        ResponseProcessorInterface $defaultResponseProcessor = null
    )
    {
        $this->defaultConfiguration = new UseCaseConfiguration();
        $this->setDefaultInputConverter('default', array());
        $this->setDefaultResponseProcessor('default', array());
        $this->defaultConfiguration->setRequestClass(Request::class);

        $this->setInputConverter('default', $defaultInputConverter ?: new DefaultInputConverter());
        $this->setResponseProcessor('default', $defaultResponseProcessor ?: new IdentityResponseProcessor());
    }

    /**
     * @param string $useCaseName
     * @param mixed $inputData
     * @return mixed
     */
    public function execute($useCaseName, $inputData = null)
    {
        $definition = $this->getDefinition($useCaseName);
        $useCase = $definition->getInstance();
        $request = $this->createUseCaseRequest($definition);

        $processor = $this->getRequestProcessorForUseCase($useCaseName);
        $processorOptions = $this->getResponseProcessorOptionsForUseCase($useCaseName);

        try {
            $this->initializeRequest($request, $inputData, $definition);
            $response = $useCase->execute($request);
            return $processor->processResponse($response, $processorOptions);
        } catch (\Exception $e) {
            return $processor->handleException($e, $processorOptions);
        }
    }

    /**
     * @param Request           $request
     * @param mixed             $inputData
     * @param UseCaseDefinition $definition
     */
    private function initializeRequest(Request $request, $inputData, UseCaseDefinition $definition)
    {
        if ($definition->getInputConverterName()) {
            $converterName = $definition->getInputConverterName();
            $converterOptions = $definition->getInputConverterOptions();
        } else {
            $converterName = $this->defaultConfiguration->getInputConverterName();
            $converterOptions = $this->defaultConfiguration->getInputConverterOptions();
        }

        $converter = $this->getInputConverter($converterName);
        $converter->initializeRequest($request, $inputData, $converterOptions);
    }

    /**
     * @param string $name
     * @return UseCaseInterface
     */
    public function get($name)
    {
        return $this->getDefinition($name)->getInstance();
    }

    /**
     * @param string $name
     * @param UseCaseInterface $useCase
     */
    public function set($name, UseCaseInterface $useCase)
    {
        $this->useCaseDefinitions[$name] = new UseCaseDefinition($name, $useCase);
    }

    /**
     * @param string $name
     * @return InputConverterInterface
     */
    public function getInputConverter($name)
    {
        if (!array_key_exists($name, $this->inputConverters)) {
            throw new InputConverterNotFoundException(sprintf('Input converter "%s" not found.', $name));
        }

        return $this->inputConverters[$name];
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
     * @param string $type
     * @param array $options
     */
    public function setDefaultInputConverter($type, $options)
    {
        $this->defaultConfiguration->setInputConverterName($type);
        $this->defaultConfiguration->setInputConverterOptions($options);
    }

    /**
     * @param string $name
     * @return InputConverterInterface
     */
    public function getResponseProcessor($name)
    {
        if (!array_key_exists($name, $this->responseProcessors)) {
            throw new ResponseProcessorNotFoundException(sprintf('Response processor "%s" not found.', $name));
        }

        return $this->responseProcessors[$name];
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
     * @param string $type
     * @param array $options
     */
    public function setDefaultResponseProcessor($type, $options)
    {
        $this->defaultConfiguration->setResponseProcessorName($type);
        $this->defaultConfiguration->setResponseProcessorOptions($options);
    }

    /**
     * @param string $useCaseName
     * @param string $requestClassName
     */
    public function assignRequestClass($useCaseName, $requestClassName)
    {
        $this->getDefinition($useCaseName)->setRequestClass($requestClassName);
    }

    /**
     * @param string $useCaseName
     * @param string $converterName
     * @param array $options
     */
    public function assignInputConverter($useCaseName, $converterName, $options = array())
    {
        $this->getDefinition($useCaseName)->setInputConverterName($converterName);
        $this->getDefinition($useCaseName)->setInputConverterOptions($options);
    }

    /**
     * @param string $useCaseName
     * @param string $processorName
     * @param array $options
     */
    public function assignResponseProcessor($useCaseName, $processorName, $options = array())
    {
        $this->getDefinition($useCaseName)->setResponseProcessorName($processorName);
        $this->getDefinition($useCaseName)->setResponseProcessorOptions($options);
    }

    /**
     * @param string $useCaseName
     * @return ResponseProcessorInterface
     */
    private function getRequestProcessorForUseCase($useCaseName)
    {
        if ($this->getDefinition($useCaseName)->getResponseProcessorName()) {
            $processorName = $this->getDefinition($useCaseName)->getResponseProcessorName();
        } else {
            $processorName = $this->defaultConfiguration->getResponseProcessorName();
        }

        return $this->getResponseProcessor($processorName);
    }

    /**
     * @param string $useCaseName
     * @return array
     */
    private function getResponseProcessorOptionsForUseCase($useCaseName)
    {
        if ($this->getDefinition($useCaseName)->getResponseProcessorOptions()) {
            return $this->getDefinition($useCaseName)->getResponseProcessorOptions();
        } else {
            return $this->defaultConfiguration->getResponseProcessorOptions();
        }
    }

    /**
     * @param string $name
     * @return UseCaseDefinition
     */
    private function getDefinition($name)
    {
        if (!array_key_exists($name, $this->useCaseDefinitions)) {
            throw new UseCaseNotFoundException(sprintf('Use case "%s" not found.', $name));
        }

        return $this->useCaseDefinitions[$name];
    }

    /**
     * @param UseCaseDefinition $useCaseDefinition
     * @return Request
     */
    private function createUseCaseRequest($useCaseDefinition)
    {
        $requestClass = $useCaseDefinition->getRequestClass() ?: $this->defaultConfiguration->getRequestClass();
        if (!class_exists($requestClass)) {
            throw new RequestClassNotFoundException(sprintf('Class "%s" not found.', $requestClass));
        }

        return new $requestClass;
    }
}
