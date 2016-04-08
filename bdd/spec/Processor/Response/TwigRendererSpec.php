<?php

namespace spec\Lamudi\UseCaseBundle\Response\Processor;

use Lamudi\UseCaseBundle\UseCase\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;

/**
 * Class TwigRendererSpec
 * @mixin \Lamudi\UseCaseBundle\Processor\Response\TwigRenderer
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
        $this->shouldThrow(\InvalidArgumentException::class)->duringProcessResponse(new Response(), []);
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

        $options = [
            'template' => ':default:index.html.twig',
            'forms' => [
                'form' => 'contact_form',
                'anotherForm' => 'search_form'
            ]
        ];

        $this->setFormFactory($formFactory);
        $this->processResponse(new Response(), $options);

        $templatingEngine->renderResponse(':default:index.html.twig', [
            'form' => $contactFormView->getWrappedObject(), 'anotherForm' => $searchFormView->getWrappedObject()
        ])->shouldHaveBeenCalled();
    }

    public function it_sets_data_of_displayed_form(
        EngineInterface $templatingEngine, FormFactoryInterface $formFactory, Form $form, FormView $formView
    )
    {
        $response = new Response();
        $response->formData = ['name' => 'John', 'age' => 40, 'city' => 'Wąbrzeźno'];

        $formFactory->create('some_form')->willReturn($form);

        $form->setData($response->formData)->shouldBeCalled();
        $form->createView()->willReturn($formView);

        $options = [
            'template' => ':default:index.html.twig',
            'forms'    => [
                'formView' => [
                    'name' => 'some_form',
                    'data_field' => 'formData'
                ]
            ]
        ];

        $this->setFormFactory($formFactory);
        $this->processResponse($response, $options);

        $templatingEngine
            ->renderResponse(':default:index.html.twig', ['formView' => $formView->getWrappedObject()])
            ->shouldHaveBeenCalled();
    }
}
