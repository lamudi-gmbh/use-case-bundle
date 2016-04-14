<?php

namespace Lamudi\UseCaseBundle\UseCase;

/**
 * This interface serves as a specification of a use case object.
 * It should not be implemented if you want to type hint the $request argument in execute() method
 * (which is the recommended way).
 */
interface UseCaseInterface
{
    /**
     * Executes the business logic of your application's use case.
     *
     * @param object $request An object containing request data in public fields.
     * @return object An object containing response data in public fields.
     */
    public function execute($request);
}
