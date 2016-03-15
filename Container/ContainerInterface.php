<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\ServiceNotFoundException;

interface ContainerInterface
{
    /**
     * @param string $name A name under which to store a service.
     * @param string|object $service An object or a string reference to an object, use acceptsReferences to determine which.
     */
    public function set($name, $service);

    /**
     * @param string $name The name of the service.
     * @return object
     * @throws ServiceNotFoundException
     */
    public function get($name);
}
