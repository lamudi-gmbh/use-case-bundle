<?php

namespace Lamudi\UseCaseBundle\Execution;

/**
 * Provides parameters necessary for the execution of the use case using the Use Case Executor.
 *
 * @package Lamudi\UseCaseBundle\Execution
 */
class UseCaseConfiguration
{
    /**
     * @var string
     */
    private $requestClassName;

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
     * Constructs a Use Case Configuration based on provided data. All parameters optional:
     * - input - string (Input Converter name) or array. In latter case, the Input Converter name must be provided under
     *   "type" key.
     * - response - string (Response Converter name) or array. In latter case, the Response Converter name
     *   must be provided under "type" key.
     *
     * @param array $data
     *
     * @throws InvalidConfigurationException
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
    public function getRequestClassName()
    {
        return $this->requestClassName;
    }

    /**
     * @param string $requestClassName
     *
     * @return UseCaseConfiguration
     */
    public function setRequestClassName($requestClassName)
    {
        $this->requestClassName = $requestClassName;
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
     *
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
     *
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
     *
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
     *
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
     *
     * @return string
     * @throws InvalidConfigurationException
     */
    private function setConfiguration($field, $data)
    {
        $nameField = $field . 'Name';
        $optionsField = $field . 'Options';

        if (is_string($data)) {
            $this->$nameField = $data;
        } else {
            $this->$nameField = 'composite';
            $this->$optionsField = $data;
        }
    }
}
