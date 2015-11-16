<?php

namespace Lamudi\UseCaseBundle\Factory;

use Lamudi\UseCaseBundle\Exception\RequestClassNotFoundException;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\UseCaseInterface;

class RequestResolver
{
    /**
     * @param UseCaseInterface $useCase
     * @return Request
     */
    public function resolve($useCase)
    {
        $useCaseReflection = new \ReflectionClass($useCase);
        $className = $this->getRequestClassName($useCaseReflection);
        $requestClassFullName = $this->getRequestFullyQualifiedName($useCaseReflection, $className);

        if (class_exists($requestClassFullName)) {
            return new $requestClassFullName;
        } elseif ($className === 'Request') {
            return new Request();
        } else {
            throw new RequestClassNotFoundException();
        }
    }

    /**
     * @param \ReflectionClass $classReflection
     * @return string
     */
    private function getRequestClassName($classReflection)
    {
        $docBlock = $classReflection->getMethod('execute')->getDocComment();
        preg_match('/@param\s+(\w+)\s+\$request/', $docBlock, $matches);
        return $matches[1];
    }

    /**
     * @param \ReflectionClass $useCaseReflection
     * @param string $className
     * @return string
     */
    private function getRequestFullyQualifiedName($useCaseReflection, $className)
    {
        return $useCaseReflection->getNamespaceName() . '\Request\\' . $className;
    }
}