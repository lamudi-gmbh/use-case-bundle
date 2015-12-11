<?php

namespace spec\Lamudi\UseCaseBundle\Response\Processor;

use Lamudi\UseCaseBundle\Response\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;

/**
 * Class TwigRendererSpec
 * @mixin \Lamudi\UseCaseBundle\Response\Processor\TwigRenLamudi\UseCaseBundle\Responsederer
 */
class TwigRendererSpec extends ObjectBehavior
{
    public function let(EngineInterface $templatingEngine)
    {
        $this->beConstructedWith($templatingEngine);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Response\Processor\TwigRenderer');
    }

    public function it_throws_an_exception_when_no_template_is_specified()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringProcessResponse(new Response(), array());
    }

    public function it_creates_views_of_specified_forms(
        EngineInterface $templatingEngine, FormFactoryInterface $formFactory, Form $contactForm, Form $searchForm,
        FormView $contactFormView, FormView $searchFormView
    )
    {
        $formFactory->create('contact_form')->willReturn($contactForm);
        $formFactory->create('search_form')->willReturn($searchForm);

        $contactForm->createView()->willReturn($contactFormView);
        $searchForm->createView()->willReturn($searchFormView);

        $options = array(
            'template' => ':default:index.html.twig',
            'forms' => array(
                'form' => 'contact_form',
                'anotherForm' => 'search_form'
            )
        );

        $this->setFormFactory($formFactory);
        $this->processResponse(new Response(), $options);

        $templatingEngine->renderResponse(':default:index.html.twig', array(
            'form' => $contactFormView->getWrappedObject(), 'anotherForm' => $searchFormView->getWrappedObject()
        ))->shouldHaveBeenCalled();
    }

    public function it_sets_data_of_displayed_form(
        EngineInterface $templatingEngine, FormFactoryInterface $formFactory, Form $form, FormView $formView
    )
    {
        $response = new Response();
        $response->formData = array('name' => 'John', 'age' => 40, 'city' => 'Wąbrzeźno');

        $formFactory->create('some_form')->willReturn($form);

        $form->setData($response->formData)->shouldBeCalled();
        $form->createView()->willReturn($formView);

        $options = array(
            'template' => ':default:index.html.twig',
            'forms'    => array(
                'formView' => array(
                    'name' => 'some_form',
                    'data_field' => 'formData'
                )
            )
        );

        $this->setFormFactory($formFactory);
        $this->processResponse($response, $options);

        $templatingEngine
            ->renderResponse(':default:index.html.twig', array('formView' => $formView->getWrappedObject()))
            ->shouldHaveBeenCalled();

    }
}
