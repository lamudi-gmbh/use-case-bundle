<?php

namespace Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\UseCaseInterface;

class UseCaseContainer
{
    /**
     * @var UseCaseInterface[]
     */
    private $useCases = array();

    /**
     * @param string $name
     * @return UseCaseInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->useCases)) {
            throw new UseCaseNotFoundException(sprintf('Use case "%s" not found.', $name));
        }

        return $this->useCases[$name];
    }

    /**
     * @param string $name
     * @param UseCaseInterface $useCase
     */
    public function set($name, UseCaseInterface $useCase)
    {
        $this->useCases[$name] = $useCase;
    }
}
