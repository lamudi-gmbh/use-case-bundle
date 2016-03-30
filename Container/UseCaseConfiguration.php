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
    private $inputProcessorName;

    /**
     * @var array
     */
    private $inputProcessorOptions = [];

    /**
     * @var string
     */
    private $responseProcessorName;

    /**
     * @var array
     */
    private $responseProcessorOptions = [];

    /**
     * @param array $data
     */
    public function __construct($data = [])
    {
        if (isset($data['input'])) {
            $this->setConfiguration('inputProcessor', $data['input']);
        }
        if (isset($data['response'])) {
            $this->setConfiguration('responseProcessor', $data['response']);
        }
    }

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
    public function getInputProcessorName()
    {
        return $this->inputProcessorName;
    }

    /**
     * @param string $inputProcessorName
     * @return UseCaseConfiguration
     */
    public function setInputProcessorName($inputProcessorName)
    {
        $this->inputProcessorName = $inputProcessorName;
        return $this;
    }

    /**
     * @return array
     */
    public function getInputProcessorOptions()
    {
        return $this->inputProcessorOptions;
    }

    /**
     * @param array $inputProcessorOptions
     * @return UseCaseConfiguration
     */
    public function setInputProcessorOptions($inputProcessorOptions)
    {
        $this->inputProcessorOptions = $inputProcessorOptions;
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

    /**
     * @param string       $field
     * @param string|array $data
     * @return string
     */
    private function setConfiguration($field, $data)
    {
        $nameField = $field . 'Name';
        $optionsField = $field . 'Options';

        if (is_string($data)) {
            $this->$nameField = $data;
        } else {
            if (!isset($data['type'])) {
                throw new \Exception('Missing ' . $field . ' type');
            }

            $this->$nameField = $data['type'];
            unset($data['type']);
            $this->$optionsField = $data;
        }
    }
}
