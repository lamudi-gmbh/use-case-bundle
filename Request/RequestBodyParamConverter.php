<?php


namespace Lamudi\UseCaseBundle\Request;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class RequestBodyParamConverter implements ParamConverterInterface
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
     * Stores the object in the request.
     *
     * @param Request $request The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $class = $configuration->getClass();

        try {
            $object = $this->serializer->deserialize(
                $request->getContent(),
                $class,
                'json'
            );
        } catch (\Exception $e) {
            throw new NotFoundHttpException(sprintf('Could not deserialize request content to object of type "%s"', $class));
        }

        // set the object as the request attribute with the given name
        // (this will later be an argument for the action)
        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        if ($configuration->getClass()) {
            return true;
        } else {
            return false;
        }
    }
}