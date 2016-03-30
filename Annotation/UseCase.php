<?php

namespace Lamudi\UseCaseBundle\Annotation;

use Lamudi\UseCaseBundle\Container\UseCaseConfiguration;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class UseCase
{
    /**
     * @var string
     */
    private $alias;

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
            $this->setAlias($data['value']);
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
