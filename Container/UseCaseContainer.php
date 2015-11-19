<?php

namespace Lamudi\UseCaseBundle\Container;

use Doctrine\Common\Annotations\Reader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
use Lamudi\UseCaseBundle\Exception\InputConverterNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Factory\RequestResolver;
use Lamudi\UseCaseBundle\Request\DefaultInputConverter;
use Lamudi\UseCaseBundle\Request\InputConverterInterface;
use Lamudi\UseCaseBundle\Response\IdentityResponseProcessor;
use Lamudi\UseCaseBundle\Response\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseContainer
{
    /**
     * @var UseCaseInterface[]
     */
    private $useCases = array();

    /**
     * @var array
     */
    private $inputConverters = array();

    /**
     * @var ResponseProcessorInterface[]
     */
    private $responseProcessors = array();

    /**
     * @var array
     */
    private $useCaseInputConverters = array();

    /**
     * @var array
     */
    private $useCaseResponseProcessors = array();

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var RequestResolver
     */
    private $requestResolver;

    /**
     * @var array
     */
    private $defaults = array(
        'input' => array(
            'type' => 'default',
            'options' => array(),
        ),
        'output' => array(
            'type' => 'default',
            'options' => array(),
        ),
    );

    /**
     * @param Reader $annotationReader
     * @param RequestResolver $requestResolver
     * @param InputConverterInterface $defaultInputConverter
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
        $this->setInputConverter('default', $defaultInputConverter ? : new DefaultInputConverter());
        $this->setResponseProcessor('default', $defaultResponseProcessor ? : new IdentityResponseProcessor());
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
        if (!array_key_exists($name, $this->useCases)) {
            throw new UseCaseNotFoundException(sprintf('Use case "%s" not found.', $name));
        }

        return $this->useCases[$name];
    }

    /**
     * @param string $name
     * @param UseCaseInterface $useCase
     */
    public function set($name, UseCaseInterface $useCase)
    {
        $this->useCases[$name] = $useCase;
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
        $this->defaults['input']['type'] = $type;
        $this->defaults['input']['options'] = $options;
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
        $this->defaults['output']['type'] = $type;
        $this->defaults['output']['options'] = $options;
    }

    /**
     * @param string $useCaseName
     * @param string $converterName
     * @param array $options
     */
    public function assignInputConverter($useCaseName, $converterName, $options = array())
    {
        $this->useCaseInputConverters[$useCaseName] = array('name' => $converterName, 'options' => $options);
    }

    /**
     * @param string $useCaseName
     * @param string $processorName
     * @param array $options
     */
    public function assignResponseProcessor($useCaseName, $processorName, $options = array())
    {
        $this->useCaseResponseProcessors[$useCaseName] = array('name' => $processorName, 'options' => $options);
    }

    /**
     * @param string $name
     * @param string $alias
     */
    public function addAlias($name, $alias)
    {
        $this->useCases[$alias] = $this->useCases[$name];
    }

    public function loadSettingsFromAnnotations()
    {
        foreach ($this->useCases as $name => $useCase) {
            $reflection = new \ReflectionClass($useCase);
            $annotations = $this->annotationReader->getClassAnnotations($reflection);

            /** @var UseCaseAnnotation $annotation */
            foreach ($annotations as $annotation) {
                if ($annotation->getAlias()) {
                    $this->addAlias($name, $annotation->getAlias());
                    $name = $annotation->getAlias();
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
        if (isset($this->useCaseInputConverters[$useCaseName]['name'])) {
            $converterName = $this->useCaseInputConverters[$useCaseName]['name'];
        } else {
            $converterName = $this->defaults['input']['type'];
        }

        return $this->getInputConverter($converterName);
    }

    /**
     * @param $useCaseName
     * @return mixed
     */
    private function getInputConverterOptionsForUseCase($useCaseName)
    {
        if (isset($this->useCaseInputConverters[$useCaseName])) {
            return $this->useCaseInputConverters[$useCaseName]['options'];
        } else {
            return $this->defaults['input']['options'];
        }
    }

    /**
     * @param string $useCaseName
     * @return ResponseProcessorInterface
     */
    private function getRequestProcessorForUseCase($useCaseName)
    {
        if (isset($this->useCaseResponseProcessors[$useCaseName])) {
            $processorName = $this->useCaseResponseProcessors[$useCaseName]['name'];
        } else {
            $processorName = $this->defaults['output']['type'];
        }

        return $this->getResponseProcessor($processorName);
    }

    /**
     * @param string $useCaseName
     * @return array
     */
    private function getResponseProcessorOptionsForUseCase($useCaseName)
    {
        if (isset($this->useCaseResponseProcessors[$useCaseName])) {
            return $this->useCaseResponseProcessors[$useCaseName]['options'];
        } else {
            return $this->defaults['output']['options'];
        }
    }
}
