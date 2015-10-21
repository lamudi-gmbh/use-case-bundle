<?php

namespace Lamudi\UseCaseBundle;

use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Response;

interface UseCaseInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function execute($request);
}