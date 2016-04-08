<?php

namespace Lamudi\UseCaseBundle\Container;

use Symfony\Component\DependencyInjection as DI;

class DelegatingContainer implements ReferenceAcceptingContainerInterface
{
    /**
     * @var array
     */
    private $references = [];

    /**
     * @var DI\ContainerInterface
     */
    private $symfonyContainer;

    /**
     * @param DI\ContainerInterface $symfonyContainer
     */
    public function __construct(DI\ContainerInterface $symfonyContainer)
    {
        $this->symfonyContainer = $symfonyContainer;
    }

    /**
     * @param string $name
     * @param object $service
     */
    public function set($name, $service)
    {
        $this->references[$name] = $service;
    }

    /**
     * @param string $name
     * @return object
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->references)) {
            throw new ServiceNotFoundException(sprintf('Service "%s" not found.', $name));
        }

        try {
            return $this->symfonyContainer->get($this->references[$name]);
        } catch (DI\Exception\ServiceNotFoundException $e) {
            throw new ServiceNotFoundException(
                sprintf('Reference "%s" points to a non-existent service "%s".', $name, $this->references[$name])
            );
        }
    }
}
