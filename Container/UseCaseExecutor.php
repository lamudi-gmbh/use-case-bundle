<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\RequestClassNotFoundException;
use Lamudi\UseCaseBundle\Exception\ServiceNotFoundException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseExecutor
{
    /**
     * @var UseCaseConfiguration[]
     */
    private $useCaseConfigurations = [];

    /**
     * @var ContainerInterface
     */
    private $useCaseContainer;

    /**
     * @var UseCaseContextResolver
     */
    private $contextResolver;

    /**
     * @param ContainerInterface $useCaseContainer
     * @param UseCaseContextResolver $contextResolver
     */
    public function __construct(ContainerInterface $useCaseContainer, UseCaseContextResolver $contextResolver)
    {
        $this->contextResolver = $contextResolver;
        $this->useCaseContainer = $useCaseContainer;
    }

    /**
     * @param string $useCaseName
     * @param mixed $input
     * @param string|array $context
     * @return mixed
     */
    public function execute($useCaseName, $input = null, $context = null)
    {
        $context = $this->getUseCaseContext($useCaseName, $context);

        $useCase = $this->getUseCase($useCaseName);
        $request = $this->getRequestForUseCase($useCaseName);
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
     * @param string|array $context
     * @return UseCaseContext
     */
    private function getUseCaseContext($useCaseName, $context)
    {
        return $this->contextResolver->resolveContext($context ?: $this->getUseCaseConfiguration($useCaseName));
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
        $requestClass = $this->getUseCaseConfiguration($useCaseName)->getRequestClass() ?: Request::class;
        if (class_exists($requestClass)) {
            return new $requestClass;
        } else {
            throw new RequestClassNotFoundException(sprintf('Class "%s" not found.', $requestClass));
        }
    }

    /**
     * @param string $name
     * @return UseCaseConfiguration
     */
    private function getUseCaseConfiguration($name)
    {
        if (!array_key_exists($name, $this->useCaseConfigurations)) {
            $this->useCaseConfigurations[$name] = new UseCaseConfiguration();
        }

        return $this->useCaseConfigurations[$name];
    }
}
