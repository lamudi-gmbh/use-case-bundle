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
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
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
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
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
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
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
     */
    public function setInputConverterName($inputConverterName)
    {
        $this->configuration->setInputConverterName($inputConverterName);
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
     */
    public function setInputConverterOptions($inputConverterOptions)
    {
        $this->configuration->setInputConverterOptions($inputConverterOptions);
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
     */
    public function setResponseProcessorName($responseProcessorName)
    {
        $this->configuration->setResponseProcessorName($responseProcessorName);
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
     */
    public function setResponseProcessorOptions($responseProcessorOptions)
    {
        $this->configuration->setResponseProcessorOptions($responseProcessorOptions);
    }
}
