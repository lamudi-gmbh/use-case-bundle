<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\InputProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\RequestClassNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Request\Processor\DefaultInputProcessor;
use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Processor\IdentityResponseProcessor;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseContainer
{
    /**
     * @var UseCaseDefinition[]
     */
    private $useCaseDefinitions = [];

    /**
     * @var array
     */
    private $inputProcessors = [];

    /**
     * @var ResponseProcessorInterface[]
     */
    private $responseProcessors = [];

    /**
     * @var UseCaseConfiguration
     */
    private $defaultConfiguration;

    /**
     * @param InputProcessorInterface    $defaultInputProcessor
     * @param ResponseProcessorInterface $defaultResponseProcessor
     */
    public function __construct(
        InputProcessorInterface $defaultInputProcessor = null,
        ResponseProcessorInterface $defaultResponseProcessor = null
    )
    {
        $this->defaultConfiguration = new UseCaseConfiguration();
        $this->setDefaultInputProcessor('default', []);
        $this->setDefaultResponseProcessor('default', []);
        $this->defaultConfiguration->setRequestClass(Request::class);

        $this->setInputProcessor('default', $defaultInputProcessor ?: new DefaultInputProcessor());
        $this->setResponseProcessor('default', $defaultResponseProcessor ?: new IdentityResponseProcessor());
    }

    /**
     * @param string $useCaseName
     * @param mixed $input
     * @return mixed
     */
    public function execute($useCaseName, $input = null)
    {
        $definition = $this->getDefinition($useCaseName);
        $useCase = $definition->getInstance();
        $request = $this->createUseCaseRequest($definition);

        $processor = $this->getRequestProcessorForUseCase($useCaseName);
        $processorOptions = $this->getResponseProcessorOptionsForUseCase($useCaseName);

        try {
            $this->initializeRequest($request, $input, $definition);
            $response = $useCase->execute($request);
            return $processor->processResponse($response, $processorOptions);
        } catch (\Exception $e) {
            return $processor->handleException($e, $processorOptions);
        }
    }

    /**
     * @param Request           $request
     * @param mixed             $input
     * @param UseCaseDefinition $definition
     */
    private function initializeRequest(Request $request, $input, UseCaseDefinition $definition)
    {
        if ($definition->getInputProcessorName()) {
            $processorName = $definition->getInputProcessorName();
            $processorOptions = $definition->getInputProcessorOptions();
        } else {
            $processorName = $this->defaultConfiguration->getInputProcessorName();
            $processorOptions = $this->defaultConfiguration->getInputProcessorOptions();
        }

        $inputProcessor = $this->getInputProcessor($processorName);
        $inputProcessor->initializeRequest($request, $input, $processorOptions);
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
    public function set($name, $useCase)
    {
        $this->useCaseDefinitions[$name] = new UseCaseDefinition($name, $useCase);
    }

    /**
     * @param string $name
     * @return InputProcessorInterface
     */
    public function getInputProcessor($name)
    {
        if (!array_key_exists($name, $this->inputProcessors)) {
            throw new InputProcessorNotFoundException(sprintf('Input Processor "%s" not found.', $name));
        }

        return $this->inputProcessors[$name];
    }

    /**
     * @param string $name
     * @param InputProcessorInterface $inputProcessor
     */
    public function setInputProcessor($name, InputProcessorInterface $inputProcessor)
    {
        $this->inputProcessors[$name] = $inputProcessor;
    }

    /**
     * @param string $type
     * @param array $options
     */
    public function setDefaultInputProcessor($type, $options)
    {
        $this->defaultConfiguration->setInputProcessorName($type);
        $this->defaultConfiguration->setInputProcessorOptions($options);
    }

    /**
     * @param string $name
     * @return InputProcessorInterface
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
     * @param string $processorName
     * @param array $options
     */
    public function assignInputProcessor($useCaseName, $processorName, $options = [])
    {
        $this->getDefinition($useCaseName)->setInputProcessorName($processorName);
        $this->getDefinition($useCaseName)->setInputProcessorOptions($options);
    }

    /**
     * @param string $useCaseName
     * @param string $processorName
     * @param array $options
     */
    public function assignResponseProcessor($useCaseName, $processorName, $options = [])
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
