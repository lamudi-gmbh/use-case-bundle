<?php

namespace Lamudi\UseCaseBundle\Handler;

use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\UseCaseInterface;

interface UseCaseHandlerInterface
{
    /**
     * @param UseCaseInterface $useCase
     * @param Request $request
     * @return mixed
     */
    public function handle($useCase, $request);
}
