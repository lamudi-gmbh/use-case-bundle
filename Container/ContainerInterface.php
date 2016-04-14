<?php

namespace Lamudi\UseCaseBundle\Container;

interface ContainerInterface
{
    /**
     * Stores the item in the container.
     *
     * @param string $name A name under which to store the item.
     * @param object $item The object to be stored.
     */
    public function set($name, $item);

    /**
     * Returns an item from the container by its name. Throws an exception if an item by given name does not exist.
     *
     * @param string $name
     *
     * @return object
     * @throws ItemNotFoundException
     */
    public function get($name);
}
