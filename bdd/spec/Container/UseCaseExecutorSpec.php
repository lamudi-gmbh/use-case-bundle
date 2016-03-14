<?php

namespace spec\Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Exception\ServiceNotFoundException;
use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Exception\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Exception\InputProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
use Lamudi\UseCaseBundle\Request\Request;
use Lamudi\UseCaseBundle\Response\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Container\UseCaseExecutor
 */
class UseCaseExecutorSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $useCaseContainer, UseCaseInterface $useCase,
        ContainerInterface $inputProcessorContainer, ContainerInterface $responseProcessorContainer,
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor
    )
    {
        $this->beConstructedWith($useCaseContainer, $inputProcessorContainer, $responseProcessorContainer);

        $useCaseContainer->get(Argument::any())->willThrow(ServiceNotFoundException::class);
        $useCaseContainer->get('use_case')->willReturn($useCase);

        $inputProcessorContainer->get(Argument::any())->willThrow(ServiceNotFoundException::class);
        $inputProcessorContainer->get('default')->willReturn($defaultInputProcessor);

        $responseProcessorContainer->get(Argument::any())->willThrow(ServiceNotFoundException::class);
        $responseProcessorContainer->get('default')->willReturn($defaultResponseProcessor);
        $defaultResponseProcessor->processResponse(Argument::cetera())->willReturn(new Response());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\UseCaseExecutor');
    }

    public function it_throws_exception_when_no_use_case_by_given_name_exists(ContainerInterface $useCaseContainer)
    {
        $useCaseContainer->get(Argument::any())->willThrow(new ServiceNotFoundException());
        $this->shouldThrow(new UseCaseNotFoundException('Use case "no_such_use_case_here" not found.'))
            ->duringExecute('no_such_use_case_here', []);
    }

    public function it_creates_request_instance_based_on_use_case_configuration_and_passes_it_into_input_processor(
        InputProcessorInterface $inputProcessor, UseCaseInterface $useCase, ContainerInterface $inputProcessorContainer
    )
    {
        $inputProcessorContainer->get('form')->willReturn($inputProcessor);

        $this->assignInputProcessor('use_case', 'form');
        $this->assignRequestClass('use_case', SomeUseCaseRequest::class);

        $input = ['foo' => 'bar', 'key' => 'value'];
        $this->execute('use_case', $input);

        $useCase->execute(Argument::type(SomeUseCaseRequest::class))->shouldHaveBeenCalled();
        $inputProcessor->initializeRequest(Argument::type(SomeUseCaseRequest::class), $input, [])
            ->shouldHaveBeenCalled();
    }

    public function it_throws_an_exception_if_input_processor_does_not_exist()
    {
        $this->assignInputProcessor('use_case', 'no_such_processor_here');
        $this->shouldThrow(InputProcessorNotFoundException::class)->duringExecute('use_case', []);
    }

    public function it_registers_response_processor_for_use_case_with_options(
        ContainerInterface $responseProcessorContainer, ResponseProcessorInterface $responseProcessor,
        UseCaseInterface $useCase, Response $useCaseResponse, HttpResponse $httpResponse
    )
    {
        $responseProcessorContainer->get('twig')->willReturn($responseProcessor);
        $useCase->execute(Argument::any())->willReturn($useCaseResponse);

        $useCaseResponseOptions = ['template' => 'HelloBundle:hello:index.html.twig'];
        $this->assignResponseProcessor('use_case', 'twig', $useCaseResponseOptions);
        $responseProcessor->processResponse($useCaseResponse, $useCaseResponseOptions)->willReturn($httpResponse);

        $this->execute('use_case', [])->shouldReturn($httpResponse);
    }

    public function it_uses_the_registered_response_processor_to_handle_errors(
        ContainerInterface $responseProcessorContainer, ResponseProcessorInterface $responseProcessor,
        HttpResponse $httpResponse, ContainerInterface $useCaseContainer, UseCaseInterface $useCase
    )
    {
        $useCaseContainer->get('use_case')->willReturn($useCase);
        $responseProcessorContainer->get('twig')->willReturn($responseProcessor);

        $exception = new UseCaseException();
        $useCase->execute(Argument::any())->willThrow($exception);

        $useCaseResponseOptions = [
            'template' => 'HelloBundle:hello:index.html.twig',
            'error_template' => 'HelloBundle:goodbye:epic_fail.html.twig'
        ];
        $responseProcessor->handleException($exception, $useCaseResponseOptions)->willReturn($httpResponse);

        $this->assignResponseProcessor('use_case', 'twig', $useCaseResponseOptions);

        $this->execute('use_case', [])->shouldReturn($httpResponse);
    }

    public function it_throws_an_exception_if_response_processor_does_not_exist(
        ContainerInterface $useCaseContainer, UseCaseInterface $useCase, Response $useCaseResponse
    )
    {
        $useCaseContainer->get('use_case')->willReturn($useCase);
        $useCaseResponseOptions = ['template' => 'HelloBundle:hello:index.html.twig'];
        $this->assignResponseProcessor('use_case', 'no_such_processor', $useCaseResponseOptions);

        $useCase->execute(Argument::any())->willReturn($useCaseResponse);

        $this->shouldThrow(ResponseProcessorNotFoundException::class)->duringExecute('use_case', []);
    }

    public function it_uses_default_input_processor_and_request_processor_when_no_custom_ones_are_registered(
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        ContainerInterface $useCaseContainer, UseCaseInterface $useCase, Response $response
    )
    {
        $useCaseContainer->get('another_use_case')->willReturn($useCase);
        $useCase->execute(Argument::type(Request::class))->willReturn($response);

        $input = ['id' => 123];
        $this->execute('another_use_case', $input);

        $defaultInputProcessor->initializeRequest(Argument::type(Request::class), $input, [])->shouldHaveBeenCalled();
        $defaultResponseProcessor->processResponse($response, [])->shouldHaveBeenCalled();
    }

    public function it_always_has_default_input_processor_and_request_processor(
        ContainerInterface $useCaseContainer, UseCaseInterface $useCase
    )
    {
        $useCaseContainer->get('yet_another_use_case')->willReturn($useCase);
        $this->execute('yet_another_use_case', [])->shouldNotThrow(\Exception::class);
    }

    public function it_works_like_a_charm_with_several_use_cases_configured(
        ContainerInterface $useCaseContainer, ContainerInterface $responseProcessorContainer,
        UseCaseInterface $useCase1, UseCaseInterface $useCase2, UseCaseInterface $useCase3,
        ContainerInterface $inputProcessorContainer, InputProcessorInterface $inputProcessor,
        ResponseProcessorInterface $responseProcessor, ResponseProcessorInterface $responseProcessor2
    )
    {
        $useCaseContainer->get('uc1')->willReturn($useCase1);
        $useCaseContainer->get('uc2')->willReturn($useCase2);
        $useCaseContainer->get('uc2_alias')->willReturn($useCase2);
        $useCaseContainer->get('uc3')->willReturn($useCase3);

        $useCase1->execute(Argument::any())->willReturn(new Response());
        $useCase2->execute(Argument::any())->willReturn(new Response());
        $useCase3->execute(Argument::any())->willThrow(new UseCaseException());

        $request = new Request();
        $inputProcessor->initializeRequest(Argument::type(Request::class), null, ['name' => 'registration_form'])->willReturn($request);
        $responseProcessor->processResponse(Argument::cetera())->willReturn('uc2 success');
        $responseProcessor2->processResponse(Argument::cetera())->willReturn('uc2 alias success');
        $responseProcessor2->handleException(Argument::cetera())->willReturn('uc3 error');

        $inputProcessorContainer->get('form')->willReturn($inputProcessor);
        $responseProcessorContainer->get('twig')->willReturn($responseProcessor);
        $responseProcessorContainer->get('twig2')->willReturn($responseProcessor2);

        $this->assignInputProcessor('uc1', 'form', ['name' => 'registration_form']);
        $this->assignResponseProcessor('uc2', 'twig', ['template' => 'AppBundle:hello:index.html.twig']);
        $this->assignResponseProcessor('uc2_alias', 'twig2', ['template' => 'AppBundle:hello:index.html.twig']);
        $this->assignResponseProcessor('uc3', 'twig2', ['template' => 'AppBundle:hello:index.html.twig']);

        $this->execute('uc1')->shouldReturnAnInstanceOf(Response::class);
        $this->execute('uc2')->shouldReturn('uc2 success');
        $this->execute('uc2_alias')->shouldReturn('uc2 alias success');
        $this->execute('uc3')->shouldReturn('uc3 error');
    }

    public function it_sets_a_default_input_processor_using_its_alias(
        InputProcessorInterface $httpInputProcessor, InputProcessorInterface $formInputProcessor,
        ContainerInterface $inputProcessorContainer, ContainerInterface $useCaseContainer, UseCaseInterface $useCase
    )
    {
        $useCaseContainer->get('use_case_with_defaults')->willReturn($useCase);
        $inputProcessorContainer->get('default')->willReturn($httpInputProcessor);
        $inputProcessorContainer->get('form')->willReturn($formInputProcessor);
        $defaultOptions = ['name' => 'default_form'];
        $this->setDefaultInputProcessor('form', $defaultOptions);

        $this->execute('use_case_with_defaults', []);

        $formInputProcessor->initializeRequest(Argument::type(Request::class), [], $defaultOptions)
            ->shouldHaveBeenCalled();
    }

    public function it_sets_a_default_response_processor_using_its_alias(
        ResponseProcessorInterface $twigResponseProcessor, ResponseProcessorInterface $jsonResponseProcessor,
        ContainerInterface $useCaseContainer, UseCaseInterface $useCase, Response $response,
        ContainerInterface $responseProcessorContainer
    )
    {
        $useCaseContainer->get('use_case_with_more_defaults')->willReturn($useCase);
        $responseProcessorContainer->get('twig')->willReturn($twigResponseProcessor);
        $responseProcessorContainer->get('json')->willReturn($jsonResponseProcessor);
        $defaultOptions = ['template' => '::base.html.twig'];

        $this->setDefaultResponseProcessor('twig', $defaultOptions);
        $useCase->execute(Argument::any())->willReturn($response);

        $this->execute('use_case_with_more_defaults', []);

        $twigResponseProcessor->processResponse($response, $defaultOptions)->shouldHaveBeenCalled();
    }
}

class SomeUseCaseRequest extends Request {}
