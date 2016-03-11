<?php

namespace Lamudi\UseCaseBundle\Response\Processor;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Response\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class JsonRenderer implements ResponseProcessorInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Processes the successful outcome of a use case execution. Returns any object that
     * satisfies the environment in which the use case is executed.
     *
     * @param Response $response
     * @param array $options
     * @return mixed
     */
    public function processResponse($response, $options = [])
    {
        $array = (array)$response;
        if (isset($options['append_on_success'])) {
            $array = array_merge($options['append_on_success'], $array);
        }

        $jsonResponse = new JsonResponse();
        $jsonResponse->setContent($this->serializer->serialize($array, 'json'));

        return $jsonResponse;
    }

    /**
     * When an exception is thrown during use case execution, this method is invoked
     *
     * @param \Exception $exception
     * @param array $options
     * @return mixed
     */
    public function handleException($exception, $options = [])
    {
        try {
            throw $exception;
        } catch (UseCaseException $e) {
            $array = ['code' => $e->getCode(), 'message' => $e->getMessage()];
            if (isset($options['append_on_error'])) {
                $array = array_merge($options['append_on_error'], $array);
            }

            return new JsonResponse($array, $e->getCode());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
