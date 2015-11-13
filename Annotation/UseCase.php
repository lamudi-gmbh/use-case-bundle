<?php

namespace Lamudi\UseCaseBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class UseCase
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['alias'])) {
            $this->setAlias($data['alias']);
        }
        if (isset($data['input'])) {
            $this->setInput($data['input']);
        }
        if (isset($data['output'])) {
            $this->setOutput($data['output']);
        }
    }

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $inputType;

    /**
     * @var array
     */
    private $inputOptions = array();

    /**
     * @var string
     */
    private $outputType;

    /**
     * @var array
     */
    private $outputOptions = array();


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
     * @param array|string $input
     */
    public function setInput($input)
    {
        if (is_string($input)) {
            $this->inputType = $input;
        } else {
            if (!isset($input['type'])) {
                throw new \Exception('Missing input type');
            }

            $this->inputType = $input['type'];
            unset($input['type']);
            $this->inputOptions = $input;
        }
    }

    /**
     * @param array|string $input
     */
    public function setOutput($input)
    {
        if (is_string($input)) {
            $this->outputType = $input;
        } else {
            if (!isset($input['type'])) {
                throw new \Exception('Missing output type');
            }

            $this->outputType = $input['type'];
            unset($input['type']);
            $this->outputOptions = $input;
        }
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return $this->inputType;
    }

    /**
     * @return array
     */
    public function getInputOptions()
    {
        return $this->inputOptions;
    }

    /**
     * @return string
     */
    public function getOutputType()
    {
        return $this->outputType;
    }

    /**
     * @return array
     */
    public function getOutputOptions()
    {
        return $this->outputOptions;
    }
}
