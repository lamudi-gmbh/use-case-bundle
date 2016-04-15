<?php

namespace Lamudi\UseCaseBundle\UseCase;

class RequestResolver
{
    /**
     * Returns the name of a Request class suitable for specified Use Case.
     *
     * @param UseCaseInterface|string $useCase A use case object, or its class name.
     *
     * @return string
     * @throws RequestClassNotFoundException
     */
    public function resolve($useCase)
    {
        $useCaseReflection = new \ReflectionClass($useCase);
        $className = $this->getRequestClassName($useCaseReflection);

        if (class_exists($className)) {
            return $className;
        } else {
            throw new RequestClassNotFoundException(sprintf('Class "%s" does not exist.', $className));
        }
    }

    /**
     * @param \ReflectionClass $classReflection
     *
     * @return string
     * @throws RequestClassNotFoundException
     */
    private function getRequestClassName($classReflection)
    {
        try {
            $executeMethod = $classReflection->getMethod('execute');
        } catch (\ReflectionException $e) {
            throw new RequestClassNotFoundException(
                sprintf('Class "%s" does not contain execute() method.', $classReflection->getName())
            );
        }

        if ($executeMethod->getNumberOfParameters() > 0) {
            return $this->resolveTypeHintClass($executeMethod);
        } else {
            return \stdClass::class;
        }
    }

    /**
     * @param \ReflectionMethod $executeMethod
     *
     * @return string
     * @throws RequestClassNotFoundException
     */
    private function resolveTypeHintClass($executeMethod)
    {
        $requestClass = $executeMethod->getParameters()[0]->getClass();
        if ($requestClass) {
            return $requestClass->getName();
        } else {
            throw new RequestClassNotFoundException(sprintf(
                'The argument of the execute() method of class "%s" must be type hinted.',
                $executeMethod->getDeclaringClass()
            ));
        }
    }
}
