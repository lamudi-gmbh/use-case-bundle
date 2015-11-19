<?php

namespace spec\Lamudi\UseCaseBundle\Request\Converter;

use Lamudi\UseCaseBundle\Request\Converter\InputConverterInterface;
use Lamudi\UseCaseBundle\Request\Request;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @mixin \Lamudi\UseCaseBundle\Request\Converter\FormInputConverter
 */
class FormInputConverterSpec extends ObjectBehavior
{
    public function let(FormFactoryInterface $formFactory, FormInterface $form)
    {
        $this->beConstructedWith($formFactory);
        $formFactory->create(Argument::cetera())->willReturn($form);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Request\Converter\FormInputConverter');
    }

    public function it_is_an_input_converter()
    {
        $this->shouldHaveType(InputConverterInterface::class);
    }

    public function it_throws_an_exception_if_form_name_is_not_specified()
    {
        $request = new Request();
        $this->shouldThrow(\InvalidArgumentException::class)->duringInitializeRequest($request, array(), array());
    }

    public function it_uses_form_factory_to_create_form_by_name(FormFactoryInterface $formFactory)
    {
        $this->initializeRequest(new Request(), array(), array('name' => 'order_form'));

        $formFactory->create('order_form', null, array('data_class' => Request::class))->shouldHaveBeenCalled();
    }

    public function it_uses_the_created_form_to_populate_request_fields(FormFactoryInterface $formFactory, FormInterface $form)
    {
        $request = new Request();
        $formFactory->create('order_form', Argument::cetera())->willReturn($form);

        $inputData = array('foo' => 'bar', 'baz' => 213);
        $this->initializeRequest($request, $inputData, array('name' => 'order_form'));

        $form->submit($inputData)->shouldHaveBeenCalled();
    }

    public function it_dumps_form_data_to_specified_field(FormFactoryInterface $formFactory, FormInterface $form)
    {
        $request = new Request();
        $formFactory->create('order_form')->willReturn($form);

        $inputData = array('foo' => 'bar', 'baz' => 213);
        $formData = array('foo' => 'bar_', 'baz' => 213312, 'csrf_token' => 'xyz');
        $form->handleRequest($inputData)->shouldBeCalled();
        $form->getData()->willReturn($formData);

        $request = $this->initializeRequest($request, $inputData, array('name' => 'order_form', 'data_field' => 'formData'));

        $form->submit($inputData)->shouldNotHaveBeenCalled();
        $request->formData->shouldBe($formData);
    }
}
