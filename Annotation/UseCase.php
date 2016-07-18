<?php

namespace Lamudi\UseCaseBundle\Annotation;

use Lamudi\UseCaseBundle\Execution\UseCaseConfiguration;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class UseCase
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var UseCaseConfiguration
     */
    private $config;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $this->setName($data['value']);
        }

        $validOptions = ['value', 'input', 'response'];
        $invalidOptions = array_diff(array_keys($data), $validOptions);
        if (count($invalidOptions) > 0) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported options on UseCase annotation: %s', implode(', ', $invalidOptions)
            ));
        }

        $this->config = new UseCaseConfiguration($data);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getInputProcessorName()
    {
        return $this->config->getInputProcessorName();
    }

    /**
     * @return array
     */
    public function getInputProcessorOptions()
    {
        return $this->config->getInputProcessorOptions();
    }

    /**
     * @return string
     */
    public function getResponseProcessorName()
    {
        return $this->config->getResponseProcessorName();
    }

    /**
     * @return array
     */
    public function getResponseProcessorOptions()
    {
        return $this->config->getResponseProcessorOptions();
    }
}
