<?php

namespace Lamudi\UseCaseBundle\Processor\Input;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation;

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
     * Populates the request object by having a Symfony form handle the HTTP request. By default it uses
     * the entire request object as a target for data from the form.
     * Available options:
     * - data_field - optional. If specified, instead of populating the request fields, the processor dumps
     *     all the form data into this field in the use case request as an associative array.
     *
     * @param object                 $request The Use Case Request object to be initialized.
     * @param HttpFoundation\Request $input   Symfony HTTP request object.
     * @param array                  $options An array of configuration options.
     *
     * @return object the Use Case Request object is returned for testability purposes.
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
