<?php

namespace Lamudi\UseCaseBundle\Request\Processor;

use Lamudi\UseCaseBundle\Request\Request;
use Symfony\Component\Form\FormFactoryInterface;

class FormInputProcessor implements InputProcessorInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Initializes a use case request based on the input data received. Additional options may help
     * determine the way to initialize the use case request object.
     *
     * @param Request $request The use case request object to be initialized.
     * @param mixed $input Any object that contains input data.
     * @param array $options An array of options used to create the request object.
     */
    public function initializeRequest($request, $input, $options = [])
    {
        if (!isset($options['name'])) {
            throw new \InvalidArgumentException();
        }

        if (isset($options['data_field'])) {
            $form = $this->formFactory->create($options['name']);
            $form->handleRequest($input);

            $fieldName = $options['data_field'];
            $request->$fieldName = $form->getData();
        } else {
            $form = $this->formFactory->create($options['name'], $request, ['data_class' => get_class($request)]);
            $form->handleRequest($input);
        }

        return $request;
    }
}
