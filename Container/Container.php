<?php

namespace Lamudi\UseCaseBundle\Container;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @inheritdoc
     */
    public function set($name, $item)
    {
        $this->items[$name] = $item;
    }

    /**
     * @inheritdoc
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->items)) {
            return $this->items[$name];
        } else {
            throw $this->createMissingItemException($name);
        }
    }

    /**
     * @param string $name
     *
     * @return ItemNotFoundException
     */
    private function createMissingItemException($name)
    {
        return new ItemNotFoundException(sprintf('Item "%s" not found.', $name));
    }
}
