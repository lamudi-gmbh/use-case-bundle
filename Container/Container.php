<?php

namespace Lamudi\UseCaseBundle\Container;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $services = [];

    /**
     * @param string $name
     * @param object $service
     */
    public function set($name, $service)
    {
        $this->services[$name] = $service;
    }

    /**
     * @param string $name
     * @return object
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        } else {
            throw $this->createMissingServiceException($name);
        }
    }

    /**
     * @param string $name
     * @return ServiceNotFoundException
     */
    private function createMissingServiceException($name)
    {
        return new ServiceNotFoundException(sprintf('Service "%s" not found.', $name));
    }
}
