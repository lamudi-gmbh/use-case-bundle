<?php

namespace spec\Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
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
        ResponseProcessorInterface $responseProcessor
    )
    {
        $this->set('use_case', $useCase);
        $this->setInputConverter('form', $inputConverter);
        $this->assignInputConverter('use_case', 'form');

        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig');
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

    public function it_converts_input_data_into_use_case_request(
        InputConverterInterface $inputConverter, UseCaseInterface $useCase, Request $useCaseRequest
    )
    {
        $inputData = array();
        $inputConverter->createRequest($inputData, array())->willReturn($useCaseRequest);

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

        $defaultInputConverter->createRequest($inputData, array())->shouldHaveBeenCalled();
        $defaultResponseProcessor->processResponse($response, array())->shouldHaveBeenCalled();

    }

    public function it_always_has_default_input_converter_and_request_processor(UseCaseInterface $useCase)
    {
        $this->set('yet_another_use_case', $useCase);
        $this->execute('yet_another_use_case', array())->shouldNotThrow(\Exception::class);
    }
}
