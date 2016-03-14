<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\InputProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\RequestClassNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\ServiceNotFoundException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseExecutor
{
    /**
     * @var UseCaseConfiguration[]
     */
    private $useCaseConfigurations = [];

    /**
     * @var UseCaseConfiguration
     */
    private $defaultConfiguration;

    /**
     * @var ContainerInterface
     */
    private $useCaseContainer;

    /**
     * @var ContainerInterface
     */
    private $inputProcessorContainer;

    /**
     * @var ContainerInterface
     */
    private $responseProcessorContainer;

    /**
     * @param ContainerInterface $useCaseContainer
     * @param ContainerInterface $inputProcessorContainer
     * @param ContainerInterface $responseProcessorContainer
     */
    public function __construct(
        ContainerInterface $useCaseContainer,
        ContainerInterface $inputProcessorContainer,
        ContainerInterface $responseProcessorContainer
    )
    {
        $this->useCaseContainer = $useCaseContainer;
        $this->inputProcessorContainer = $inputProcessorContainer;
        $this->responseProcessorContainer = $responseProcessorContainer;

        $this->defaultConfiguration = new UseCaseConfiguration(['input' => 'default', 'output' => 'default']);
        $this->defaultConfiguration->setRequestClass(Request::class);
    }

    /**
     * @param string $useCaseName
     * @param mixed $input
     * @return mixed
     */
    public function execute($useCaseName, $input = null)
    {
        $context = $this->getUseCaseContext($useCaseName);

        $useCase = $context->getUseCase();
        $request = $context->getUseCaseRequest();
        $inputProcessor = $context->getInputProcessor();
        $inputProcessorOptions = $context->getInputProcessorOptions();
        $responseProcessor = $context->getResponseProcessor();
        $responseProcessorOptions = $context->getResponseProcessorOptions();

        try {
            $inputProcessor->initializeRequest($request, $input, $inputProcessorOptions);
            $response = $useCase->execute($request);
            return $responseProcessor->processResponse($response, $responseProcessorOptions);
        } catch (\Exception $exception) {
            return $responseProcessor->handleException($exception, $responseProcessorOptions);
        }
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
        $this->getUseCaseConfiguration($useCaseName)->setRequestClass($requestClassName);
    }

    /**
     * @param string $useCaseName
     * @param string $processorName
     * @param array $options
     */
    public function assignInputProcessor($useCaseName, $processorName, $options = [])
    {
        $this->getUseCaseConfiguration($useCaseName)->setInputProcessorName($processorName);
        $this->getUseCaseConfiguration($useCaseName)->setInputProcessorOptions($options);
    }

    /**
     * @param string $useCaseName
     * @param string $processorName
     * @param array $options
     */
    public function assignResponseProcessor($useCaseName, $processorName, $options = [])
    {
        $this->getUseCaseConfiguration($useCaseName)->setResponseProcessorName($processorName);
        $this->getUseCaseConfiguration($useCaseName)->setResponseProcessorOptions($options);
    }

    /**
     * @param string $useCaseName
     * @return UseCaseContext
     */
    private function getUseCaseContext($useCaseName)
    {
        $useCase = $this->getUseCase($useCaseName);
        $useCaseRequest = $this->getRequestForUseCase($useCaseName);
        $inputProcessor = $this->getInputProcessorForUseCase($useCaseName);
        $inputProcessorOptions = $this->getInputProcessorOptionsForUseCase($useCaseName);
        $responseProcessor = $this->getResponseProcessorForUseCase($useCaseName);
        $responseProcessorOptions = $this->getResponseProcessorOptionsForUseCase($useCaseName);

        $context = new UseCaseContext();
        $context->setUseCase($useCase);
        $context->setUseCaseRequest($useCaseRequest);
        $context->setInputProcessor($inputProcessor);
        $context->setInputProcessorOptions($inputProcessorOptions);
        $context->setResponseProcessor($responseProcessor);
        $context->setResponseProcessorOptions($responseProcessorOptions);

        return $context;
    }

    /**
     * @param string $name
     * @return UseCaseInterface
     */
    private function getUseCase($name)
    {
        try {
            return $this->useCaseContainer->get($name);
        } catch (ServiceNotFoundException $e) {
            throw new UseCaseNotFoundException(sprintf('Use case "%s" not found.', $name));
        }
    }

    /**
     * @param string $useCaseName
     * @return Request
     */
    private function getRequestForUseCase($useCaseName)
    {
        $requestClass = $this->getUseCaseConfiguration($useCaseName)->getRequestClass();
        if (class_exists($requestClass)) {
            return new $requestClass;
        } else {
            throw new RequestClassNotFoundException(sprintf('Class "%s" not found.', $requestClass));
        }
    }

    /**
     * @param string $useCaseName
     * @return InputProcessorInterface
     */
    private function getInputProcessorForUseCase($useCaseName)
    {
        $processorName = $this->getUseCaseConfiguration($useCaseName)->getInputProcessorName();
        try {
            return $this->inputProcessorContainer->get($processorName);
        } catch (ServiceNotFoundException $e) {
            throw new InputProcessorNotFoundException(sprintf('Input Processor "%s" not found.', $processorName));
        }
    }

    /**
     * @param string $useCaseName
     * @return array
     */
    private function getInputProcessorOptionsForUseCase($useCaseName)
    {
        return $this->getUseCaseConfiguration($useCaseName)->getInputProcessorOptions();
    }

    /**
     * @param string $useCaseName
     * @return ResponseProcessorInterface
     */
    private function getResponseProcessorForUseCase($useCaseName)
    {
        $processorName = $this->getUseCaseConfiguration($useCaseName)->getResponseProcessorName();
        try {
            return $this->responseProcessorContainer->get($processorName);
        } catch (ServiceNotFoundException $e) {
            throw new ResponseProcessorNotFoundException(sprintf('Response Processor "%s" not found.', $processorName));
        }
    }

    /**
     * @param string $useCaseName
     * @return array
     */
    private function getResponseProcessorOptionsForUseCase($useCaseName)
    {
        return $this->getUseCaseConfiguration($useCaseName)->getResponseProcessorOptions();
    }

    /**
     * @param string $name
     * @return UseCaseConfiguration
     */
    private function getUseCaseConfiguration($name)
    {
        if (!array_key_exists($name, $this->useCaseConfigurations)) {
            $this->useCaseConfigurations[$name] = clone $this->defaultConfiguration;
        }

        return $this->useCaseConfigurations[$name];
    }
}
