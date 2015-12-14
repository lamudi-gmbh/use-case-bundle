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
        if (isset($data['value'])) {
            $this->setAlias($data['value']);
        } else {
            throw new \InvalidArgumentException('Missing use case name.');
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
     * @var string|array
     */
    private $input = '';

    /**
     * @var string|array
     */
    private $output = '';

    /**
     * @return string
     */
    public function getName()
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
        $this->input = $input;
    }

    /**
     * @param array|string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return $this->getType('input');
    }

    /**
     * @return array
     */
    public function getInputOptions()
    {
        return $this->getOptions($this->input);
    }

    /**
     * @return string
     */
    public function getOutputType()
    {
        return $this->getType('output');
    }

    /**
     * @return array
     */
    public function getOutputOptions()
    {
        return $this->getOptions($this->output);
    }

    /**
     * @param string|array $fieldName
     * @return string
     */
    private function getType($fieldName)
    {
        $field = $this->$fieldName;

        if (is_string($field)) {
            return $field;
        } else {
            if (!isset($field['type'])) {
                throw new \Exception('Missing ' . $fieldName . ' type');
            }

            return $field['type'];
        }
    }

    /**
     * @param string|array $field
     * @return array
     */
    private function getOptions($field)
    {
        if (is_string($field)) {
            return array();
        } else {
            unset($field['type']);
            return $field;
        }
    }
}
