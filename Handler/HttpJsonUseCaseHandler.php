<?php

namespace Lamudi\UseCaseBundle\Handler;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Response;
use Lamudi\UseCaseBundle\UseCaseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class HttpJsonUseCaseHandler implements UseCaseHandlerInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * HttpJsonUseCaseHandler constructor.
     *
     * @param EncoderInterface $encoder
     */
    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param UseCaseInterface $useCase
     * @param Request $request
     * @return JsonResponse
     */
    public function handle($useCase, $request)
    {
        try {
            $response = $useCase->execute($request);
            $jsonResponse = new JsonResponse();
            $jsonResponse->setContent($this->encodeResponse($response));
        } catch (UseCaseException $e) {
            $jsonResponse = new JsonResponse(array('code' => $e->getCode(), 'message' => $e->getMessage()));
        }

        return $jsonResponse;
    }

    /**
     * @param Response $response
     * @return string
     */
    private function encodeResponse($response)
    {
        $json = $this->encoder->encode($response, 'json');

        if ($json === '{}') {
            $json = '{"code":200}';
        } else {
            $json = substr($json, 0, -1) . ',"code":200}';
        }

        return $json;
    }
}