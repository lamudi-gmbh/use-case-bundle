<?php

namespace Lamudi\UseCaseBundle\Execution;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Container\ItemNotFoundException;
use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
use Lamudi\UseCaseBundle\Processor\Response\ResponseProcessorInterface;

/**
 * Creates the context for the Use Case execution.
 *
 * @package Lamudi\UseCaseBundle\Execution
 */
class UseCaseContextResolver
{
    /**
     * @var string
     */
    private $defaultContextName = 'default';

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
        $this->configurations[$this->defaultContextName] = new UseCaseConfiguration(['input' => 'default', 'response' => 'default']);
    }

    /**
     * Saves a named context configuration. Both Input Processor and Response Processor configurations
     * are optional and will fall back to default if not specified.
     *
     * @param string            $contextName
     * @param string|array|null $inputProcessor name or configuration
     * @param string|array|null $responseProcessor name or configuration
     */
    public function addContextDefinition($contextName, $inputProcessor = null, $responseProcessor = null)
    {
        $this->configurations[$contextName] = new UseCaseConfiguration([
            'input' => $inputProcessor, 'response' => $responseProcessor
        ]);
    }

    /**
     * Creates a Use Case context based on the specified context configuration. The following configuration formats
     * are supported:
     * - string - resolved to a named Context created using addContextDefinition() method. The default
     *   Processors' options will be overriden by those belonging to the Context.
     * - array or UseCaseConfiguration object - specify the Input Processor, the Response Processor and their options.
     *   An array should come in the same format as the argument to UseCaseConfiguration constructor. In this case,
     *   the Input and Response processor options will be merged with the respective default options. The processors
     *   themselves will fall back to default if not specified.
     *
     * @param string|array|UseCaseConfiguration $contextConfiguration
     *
     * @return UseCaseContext
     * @throws InvalidConfigurationException
     */
    public function resolveContext($contextConfiguration)
    {
        $defaultConfig = $this->getDefaultConfiguration();
        $config = $this->resolveConfiguration($contextConfiguration);

        if (!is_string($contextConfiguration)) {
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
     * Determines which predefined context configuration will be used as a fallback for a lack of more specific settings.
     *
     * @param string $contextName
     */
    public function setDefaultContextName($contextName)
    {
        $this->defaultContextName = $contextName;
    }

    /**
     * Returns the default Use Case Configuration.
     *
     * @return UseCaseConfiguration
     * @throws InvalidConfigurationException
     */
    public function getDefaultConfiguration()
    {
        return $this->getConfigurationByName($this->defaultContextName);
    }

    /**
     * @param string $inputProcessorName
     *
     * @return InputProcessorInterface
     */
    private function getInputProcessor($inputProcessorName)
    {
        try {
            return $this->inputProcessorContainer->get($inputProcessorName);
        } catch (ItemNotFoundException $e) {
            throw new InputProcessorNotFoundException(sprintf('Input processor "%s" not found.', $inputProcessorName));
        }
    }

    /**
     * @param string $responseProcessorName
     *
     * @return ResponseProcessorInterface
     */
    private function getResponseProcessor($responseProcessorName)
    {
        try {
            return $this->responseProcessorContainer->get($responseProcessorName);
        } catch (ItemNotFoundException $e) {
            throw new ResponseProcessorNotFoundException(sprintf('Response processor "%s" not found.', $responseProcessorName));
        }
    }

    /**
     * @param string|array|UseCaseConfiguration $contextConfiguration
     *
     * @return UseCaseConfiguration
     * @throws InvalidConfigurationException
     */
    private function resolveConfiguration($contextConfiguration)
    {
        if (is_string($contextConfiguration)) {
            return $this->getConfigurationByName($contextConfiguration);
        } elseif (is_array($contextConfiguration)) {
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

    /**
     * @param string $contextConfiguration
     *
     * @return UseCaseConfiguration
     * @throws InvalidConfigurationException
     */
    private function getConfigurationByName($contextConfiguration)
    {
        if (array_key_exists($contextConfiguration, $this->configurations)) {
            return $this->configurations[$contextConfiguration];
        } else {
            throw new InvalidConfigurationException(
                sprintf('Context "%s" has not been defined.', $contextConfiguration)
            );
        }
    }
}
