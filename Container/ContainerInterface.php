<?php

namespace Lamudi\UseCaseBundle\Container;

interface ContainerInterface
{
    /**
     * @param string $name
     * @param object $service
     */
    public function set($name, $service);

    /**
     * @param string $name
     * @return object
     */
    public function get($name);
}
