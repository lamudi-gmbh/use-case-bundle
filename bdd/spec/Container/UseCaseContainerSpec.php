<?php

namespace spec\Lamudi\UseCaseBundle\Container;

use Doctrine\Common\Annotations\Reader;
use Lamudi\UseCaseBundle\Annotation\UseCase as UseCaseAnnotation;
use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Factory\RequestResolver;
use Lamudi\UseCaseBundle\Request\InputConverterInterface;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Lamudi\UseCaseBundle\Response\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Container\UseCaseContainer
 */
class UseCaseContainerSpec extends ObjectBehavior
{
    public function let(
        UseCaseInterface $useCase, InputConverterInterface $inputConverter,
        ResponseProcessorInterface $responseProcessor, Reader $annotationReader, RequestResolver $requestResolver
    )
    {
        $this->beConstructedWith($annotationReader, $requestResolver);
        $annotationReader->getClassAnnotations(Argument::any())->willReturn(array());

        $this->set('use_case', $useCase);
        $this->setInputConverter('form', $inputConverter);
        $this->assignInputConverter('use_case', 'form');

        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig');

        $requestResolver->resolve(Argument::any())->willReturn(new Request());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\UseCaseContainer');
    }

    public function it_stores_use_cases_identified_by_name(
        UseCaseInterface $useCase1, UseCaseInterface $useCase2
    )
    {
        $this->set('login', $useCase1);
        $this->set('logout', $useCase2);

        $this->get('login')->shouldBe($useCase1);
        $this->get('logout')->shouldBe($useCase2);
    }

    public function it_throws_exception_when_no_use_case_by_given_name_exists(
        UseCaseInterface $useCase
    )
    {
        $this->set('a_use_case', $useCase);
        $this->shouldThrow(UseCaseNotFoundException::class)->duringGet('no_such_use_case_here');
        $this->shouldThrow(UseCaseNotFoundException::class)->duringExecute('no_such_use_case_here', array());
    }

    public function it_creates_request_instance_based_on_use_case_object_and_passes_it_into_input_converter(
        $useCase, Request $request, RequestResolver $requestResolver, InputConverterInterface $inputConverter
    )
    {
        $requestResolver->resolve($useCase)->willReturn($request);
        $this->execute('use_case');

        $inputConverter->initializeRequest($request, null, array())->shouldHaveBeenCalled();
    }

    public function it_initializes_the_use_case_request_with_input_data(
        InputConverterInterface $inputConverter, UseCaseInterface $useCase, Request $useCaseRequest,
        RequestResolver $requestResolver
    )
    {
        $inputData = array();
        $requestResolver->resolve($useCase)->willReturn($useCaseRequest);
        $inputConverter->initializeRequest($useCaseRequest, $inputData, array())->shouldBeCalled();

        $this->setInputConverter('form', $inputConverter);
        $this->assignInputConverter('use_case', 'form');
        $this->set('use_case', $useCase);
        $this->execute('use_case', $inputData);

        $useCase->execute($useCaseRequest)->shouldHaveBeenCalled();
    }

    public function it_registers_response_processor_for_use_case_with_options(
        ResponseProcessorInterface $responseProcessor, HttpResponse $httpResponse,
        UseCaseInterface $useCase, Response $useCaseResponse
    )
    {
        $useCase->execute(Argument::any())->willReturn($useCaseResponse);

        $useCaseResponseOptions = array('template' => 'HelloBundle:hello:index.html.twig');
        $responseProcessor->processResponse($useCaseResponse, $useCaseResponseOptions)->willReturn($httpResponse);

        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig', $useCaseResponseOptions);

        $this->execute('use_case', array())->shouldReturn($httpResponse);
    }

    public function it_uses_the_registered_response_processor_to_handle_errors(
        ResponseProcessorInterface $responseProcessor, HttpResponse $httpResponse,
        UseCaseInterface $useCase
    )
    {
        $exception = new UseCaseException();
        $useCase->execute(Argument::any())->willThrow($exception);

        $useCaseResponseOptions = array(
            'template' => 'HelloBundle:hello:index.html.twig',
            'error_template' => 'HelloBundle:goodbye:epic_fail.html.twig'
        );
        $responseProcessor->handleException($exception, $useCaseResponseOptions)->willReturn($httpResponse);

        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig', $useCaseResponseOptions);

        $this->execute('use_case', array())->shouldReturn($httpResponse);
    }

    public function it_uses_default_input_converter_and_request_processor_when_no_custom_ones_are_registered(
        InputConverterInterface $defaultInputConverter, ResponseProcessorInterface $defaultResponseProcessor,
        UseCaseInterface $useCase, Response $response
    )
    {
        $inputData = array('id' => 123);

        $this->setInputConverter('default', $defaultInputConverter);
        $this->setResponseProcessor('default', $defaultResponseProcessor);
        $this->set('another_use_case', $useCase);
        $useCase->execute(Argument::any())->willReturn($response);

        $this->execute('another_use_case', $inputData);

        $defaultInputConverter->initializeRequest(Argument::type(Request::class), $inputData, array())->shouldHaveBeenCalled();
        $defaultResponseProcessor->processResponse($response, array())->shouldHaveBeenCalled();

    }

    public function it_always_has_default_input_converter_and_request_processor(UseCaseInterface $useCase)
    {
        $this->set('yet_another_use_case', $useCase);
        $this->execute('yet_another_use_case', array())->shouldNotThrow(\Exception::class);
    }

    public function it_loads_annotations_from_use_case_classes(
        Reader $annotationReader, UseCaseInterface $useCase1, UseCaseInterface $useCase2,
        InputConverterInterface $inputConverter, ResponseProcessorInterface $responseProcessor, ResponseProcessorInterface $responseProcessor2
    )
    {
        $useCase1Annotation = new UseCaseAnnotation(array());
        $useCase1Annotation->setInput(array('type' => 'form', 'name' => 'registration_form'));
        $useCase2Annotation1 = new UseCaseAnnotation(array());
        $useCase2Annotation1->setOutput(array('type' => 'twig', 'template' => 'AppBundle:hello:index.html.twig'));
        $useCase2Annotation2 = new UseCaseAnnotation(array());
        $useCase2Annotation2->setAlias('uc2_alias');
        $useCase2Annotation2->setOutput(array('type' => 'twig2', 'template' => 'AppBundle:hello:index.html.twig'));

        $request = new Request();
        $inputConverter->initializeRequest(Argument::type(Request::class), null, array('name' => 'registration_form'))->willReturn($request);
        $responseProcessor->processResponse(Argument::cetera())->willReturn('uc2 success');
        $responseProcessor2->processResponse(Argument::cetera())->willReturn('uc2 alias success');

        $this->set('uc1', $useCase1);
        $this->set('uc2', $useCase2);
        $this->setInputConverter('form', $inputConverter);
        $this->setResponseProcessor('twig', $responseProcessor);
        $this->setResponseProcessor('twig2', $responseProcessor2);

        $annotationReader
            ->getClassAnnotations(Argument::which('getName', get_class($useCase1->getWrappedObject())))
            ->willReturn(array($useCase1Annotation));
        $annotationReader
            ->getClassAnnotations(Argument::which('getName', get_class($useCase2->getWrappedObject())))
            ->willReturn(array($useCase2Annotation1, $useCase2Annotation2));


        $this->loadSettingsFromAnnotations();

        $this->execute('uc1')->shouldReturnAnInstanceOf(Response::class);
        $useCase1->execute($request)->shouldHaveBeenCalled();

        $this->execute('uc2')->shouldReturn('uc2 success');
        $this->execute('uc2_alias')->shouldReturn('uc2 alias success');
    }

    public function it_sets_a_default_input_converter_using_its_alias(
        InputConverterInterface $httpInputConverter, InputConverterInterface $formInputConverter,
        UseCaseInterface $useCase
    )
    {
        $this->set('use_case_with_defaults', $useCase);
        $this->setInputConverter('default', $httpInputConverter);
        $this->setInputConverter('form', $formInputConverter);
        $defaultOptions = array('name' => 'default_form');
        $this->setDefaultInputConverter('form', $defaultOptions);

        $this->execute('use_case_with_defaults', array());

        $formInputConverter->initializeRequest(Argument::type(Request::class), array(), $defaultOptions)
            ->shouldHaveBeenCalled();
    }

    public function it_sets_a_default_response_processor_using_its_alias(
        ResponseProcessorInterface $twigResponseProcessor, ResponseProcessorInterface $jsonResponseProcessor,
        UseCaseInterface $useCase, Response $response
    )
    {
        $this->set('use_case_with_more_defaults', $useCase);
        $this->setResponseProcessor('twig', $twigResponseProcessor);
        $this->setResponseProcessor('json', $jsonResponseProcessor);
        $defaultOptions = array('template' => '::base.html.twig');

        $this->setDefaultResponseProcessor('twig', $defaultOptions);
        $useCase->execute(Argument::any())->willReturn($response);

        $this->execute('use_case_with_more_defaults', array());

        $twigResponseProcessor->processResponse($response, $defaultOptions)->shouldHaveBeenCalled();
    }
}
