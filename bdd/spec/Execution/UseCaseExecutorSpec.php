<?php

namespace spec\Lamudi\UseCaseBundle\Execution;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Execution\UseCaseConfiguration;
use Lamudi\UseCaseBundle\Execution\UseCaseContext;
use Lamudi\UseCaseBundle\Execution\UseCaseContextResolver;
use Lamudi\UseCaseBundle\Container\ItemNotFoundException;
use Lamudi\UseCaseBundle\Exception\AlternativeCourseException;
use Lamudi\UseCaseBundle\Execution\UseCaseNotFoundException;
use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
use Lamudi\UseCaseBundle\Processor\Response\InputAwareResponseProcessor;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Lamudi\UseCaseBundle\Processor\Response\ResponseProcessorInterface;
use Lamudi\UseCaseBundle\UseCase\UseCaseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Execution\UseCaseExecutor
 */
class UseCaseExecutorSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $useCaseContainer, UseCaseInterface $useCase,
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        UseCaseContextResolver $contextResolver, UseCaseContext $context
    )
    {
        $this->beConstructedWith($useCaseContainer, $contextResolver);

        $useCaseContainer->get('use_case')->willReturn($useCase);
        $this->assignRequestClass('use_case', '\stdClass');
        $context->getInputProcessor()->willReturn($defaultInputProcessor);
        $context->getInputProcessorOptions()->willReturn([]);
        $context->getResponseProcessor()->willReturn($defaultResponseProcessor);
        $context->getResponseProcessorOptions()->willReturn([]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Execution\UseCaseExecutor');
    }

    public function it_throws_exception_when_no_use_case_by_given_name_exists(ContainerInterface $useCaseContainer)
    {
        $useCaseContainer->get('no_such_use_case_here')->willThrow(new ItemNotFoundException());
        $this->shouldThrow(new UseCaseNotFoundException('Use case "no_such_use_case_here" not found.'))
            ->duringExecute('no_such_use_case_here', []);
    }

    public function it_creates_request_instance_based_on_use_case_configuration_and_passes_it_into_input_processor(
        InputProcessorInterface $inputProcessor, UseCaseInterface $useCase, UseCaseContext $context,
        UseCaseContextResolver $contextResolver
    )
    {
        $inputProcessorOptions = ['name' => 'contact_form', 'style' => 'blue'];
        $context->getInputProcessor()->willReturn($inputProcessor);
        $context->getInputProcessorOptions()->willReturn($inputProcessorOptions);
        $contextResolver->resolveContext(Argument::which('getInputProcessorOptions', $inputProcessorOptions))
            ->willReturn($context);

        $this->assignInputProcessor('use_case', 'form', $inputProcessorOptions);
        $this->assignRequestClass('use_case', SomeUseCaseRequest::class);
        $context->getInputProcessor()->willReturn($inputProcessor);

        $input = ['foo' => 'bar', 'key' => 'value'];
        $this->execute('use_case', $input);

        $useCase->execute(Argument::type(SomeUseCaseRequest::class))->shouldHaveBeenCalled();
        $inputProcessor->initializeRequest(Argument::type(SomeUseCaseRequest::class), $input, $inputProcessorOptions)
            ->shouldHaveBeenCalled();
    }

    public function it_registers_response_processor_for_use_case_with_options(
        UseCaseContextResolver $contextResolver, UseCaseContext $context, ResponseProcessorInterface $responseProcessor,
        UseCaseInterface $useCase, \stdClass $useCaseResponse, HttpResponse $output
    )
    {
        $responseProcessorOptions = ['template' => 'HelloBundle:hello:index.html.twig'];
        $context->getResponseProcessor()->willReturn($responseProcessor);
        $context->getResponseProcessorOptions()->willReturn($responseProcessorOptions);
        $contextResolver->resolveContext(Argument::which('getResponseProcessorOptions', $responseProcessorOptions))
            ->willReturn($context);

        $this->assignResponseProcessor('use_case', 'twig', $responseProcessorOptions);
        $this->assignRequestClass('use_case', SomeUseCaseRequest::class);
        $responseProcessor->processResponse($useCaseResponse, $responseProcessorOptions)->willReturn($output);

        $context->getResponseProcessor()->willReturn($responseProcessor);
        $useCase->execute(Argument::type(SomeUseCaseRequest::class))->willReturn($useCaseResponse);
        $this->execute('use_case', [])->shouldBe($output);
    }

    public function it_uses_context_resolver_to_fetch_the_use_case_context(
        UseCaseInterface $useCase, UseCaseContextResolver $contextResolver, UseCaseContext $context, \stdClass $response,
        InputProcessorInterface $formInputProcessor, ResponseProcessorInterface $twigResponseProcessor, HttpResponse $httpResponse
    )
    {
        $this->assignRequestClass('use_case', SomeUseCaseRequest::class);
        $this->assignInputProcessor('use_case', 'form');
        $this->assignResponseProcessor('use_case', 'twig');

        $config = new UseCaseConfiguration();
        $config->setRequestClassName(SomeUseCaseRequest::class);
        $config->setInputProcessorName('form');
        $config->setResponseProcessorName('twig');

        $contextResolver->resolveContext($config)->willReturn($context);
        $context->getInputProcessor()->willReturn($formInputProcessor);
        $context->getInputProcessorOptions()->willReturn(['name' => 'default_form']);
        $context->getResponseProcessor()->willReturn($twigResponseProcessor);
        $context->getResponseProcessorOptions()->willReturn(['template' => 'AppBundle:hello:default.html.twig']);

        $input = ['form_data' => ['name' => 'John'], 'user_id' => 665, 'action' => 'update'];
        $formInputProcessor->initializeRequest(Argument::type(SomeUseCaseRequest::class), $input, ['name' => 'default_form'])
            ->shouldBeCalled();
        $useCase->execute(Argument::type(SomeUseCaseRequest::class))->willReturn($response);
        $twigResponseProcessor->processResponse($response, ['template' => 'AppBundle:hello:default.html.twig'])
            ->willReturn($httpResponse);

        $this->execute('use_case', $input)->shouldBe($httpResponse);
    }

    public function it_uses_the_registered_response_processor_to_handle_errors(
        UseCaseContextResolver $contextResolver, UseCaseContext $context, ResponseProcessorInterface $responseProcessor,
        HttpResponse $httpResponse, UseCaseInterface $useCase
    )
    {
        $responseProcessorOptions = [
            'template' => 'HelloBundle:hello:index.html.twig',
            'error_template' => 'HelloBundle:goodbye:epic_fail.html.twig'
        ];
        $context->getResponseProcessor()->willReturn($responseProcessor);
        $context->getResponseProcessorOptions()->willReturn($responseProcessorOptions);
        $contextResolver->resolveContext(Argument::which('getResponseProcessorOptions', $responseProcessorOptions))
            ->willReturn($context);

        $exception = new AlternativeCourseException();
        $useCase->execute(Argument::any())->willThrow($exception);
        $responseProcessor->handleException($exception, $responseProcessorOptions)->willReturn($httpResponse);

        $this->assignResponseProcessor('use_case', 'twig', $responseProcessorOptions);
        $this->execute('use_case', [])->shouldReturn($httpResponse);
    }

    public function it_resolves_context_with_empty_configuration_if_no_configuration_exists_for_use_case(
        UseCaseContextResolver $contextResolver, UseCaseContext $context
    )
    {
        $defaultEmptyConfiguration = new UseCaseConfiguration();
        $defaultEmptyConfiguration->setRequestClassName('\stdClass');

        $contextResolver->resolveContext($defaultEmptyConfiguration)->shouldBeCalled();
        $contextResolver->resolveContext($defaultEmptyConfiguration)->willReturn($context);

        $this->execute('use_case', []);
    }

    public function it_resolves_context_by_context_name(UseCaseContextResolver $contextResolver, UseCaseContext $context)
    {
        $contextResolver->resolveContext('test_context')->shouldBeCalled();
        $contextResolver->resolveContext('test_context')->willReturn($context);
        $this->execute('use_case', ['foo' => 'bar'], 'test_context');
    }

    public function it_passes_the_input_to_the_response_processor_if_its_input_aware(
        UseCaseContextResolver $contextResolver, UseCaseContext $newContext,
        InputProcessorInterface $inputProcessor, InputAwareResponseProcessor $inputAwareResponseProcessor
    )
    {
        $input = ['some' => 'input', 'for' => 'the use case'];

        $contextResolver->resolveContext('input_aware')->willReturn($newContext);

        $newContext->getInputProcessor()->willReturn($inputProcessor);
        $newContext->getInputProcessorOptions()->willReturn([]);
        $newContext->getResponseProcessor()->willReturn($inputAwareResponseProcessor);
        $newContext->getResponseProcessorOptions()->willReturn([]);

        $inputAwareResponseProcessor->setInput($input)->shouldBeCalled();
        $inputAwareResponseProcessor->processResponse(Argument::any(), [])->shouldBeCalled();

        $this->execute('use_case', $input, 'input_aware');
    }
}

class SomeUseCaseRequest {}
