<?php

namespace Lamudi\UseCaseBundle\Container;

/**
 * Use this interface to mark containers that accept references to services instead of their instances.
 */
interface ReferenceAcceptingContainerInterface extends ContainerInterface
{
    /**
     * Stores a reference to an item under given name. The retrieval is later delegated to another container.
     *
     * @param string $name A name under which to store an item.
     * @param string $item A reference to the object that will be later retrieved from the containe.
     */
    public function set($name, $item);
}
