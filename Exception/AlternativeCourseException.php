<?php

namespace Lamudi\UseCaseBundle\Exception;

/**
 * The exceptions thrown by use cases should extend this exception to distinct alternative courses of use
 * case execution from an application failure. This distinction is made by handleException() method implementations
 * in the Response Processors shipped with this bundle.
 *
 * @package Lamudi\UseCaseBundle\Exception
 */
class AlternativeCourseException extends \Exception
{
}
