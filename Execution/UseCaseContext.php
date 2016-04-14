<?php

namespace Lamudi\UseCaseBundle\Execution;

use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
use Lamudi\UseCaseBundle\Processor\Response\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCase\UseCaseInterface;

/**
 * Use Case Context consists of all the processors resolved for the Use Case execution and the created empty
 * Use Case Request object. All these objects together create a context ready for the use case to be executed in.
 *
 * @package Lamudi\UseCaseBundle\Execution
 */
class UseCaseContext
{
    /**
     * @var UseCaseInterface
     */
    private $useCase;

    /**
     * @var object
     */
    private $useCaseRequest;

    /**
     * @var InputProcessorInterface
     */
    private $inputProcessor;

    /**
     * @var array
     */
    private $inputProcessorOptions = [];

    /**
     * @var ResponseProcessorInterface
     */
    private $responseProcessor;

    /**
     * @var array
     */
    private $responseProcessorOptions = [];

    /**
     * @return UseCaseInterface
     */
    public function getUseCase()
    {
        return $this->useCase;
    }

    /**
     * @param UseCaseInterface $useCase
     * @return UseCaseContext
     */
    public function setUseCase($useCase)
    {
        $this->useCase = $useCase;
        return $this;
    }

    /**
     * @return object
     */
    public function getUseCaseRequest()
    {
        return $this->useCaseRequest;
    }

    /**
     * @param object $useCaseRequest
     * @return UseCaseContext
     */
    public function setUseCaseRequest($useCaseRequest)
    {
        $this->useCaseRequest = $useCaseRequest;
        return $this;
    }

    /**
     * @return InputProcessorInterface
     */
    public function getInputProcessor()
    {
        return $this->inputProcessor;
    }

    /**
     * @param InputProcessorInterface $inputProcessor
     * @return UseCaseContext
     */
    public function setInputProcessor($inputProcessor)
    {
        $this->inputProcessor = $inputProcessor;
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
     * @return UseCaseContext
     */
    public function setInputProcessorOptions($inputProcessorOptions)
    {
        $this->inputProcessorOptions = $inputProcessorOptions;
        return $this;
    }

    /**
     * @return ResponseProcessorInterface
     */
    public function getResponseProcessor()
    {
        return $this->responseProcessor;
    }

    /**
     * @param ResponseProcessorInterface $responseProcessor
     * @return UseCaseContext
     */
    public function setResponseProcessor($responseProcessor)
    {
        $this->responseProcessor = $responseProcessor;
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
     * @return UseCaseContext
     */
    public function setResponseProcessorOptions($responseProcessorOptions)
    {
        $this->responseProcessorOptions = $responseProcessorOptions;
        return $this;
    }
}
