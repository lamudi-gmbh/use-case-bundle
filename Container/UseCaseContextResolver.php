<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\InputProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\ServiceNotFoundException;
use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;

class UseCaseContextResolver
{
    /**
     * @var string
     */
    private $defaultContext = 'default';

    /**
     * @var ContainerInterface
     */
    private $inputProcessorContainer;

    /**
     * @var ContainerInterface
     */
    private $responseProcessorContainer;

    /**
     * @var UseCaseConfiguration[]
     */
    private $configurations = [];

    /**
     * @param ContainerInterface $inputProcessorContainer
     * @param ContainerInterface $responseProcessorContainer
     */
    public function __construct(ContainerInterface $inputProcessorContainer, ContainerInterface $responseProcessorContainer)
    {
        $this->inputProcessorContainer = $inputProcessorContainer;
        $this->responseProcessorContainer = $responseProcessorContainer;
        $this->configurations[$this->defaultContext] = new UseCaseConfiguration(['input' => 'default', 'output' => 'default']);
    }

    /**
     * @param string $contextName
     * @param string|array|null $inputProcessor
     * @param string|array|null $responseProcessor
     */
    public function setContext($contextName, $inputProcessor = null, $responseProcessor = null)
    {
        $this->configurations[$contextName] = new UseCaseConfiguration([
            'input' => $inputProcessor, 'output' => $responseProcessor
        ]);
    }

    /**
     * @param null|string|array|UseCaseConfiguration $contextConfiguration
     * @return UseCaseContext
     */
    public function resolveContext($contextConfiguration)
    {
        $defaultConfig = $this->getDefaultConfiguration();
        if (is_string($contextConfiguration)) {
            $config = $this->configurations[$contextConfiguration];
        } else {
            $config = $this->resolveConfiguration($contextConfiguration);
            $this->mergeOptionsWithDefaultConfig($config, $defaultConfig);
        }

        $inputProcessorName = $config->getInputProcessorName() ?: $defaultConfig->getInputProcessorName();
        $inputProcessorOptions = $config->getInputProcessorOptions() ?: $defaultConfig->getInputProcessorOptions();
        $responseProcessorName = $config->getResponseProcessorName() ?: $defaultConfig->getResponseProcessorName();
        $responseProcessorOptions = $config->getResponseProcessorOptions() ?: $defaultConfig->getResponseProcessorOptions();

        $context = new UseCaseContext();
        $context->setInputProcessor($this->getInputProcessor($inputProcessorName));
        $context->setResponseProcessor($this->getResponseProcessor($responseProcessorName));
        $context->setInputProcessorOptions($inputProcessorOptions);
        $context->setResponseProcessorOptions($responseProcessorOptions);

        return $context;
    }

    /**
     * @param string $contextName
     */
    public function setDefaultContextName($contextName)
    {
        $this->defaultContext = $contextName;
    }

    /**
     * @return UseCaseConfiguration
     */
    private function getDefaultConfiguration()
    {
        return $this->configurations[$this->defaultContext];
    }

    /**
     * @param string $inputProcessorName
     * @return InputProcessorInterface
     */
    private function getInputProcessor($inputProcessorName)
    {
        try {
            return $this->inputProcessorContainer->get($inputProcessorName);
        } catch (ServiceNotFoundException $e) {
            throw new InputProcessorNotFoundException(sprintf('Input processor "%s" not found.', $inputProcessorName));
        }
    }

    /**
     * @param string $responseProcessorName
     * @return ResponseProcessorInterface
     */
    private function getResponseProcessor($responseProcessorName)
    {
        try {
            return $this->responseProcessorContainer->get($responseProcessorName);
        } catch (ServiceNotFoundException $e) {
            throw new ResponseProcessorNotFoundException(sprintf('Response processor "%s" not found.', $responseProcessorName));
        }
    }

    /**
     * @param mixed $contextConfiguration
     * @return UseCaseConfiguration
     */
    private function resolveConfiguration($contextConfiguration)
    {
        if (is_array($contextConfiguration)) {
            return new UseCaseConfiguration($contextConfiguration);
        } elseif ($contextConfiguration instanceof UseCaseConfiguration) {
            return clone $contextConfiguration;
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param UseCaseConfiguration $config
     * @param UseCaseConfiguration $defaultConfig
     */
    private function mergeOptionsWithDefaultConfig($config, $defaultConfig)
    {
        $config->setInputProcessorOptions(
            array_merge($defaultConfig->getInputProcessorOptions(), $config->getInputProcessorOptions())
        );
        $config->setResponseProcessorOptions(
            array_merge($defaultConfig->getResponseProcessorOptions(), $config->getResponseProcessorOptions())
        );
    }
}
