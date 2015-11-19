<?php

namespace Lamudi\UseCaseBundle\Response\Processor;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Response\Response;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class JsonRenderer implements ResponseProcessorInterface
{
    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @param EncoderInterface $jsonEncoder
     */
    public function __construct(EncoderInterface $jsonEncoder)
    {
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Processes the successful outcome of a use case execution. Returns any object that
     * satisfies the environment in which the use case is executed.
     *
     * @param Response $response
     * @param array $options
     * @return mixed
     */
    public function processResponse($response, $options = array())
    {
        $array = (array)$response;
        if (isset($options['append_on_success'])) {
            $array = array_merge($options['append_on_success'], $array);
        }

        $jsonResponse = new JsonResponse();
        $jsonResponse->setContent($this->jsonEncoder->encode($array, 'json'));

        return $jsonResponse;
    }

    /**
     * When an exception is thrown during use case execution, this method is invoked
     *
     * @param \Exception $exception
     * @param array $options
     * @return mixed
     */
    public function handleException($exception, $options = array())
    {
        try {
            throw $exception;
        } catch (UseCaseException $e) {
            $array = array('code' => $e->getCode(), 'message' => $e->getMessage());
            if (isset($options['append_on_error'])) {
                $array = array_merge($options['append_on_error'], $array);
            }

            return new JsonResponse($array);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}