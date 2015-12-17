<?php

namespace Lamudi\UseCaseBundle\Container;

/**
 * @package Lamudi\UseCaseBundle\Container
 */
class UseCaseConfiguration
{
    /**
     * @var string
     */
    private $requestClass;

    /**
     * @var string
     */
    private $inputConverterName;

    /**
     * @var array
     */
    private $inputConverterOptions = array();

    /**
     * @var string
     */
    private $responseProcessorName;

    /**
     * @var array
     */
    private $responseProcessorOptions = array();

    /**
     * @return string
     */
    public function getRequestClass()
    {
        return $this->requestClass;
    }

    /**
     * @param string $requestClass
     * @return UseCaseConfiguration
     */
    public function setRequestClass($requestClass)
    {
        $this->requestClass = $requestClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputConverterName()
    {
        return $this->inputConverterName;
    }

    /**
     * @param string $inputConverterName
     * @return UseCaseConfiguration
     */
    public function setInputConverterName($inputConverterName)
    {
        $this->inputConverterName = $inputConverterName;
        return $this;
    }

    /**
     * @return array
     */
    public function getInputConverterOptions()
    {
        return $this->inputConverterOptions;
    }

    /**
     * @param array $inputConverterOptions
     * @return UseCaseConfiguration
     */
    public function setInputConverterOptions($inputConverterOptions)
    {
        $this->inputConverterOptions = $inputConverterOptions;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseProcessorName()
    {
        return $this->responseProcessorName;
    }

    /**
     * @param string $responseProcessorName
     * @return UseCaseConfiguration
     */
    public function setResponseProcessorName($responseProcessorName)
    {
        $this->responseProcessorName = $responseProcessorName;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponseProcessorOptions()
    {
        return $this->responseProcessorOptions;
    }

    /**
     * @param array $responseProcessorOptions
     * @return UseCaseConfiguration
     */
    public function setResponseProcessorOptions($responseProcessorOptions)
    {
        $this->responseProcessorOptions = $responseProcessorOptions;
        return $this;
    }
}
