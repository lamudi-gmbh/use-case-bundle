<?php

namespace Lamudi\UseCaseBundle\Container;

use Doctrine\Common\Annotations\Reader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
use Lamudi\UseCaseBundle\Exception\InputConverterNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Factory\RequestResolver;
use Lamudi\UseCaseBundle\Request\Converter\DefaultInputConverter;
use Lamudi\UseCaseBundle\Request\Converter\InputConverterInterface;
use Lamudi\UseCaseBundle\Response\Processor\IdentityResponseProcessor;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseContainer
{
    /**
     * @var UseCaseDefinition[]
     */
    private $useCaseDefinitions = array();

    /**
     * @var array
     */
    private $inputConverters = array();

    /**
     * @var ResponseProcessorInterface[]
     */
    private $responseProcessors = array();

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var RequestResolver
     */
    private $requestResolver;

    /**
     * @var UseCaseConfiguration
     */
    private $defaultConfiguration;

    /**
     * @param Reader                     $annotationReader
     * @param RequestResolver            $requestResolver
     * @param InputConverterInterface    $defaultInputConverter
     * @param ResponseProcessorInterface $defaultResponseProcessor
     */
    public function __construct(
        Reader $annotationReader,
        RequestResolver $requestResolver,
        InputConverterInterface $defaultInputConverter = null,
        ResponseProcessorInterface $defaultResponseProcessor = null
    )
    {
        $this->annotationReader = $annotationReader;
        $this->requestResolver = $requestResolver;

        $this->defaultConfiguration = new UseCaseConfiguration();
        $this->setDefaultInputConverter('default', array());
        $this->setDefaultResponseProcessor('default', array());

        $this->setInputConverter('default', $defaultInputConverter ?: new DefaultInputConverter());
        $this->setResponseProcessor('default', $defaultResponseProcessor ?: new IdentityResponseProcessor());
    }

    /**
     * @param string $useCaseName
     * @param mixed $inputData
     * @return mixed
     */
    public function execute($useCaseName, $inputData = null)
    {
        $useCase = $this->get($useCaseName);
        $request = $this->requestResolver->resolve($useCase);

        $inputConverter = $this->getInputConverterForUseCase($useCaseName);
        $converterOptions = $this->getInputConverterOptionsForUseCase($useCaseName);

        $processor = $this->getRequestProcessorForUseCase($useCaseName);
        $processorOptions = $this->getResponseProcessorOptionsForUseCase($useCaseName);

        try {
            $inputConverter->initializeRequest($request, $inputData, $converterOptions);
            $response = $useCase->execute($request);
            return $processor->processResponse($response, $processorOptions);
        } catch (\Exception $e) {
            return $processor->handleException($e, $processorOptions);
        }
    }

    /**
     * @param string $name
     * @return UseCaseInterface
     */
    public function get($name)
    {
        return $this->getDefinition($name)->getInstance();
    }

    /**
     * @param string $name
     * @param UseCaseInterface $useCase
     */
    public function set($name, UseCaseInterface $useCase)
    {
        $this->useCaseDefinitions[$name] = new UseCaseDefinition($name, $useCase);
    }

    /**
     * @param string $name
     * @return InputConverterInterface
     */
    public function getInputConverter($name)
    {
        if (!array_key_exists($name, $this->inputConverters)) {
            throw new InputConverterNotFoundException(sprintf('Input converter "%s" not found.', $name));
        }

        return $this->inputConverters[$name];
    }

    /**
     * @param string $name
     * @param InputConverterInterface $inputConverter
     */
    public function setInputConverter($name, InputConverterInterface $inputConverter)
    {
        $this->inputConverters[$name] = $inputConverter;
    }

    /**
     * @param string $type
     * @param array $options
     */
    public function setDefaultInputConverter($type, $options)
    {
        $this->defaultConfiguration->setInputConverterName($type);
        $this->defaultConfiguration->setInputConverterOptions($options);
    }

    /**
     * @param string $name
     * @return InputConverterInterface
     */
    public function getResponseProcessor($name)
    {
        if (!array_key_exists($name, $this->responseProcessors)) {
            throw new ResponseProcessorNotFoundException(sprintf('Response processor "%s" not found.', $name));
        }

        return $this->responseProcessors[$name];
    }

    /**
     * @param string $name
     * @param ResponseProcessorInterface $responseProcessor
     */
    public function setResponseProcessor($name, ResponseProcessorInterface $responseProcessor)
    {
        $this->responseProcessors[$name] = $responseProcessor;
    }

    /**
     * @param string $type
     * @param array $options
     */
    public function setDefaultResponseProcessor($type, $options)
    {
        $this->defaultConfiguration->setResponseProcessorName($type);
        $this->defaultConfiguration->setResponseProcessorOptions($options);
    }

    /**
     * @param string $useCaseName
     * @param string $converterName
     * @param array $options
     */
    public function assignInputConverter($useCaseName, $converterName, $options = array())
    {
        $this->getDefinition($useCaseName)->setInputConverterName($converterName);
        $this->getDefinition($useCaseName)->setInputConverterOptions($options);
    }

    /**
     * @param string $useCaseName
     * @param string $processorName
     * @param array $options
     */
    public function assignResponseProcessor($useCaseName, $processorName, $options = array())
    {
        $this->getDefinition($useCaseName)->setResponseProcessorName($processorName);
        $this->getDefinition($useCaseName)->setResponseProcessorOptions($options);
    }

    public function loadSettingsFromAnnotations()
    {
        foreach ($this->useCaseDefinitions as $name => $definition) {
            $useCase = $definition->getInstance();
            $reflection = new \ReflectionClass($useCase);
            $annotations = $this->annotationReader->getClassAnnotations($reflection);

            /** @var UseCaseAnnotation $annotation */
            foreach ($annotations as $annotation) {
                if (!$annotation instanceof UseCaseAnnotation) {
                    continue;
                }

                if ($annotation->getAlias()) {
                    $name = $annotation->getAlias();
                    $this->set($name, $useCase);
                }
                if ($annotation->getInputType()) {
                    $this->assignInputConverter($name, $annotation->getInputType(), $annotation->getInputOptions());
                }
                if ($annotation->getOutputType()) {
                    $this->assignResponseProcessor($name, $annotation->getOutputType(), $annotation->getOutputOptions());
                }
            }
        }
    }

    /**
     * @param string $useCaseName
     * @return InputConverterInterface
     */
    private function getInputConverterForUseCase($useCaseName)
    {
        if ($this->getDefinition($useCaseName)->getInputConverterName()) {
            $converterName = $this->getDefinition($useCaseName)->getInputConverterName();
        } else {
            $converterName = $this->defaultConfiguration->getInputConverterName();
        }

        return $this->getInputConverter($converterName);
    }

    /**
     * @param string $useCaseName
     * @return mixed
     */
    private function getInputConverterOptionsForUseCase($useCaseName)
    {
        if ($this->getDefinition($useCaseName)->getInputConverterOptions()) {
            return $this->getDefinition($useCaseName)->getInputConverterOptions();
        } else {
            return $this->defaultConfiguration->getInputConverterOptions();
        }
    }

    /**
     * @param string $useCaseName
     * @return ResponseProcessorInterface
     */
    private function getRequestProcessorForUseCase($useCaseName)
    {
        if ($this->getDefinition($useCaseName)->getResponseProcessorName()) {
            $processorName = $this->getDefinition($useCaseName)->getResponseProcessorName();
        } else {
            $processorName = $this->defaultConfiguration->getResponseProcessorName();
        }

        return $this->getResponseProcessor($processorName);
    }

    /**
     * @param string $useCaseName
     * @return array
     */
    private function getResponseProcessorOptionsForUseCase($useCaseName)
    {
        if ($this->getDefinition($useCaseName)->getResponseProcessorOptions()) {
            return $this->getDefinition($useCaseName)->getResponseProcessorOptions();
        } else {
            return $this->defaultConfiguration->getResponseProcessorOptions();
        }
    }

    /**
     * @param string $name
     * @return UseCaseDefinition
     */
    private function getDefinition($name)
    {
        if (!array_key_exists($name, $this->useCaseDefinitions)) {
            throw new UseCaseNotFoundException(sprintf('Use case "%s" not found.', $name));
        }

        return $this->useCaseDefinitions[$name];
    }
}
