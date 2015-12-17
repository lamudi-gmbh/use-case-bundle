<?php

namespace Lamudi\UseCaseBundle;

use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Response;

/**
 * This interface serves as a specification of a use case object.
 * It should not be implemented if you want to type hint the $request argument in execute() method
 * (which is the recommended way).
 *
 * @package Lamudi\UseCaseBundle
 */
interface UseCaseInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function execute($request);
}
