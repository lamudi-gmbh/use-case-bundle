<?php

namespace Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Exception\AlternativeCourseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class JsonRenderer implements ResponseProcessorInterface
{
    const DEFAULT_HTTP_STATUS_CODE = 404;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Encodes the Use Case Response object as JSON and returns it as Symfony JSON response.
     * Available options:
     * - append_on_success - a list of key/value pairs that are appended to the JSON that was created as a result
     *     of successful Use Case execution. If the Response contains colliding fields, the values from the Response will prevail.
     *
     * @param object $response
     * @param array  $options
     *
     * @return JsonResponse
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
     * If a Use Case Exception is thrown, it returns a Symfony JSON response with the exception's message and code
     * in the JSON object. Otherwise, the exception is rethrown.
     * Available options:
     * - append_on_error - a list of key/value pairs that are appended to the resulting JSON.
     * - http_status_code - optional. The status code that the JSON response will contain. Defaults to 404.
     *
     * @param \Exception $exception
     * @param array      $options
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function handleException(\Exception $exception, $options = [])
    {
        if (!isset($options['http_status_code'])) {
            $options['http_status_code'] = self::DEFAULT_HTTP_STATUS_CODE;
        }

        try {
            throw $exception;
        } catch (AlternativeCourseException $e) {
            $array = ['code' => $e->getCode(), 'message' => $e->getMessage()];
            if (isset($options['append_on_error'])) {
                $array = array_merge($options['append_on_error'], $array);
            }

            return new JsonResponse($array, $options['http_status_code']);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
