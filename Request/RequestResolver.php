<?php

namespace Lamudi\UseCaseBundle\Request;

use Lamudi\UseCaseBundle\Exception\RequestClassNotFoundException;
use Lamudi\UseCaseBundle\UseCaseInterface;

class RequestResolver
{
    /**
     * Returns a Request object suitable for specified use case.
     *
     * @param UseCaseInterface|string $useCase A use case object, or its class name.
     * @return Request
     */
    public function resolve($useCase)
    {
        $useCaseReflection = new \ReflectionClass($useCase);
        $className = $this->getRequestClassName($useCaseReflection);

        if (class_exists($className)) {
            return new $className;
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
        $className = $this->resolveFromTypeHint($classReflection);

        if (!$className) {
            $className = $this->resolveFromDocBlock($classReflection);
        }

        return $className;
    }

    /**
     * @param \ReflectionClass $classReflection
     * @return string
     */
    private function resolveFromDocBlock($classReflection)
    {
        $docBlock = $classReflection->getMethod('execute')->getDocComment();
        preg_match('/@param\s+(\w+)\s+\$request/', $docBlock, $matches);
        $className = $matches[1];

        if ($className === 'Request') {
            $className = Request::class;
        } else {
            $className = $classReflection->getNamespaceName() . '\Request\\' . $className;
        }

        return $className;
    }

    /**
     * @param \ReflectionClass $classReflection
     * @return string
     */
    private function resolveFromTypeHint($classReflection)
    {
        $method = $classReflection->getMethod('execute');
        $requestClass = $method->getParameters()[0]->getClass();

        if ($requestClass) {
            return $requestClass->getName();
        } else {
            return null;
        }
    }
}
