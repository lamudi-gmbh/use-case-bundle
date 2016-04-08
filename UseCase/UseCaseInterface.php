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
     * @param Request $request
     * @return Response
     */
    public function execute($request);
}
