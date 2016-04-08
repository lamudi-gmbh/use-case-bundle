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
        } else {
            throw new \InvalidArgumentException('Missing use case name.');
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
    public function getInputType()
    {
        return $this->config->getInputProcessorName();
    }

    /**
     * @return array
     */
    public function getInputOptions()
    {
        return $this->config->getInputProcessorOptions();
    }

    /**
     * @return string
     */
    public function getResponseType()
    {
        return $this->config->getResponseProcessorName();
    }

    /**
     * @return array
     */
    public function getResponseOptions()
    {
        return $this->config->getResponseProcessorOptions();
    }
}
