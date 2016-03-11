<?php

namespace spec\Lamudi\UseCaseBundle\Container;

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
 * @mixin \Lamudi\UseCaseBundle\Container\UseCaseContainer
 */
class UseCaseContainerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\UseCaseContainer');
    }

    public function it_stores_use_cases_identified_by_name(UseCaseInterface $useCase1, UseCaseInterface $useCase2)
    {
        $this->set('login', $useCase1);
        $this->set('logout', $useCase2);

        $this->get('login')->shouldBe($useCase1);
        $this->get('logout')->shouldBe($useCase2);
    }

    public function it_throws_exception_when_no_use_case_by_given_name_exists(UseCaseInterface $useCase)
    {
        $this->set('a_use_case', $useCase);
        $this->shouldThrow(UseCaseNotFoundException::class)->duringGet('no_such_use_case_here');
        $this->shouldThrow(UseCaseNotFoundException::class)->duringExecute('no_such_use_case_here', []);
    }

    public function it_creates_request_instance_based_on_use_case_configuration_and_passes_it_into_input_processor(
        InputProcessorInterface $inputProcessor, UseCaseInterface $useCase
    )
    {
        $this->set('use_case', $useCase);
        $this->setInputProcessor('form', $inputProcessor);
        $this->assignInputProcessor('use_case', 'form');
        $this->assignRequestClass('use_case', SomeUseCaseRequest::class);

        $input = ['foo' => 'bar', 'key' => 'value'];
        $this->execute('use_case', $input);

        $this->getInputProcessor('form')->shouldReturn($inputProcessor);
        $useCase->execute(Argument::type(SomeUseCaseRequest::class))->shouldHaveBeenCalled();
        $inputProcessor->initializeRequest(Argument::type(SomeUseCaseRequest::class), $input, [])
            ->shouldHaveBeenCalled();
    }

    public function it_throws_an_exception_if_input_processor_does_not_exist(
        InputProcessorInterface $inputProcessor, UseCaseInterface $useCase
    )
    {
        $input = [];

        $this->set('use_case', $useCase);
        $this->setInputProcessor('form', $inputProcessor);
        $this->assignInputProcessor('use_case', 'no_such_processor_here');

        $this->shouldThrow(InputProcessorNotFoundException::class)->duringExecute('use_case', $input);
        $this->shouldThrow(InputProcessorNotFoundException::class)->duringGetInputProcessor('no_such_processor_too');
    }

    public function it_registers_response_processor_for_use_case_with_options(
        ResponseProcessorInterface $responseProcessor, HttpResponse $httpResponse,
        UseCaseInterface $useCase, Response $useCaseResponse
    )
    {
        $this->set('use_case', $useCase);
        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig');
        $useCase->execute(Argument::any())->willReturn($useCaseResponse);

        $useCaseResponseOptions = ['template' => 'HelloBundle:hello:index.html.twig'];
        $responseProcessor->processResponse($useCaseResponse, $useCaseResponseOptions)->willReturn($httpResponse);

        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig', $useCaseResponseOptions);

        $this->execute('use_case', [])->shouldReturn($httpResponse);
        $this->getResponseProcessor('twig')->shouldReturn($responseProcessor);
    }

    public function it_uses_the_registered_response_processor_to_handle_errors(
        ResponseProcessorInterface $responseProcessor, HttpResponse $httpResponse, UseCaseInterface $useCase
    )
    {
        $this->set('use_case', $useCase);
        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig');

        $exception = new UseCaseException();
        $useCase->execute(Argument::any())->willThrow($exception);

        $useCaseResponseOptions = [
            'template' => 'HelloBundle:hello:index.html.twig',
            'error_template' => 'HelloBundle:goodbye:epic_fail.html.twig'
        ];
        $responseProcessor->handleException($exception, $useCaseResponseOptions)->willReturn($httpResponse);

        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig', $useCaseResponseOptions);

        $this->execute('use_case', [])->shouldReturn($httpResponse);
    }


    public function it_throws_an_exception_if_response_processor_does_not_exist(
        ResponseProcessorInterface $responseProcessor, UseCaseInterface $useCase, Response $useCaseResponse
    )
    {
        $this->set('use_case', $useCase);
        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'twig');

        $useCase->execute(Argument::any())->willReturn($useCaseResponse);
        $useCaseResponseOptions = ['template' => 'HelloBundle:hello:index.html.twig'];

        $this->setResponseProcessor('twig', $responseProcessor);
        $this->assignResponseProcessor('use_case', 'no_such_processor', $useCaseResponseOptions);

        $this->shouldThrow(ResponseProcessorNotFoundException::class)->duringExecute('use_case', []);
        $this->shouldThrow(ResponseProcessorNotFoundException::class)->duringGetResponseProcessor('no_such_processor_too');
    }

    public function it_uses_default_input_processor_and_request_processor_when_no_custom_ones_are_registered(
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        UseCaseInterface $useCase, Response $response
    )
    {
        $input = ['id' => 123];

        $this->setInputProcessor('default', $defaultInputProcessor);
        $this->setResponseProcessor('default', $defaultResponseProcessor);
        $this->set('another_use_case', $useCase);
        $useCase->execute(Argument::any())->willReturn($response);

        $this->execute('another_use_case', $input);

        $defaultInputProcessor->initializeRequest(Argument::type(Request::class), $input, [])->shouldHaveBeenCalled();
        $defaultResponseProcessor->processResponse($response, [])->shouldHaveBeenCalled();

    }

    public function it_always_has_default_input_processor_and_request_processor(UseCaseInterface $useCase)
    {
        $this->set('yet_another_use_case', $useCase);
        $this->execute('yet_another_use_case', [])->shouldNotThrow(\Exception::class);
    }

    public function it_works_like_a_charm_with_several_use_cases_configured(
        UseCaseInterface $useCase1, UseCaseInterface $useCase2, UseCaseInterface $useCase3,
        InputProcessorInterface $inputProcessor,
        ResponseProcessorInterface $responseProcessor, ResponseProcessorInterface $responseProcessor2
    )
    {
        $useCase1->execute(Argument::any())->willReturn(new Response());
        $useCase2->execute(Argument::any())->willReturn(new Response());
        $useCase3->execute(Argument::any())->willThrow(new UseCaseException());

        $request = new Request();
        $inputProcessor->initializeRequest(Argument::type(Request::class), null, ['name' => 'registration_form'])->willReturn($request);
        $responseProcessor->processResponse(Argument::cetera())->willReturn('uc2 success');
        $responseProcessor2->processResponse(Argument::cetera())->willReturn('uc2 alias success');
        $responseProcessor2->handleException(Argument::cetera())->willReturn('uc3 error');

        $this->set('uc1', $useCase1);
        $this->set('uc2', $useCase2);
        $this->set('uc2_alias', $useCase2);
        $this->set('uc3', $useCase3);
        $this->setInputProcessor('form', $inputProcessor);
        $this->setResponseProcessor('twig', $responseProcessor);
        $this->setResponseProcessor('twig2', $responseProcessor2);

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
        UseCaseInterface $useCase
    )
    {
        $this->set('use_case_with_defaults', $useCase);
        $this->setInputProcessor('default', $httpInputProcessor);
        $this->setInputProcessor('form', $formInputProcessor);
        $defaultOptions = ['name' => 'default_form'];
        $this->setDefaultInputProcessor('form', $defaultOptions);

        $this->execute('use_case_with_defaults', []);

        $formInputProcessor->initializeRequest(Argument::type(Request::class), [], $defaultOptions)
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
        $defaultOptions = ['template' => '::base.html.twig'];

        $this->setDefaultResponseProcessor('twig', $defaultOptions);
        $useCase->execute(Argument::any())->willReturn($response);

        $this->execute('use_case_with_more_defaults', []);

        $twigResponseProcessor->processResponse($response, $defaultOptions)->shouldHaveBeenCalled();
    }
}

class SomeUseCaseRequest extends Request {}
