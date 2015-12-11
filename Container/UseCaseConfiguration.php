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
    public function getInputConverterName()
    {
        return $this->inputConverterName;
    }

    /**
     * @param string $inputConverterName
     */
    public function setInputConverterName($inputConverterName)
    {
        $this->inputConverterName = $inputConverterName;
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
     */
    public function setInputConverterOptions($inputConverterOptions)
    {
        $this->inputConverterOptions = $inputConverterOptions;
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
     */
    public function setResponseProcessorName($responseProcessorName)
    {
        $this->responseProcessorName = $responseProcessorName;
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
     */
    public function setResponseProcessorOptions($responseProcessorOptions)
    {
        $this->responseProcessorOptions = $responseProcessorOptions;
    }
}
