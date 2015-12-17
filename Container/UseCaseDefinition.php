<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseDefinition
{
    /**
     * @var UseCaseInterface
     */
    private $instance;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var UseCaseConfiguration
     */
    private $configuration;

    /**
     * @param string           $alias
     * @param UseCaseInterface $instance
     */
    public function __construct($alias, $instance)
    {
        $this->alias = $alias;
        $this->instance = $instance;
        $this->configuration = new UseCaseConfiguration();
    }

    /**
     * @return UseCaseInterface
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param UseCaseInterface $instance
     * @return UseCaseDefinition
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return UseCaseDefinition
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestClass()
    {
        return $this->configuration->getRequestClass();
    }

    /**
     * @param string $requestClass
     * @return UseCaseDefinition
     */
    public function setRequestClass($requestClass)
    {
        $this->configuration->setRequestClass($requestClass);
        return $this;
    }

    /**
     * @return UseCaseConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param UseCaseConfiguration $configuration
     * @return UseCaseDefinition
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputConverterName()
    {
        return $this->configuration->getInputConverterName();
    }

    /**
     * @param string $inputConverterName
     * @return UseCaseDefinition
     */
    public function setInputConverterName($inputConverterName)
    {
        $this->configuration->setInputConverterName($inputConverterName);
        return $this;
    }

    /**
     * @return array
     */
    public function getInputConverterOptions()
    {
        return $this->configuration->getInputConverterOptions();
    }

    /**
     * @param array $inputConverterOptions
     * @return UseCaseDefinition
     */
    public function setInputConverterOptions($inputConverterOptions)
    {
        $this->configuration->setInputConverterOptions($inputConverterOptions);
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseProcessorName()
    {
        return $this->configuration->getResponseProcessorName();
    }

    /**
     * @param string $responseProcessorName
     * @return UseCaseDefinition
     */
    public function setResponseProcessorName($responseProcessorName)
    {
        $this->configuration->setResponseProcessorName($responseProcessorName);
        return $this;
    }

    /**
     * @return array
     */
    public function getResponseProcessorOptions()
    {
        return $this->configuration->getResponseProcessorOptions();
    }

    /**
     * @param array $responseProcessorOptions
     * @return UseCaseDefinition
     */
    public function setResponseProcessorOptions($responseProcessorOptions)
    {
        $this->configuration->setResponseProcessorOptions($responseProcessorOptions);
        return $this;
    }
}
