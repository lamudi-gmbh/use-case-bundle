<?php

namespace spec\Lamudi\UseCaseBundle\Container;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Container\UseCaseConfiguration;
use Lamudi\UseCaseBundle\Container\UseCaseContext;
use Lamudi\UseCaseBundle\Container\UseCaseContextResolver;
use Lamudi\UseCaseBundle\Exception\InputProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Exception\ServiceNotFoundException;
use Lamudi\UseCaseBundle\Request\Processor\InputProcessorInterface;
use Lamudi\UseCaseBundle\Response\Processor\ResponseProcessorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin UseCaseContextResolver
 */
class UseCaseContextResolverSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $inputProcessorContainer, ContainerInterface $responseProcessorContainer,
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor,
        InputProcessorInterface $cliInputProcessor, ResponseProcessorInterface $cliResponseProcessor
    )
    {
        $this->beConstructedWith($inputProcessorContainer, $responseProcessorContainer);

        $inputProcessorContainer->get('default')->willReturn($defaultInputProcessor);
        $inputProcessorContainer->get('http')->willReturn($httpInputProcessor);
        $inputProcessorContainer->get('cli')->willReturn($cliInputProcessor);
        $responseProcessorContainer->get('default')->willReturn($defaultResponseProcessor);
        $responseProcessorContainer->get('twig')->willReturn($twigResponseProcessor);
        $responseProcessorContainer->get('cli')->willReturn($cliResponseProcessor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Container\UseCaseContextResolver');
    }

    public function it_resolves_context_that_contains_input_and_response_processor(
        InputProcessorInterface $httpInputProcessor, InputProcessorInterface $cliInputProcessor,
        ResponseProcessorInterface $twigResponseProcessor, ResponseProcessorInterface $cliResponseProcessor
    )
    {
        $this->setContext('web', 'http', 'twig');
        $this->setContext('console', 'cli', 'cli');

        $webContext = $this->resolveContext('web');
        $webContext->shouldHaveType(UseCaseContext::class);
        $webContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $webContext->getResponseProcessor()->shouldBe($twigResponseProcessor);

        $consoleContext = $this->resolveContext('console');
        $consoleContext->shouldHaveType(UseCaseContext::class);
        $consoleContext->getInputProcessor()->shouldBe($cliInputProcessor);
        $consoleContext->getResponseProcessor()->shouldBe($cliResponseProcessor);
    }

    public function it_resolves_context_with_options(
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor
    )
    {
        $this->setContext('web', ['type' => 'http', 'accept' => 'text/html'], ['type' => 'twig', 'template' => 'none']);

        $webContext = $this->resolveContext('web');
        $webContext->shouldHaveType(UseCaseContext::class);
        $webContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $webContext->getInputProcessorOptions()->shouldBe(['accept' => 'text/html']);
        $webContext->getResponseProcessor()->shouldBe($twigResponseProcessor);
        $webContext->getResponseProcessorOptions()->shouldBe(['template' => 'none']);
    }

    public function it_falls_back_to_default_configuration_when_context_is_incomplete(
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor
    )
    {
        $this->setContext('only_input', 'http');
        $this->setContext('only_response', null, 'twig');

        $onlyInputContext = $this->resolveContext('only_input');
        $onlyInputContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $onlyInputContext->getResponseProcessor()->shouldBe($defaultResponseProcessor);
        $onlyResponseContext = $this->resolveContext('only_response');
        $onlyResponseContext->getInputProcessor()->shouldBe($defaultInputProcessor);
        $onlyResponseContext->getResponseProcessor()->shouldBe($twigResponseProcessor);
    }

    public function it_allows_to_set_the_name_of_the_default_context(
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor,
        InputProcessorInterface $cliInputProcessor, ResponseProcessorInterface $cliResponseProcessor
    )
    {
        $this->setContext('web', 'http', 'twig');
        $this->setContext('only_input', 'cli');
        $this->setContext('only_response', null, 'cli');
        $this->setDefaultContextName('web');

        $onlyInputContext = $this->resolveContext('only_input');
        $onlyInputContext->getInputProcessor()->shouldBe($cliInputProcessor);
        $onlyInputContext->getResponseProcessor()->shouldBe($twigResponseProcessor);
        $onlyResponseContext = $this->resolveContext('only_response');
        $onlyResponseContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $onlyResponseContext->getResponseProcessor()->shouldBe($cliResponseProcessor);
    }

    public function it_overrides_the_options_of_the_default_context(
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor
    )
    {
        $this->setContext('default', ['type' => 'default', 'option' => 'foo'], ['type' => 'default', 'foo' => 'bar']);
        $this->setContext('only_input', ['type' => 'http', 'accept' => 'text/html']);
        $this->setContext('only_response', null, ['type' => 'twig', 'template' => 'none']);

        $onlyInputContext = $this->resolveContext('only_input');
        $onlyInputContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $onlyInputContext->getInputProcessorOptions()->shouldBe(['accept' => 'text/html']);
        $onlyInputContext->getResponseProcessor()->shouldBe($defaultResponseProcessor);
        $onlyInputContext->getResponseProcessorOptions()->shouldBe(['foo' => 'bar']);

        $onlyResponseContext = $this->resolveContext('only_response');
        $onlyResponseContext->getInputProcessor()->shouldBe($defaultInputProcessor);
        $onlyResponseContext->getInputProcessorOptions()->shouldBe(['option' => 'foo']);
        $onlyResponseContext->getResponseProcessor()->shouldBe($twigResponseProcessor);
        $onlyResponseContext->getResponseProcessorOptions()->shouldBe(['template' => 'none']);
    }

    public function it_resolves_contexts_from_configuration_array(
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor
    )
    {
        $defaultContext = $this->resolveContext(['input' => 'default', 'output' => 'default']);
        $defaultContext->getInputProcessor()->shouldBe($defaultInputProcessor);
        $defaultContext->getResponseProcessor()->shouldBe($defaultResponseProcessor);

        $webContext = $this->resolveContext(['input' => 'http', 'output' => 'twig']);
        $webContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $webContext->getResponseProcessor()->shouldBe($twigResponseProcessor);
    }

    public function it_merges_given_options_with_defaults(
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor
    )
    {
        $this->setContext('default', ['type' => 'cli', 'yes' => true, 'maybe' => 5], ['type' => 'cli', 'maybe' => 3, 'no' => false]);
        $context = $this->resolveContext([
            'input' => ['type' => 'http', 'yes' => false, 'probably' => 'not'],
            'output' => ['type' => 'twig', 'yes' => true, 'maybe' => 10]
        ]);

        $context->getInputProcessor()->shouldBe($httpInputProcessor);
        $context->getInputProcessorOptions()->shouldBe(['yes' => false, 'maybe' => 5, 'probably' => 'not']);
        $context->getResponseProcessor()->shouldBe($twigResponseProcessor);
        $context->getResponseProcessorOptions()->shouldBe(['maybe' => 10, 'no' => false, 'yes' => true]);
    }

    public function it_works_with_instances_of_use_case_configuration(
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $cliResponseProcessor
    )
    {
        $config = new UseCaseConfiguration();
        $config->setInputProcessorName('http');
        $config->setInputProcessorOptions(['accept' => 'application/json']);
        $config->setResponseProcessorName('cli');
        $config->setResponseProcessorOptions(['width' => 80, 'height' => 25]);

        $context = $this->resolveContext($config);
        $context->getInputProcessor()->shouldBe($httpInputProcessor);
        $context->getInputProcessorOptions()->shouldBe(['accept' => 'application/json']);
        $context->getResponseProcessor()->shouldBe($cliResponseProcessor);
        $context->getResponseProcessorOptions()->shouldBe(['width' => 80, 'height' => 25]);
    }

    public function it_throws_an_exception_when_argument_type_is_invalid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringResolveContext(false);
        $this->shouldThrow(\InvalidArgumentException::class)->duringResolveContext(new \DateTime());
        $this->shouldThrow(\InvalidArgumentException::class)->duringResolveContext(3.14);
    }

    public function it_throws_an_exception_if_input_processor_does_not_exist(ContainerInterface $inputProcessorContainer)
    {
        $inputProcessorContainer->get('no_such_processor')->willThrow(ServiceNotFoundException::class);
        $this->setContext('broken_context', 'no_such_processor');
        $this->shouldThrow(InputProcessorNotFoundException::class)->duringResolveContext('broken_context');
    }

    public function it_throws_an_exception_if_response_processor_does_not_exist(ContainerInterface $responseProcessorContainer)
    {
        $responseProcessorContainer->get('no_such_processor')->willThrow(ServiceNotFoundException::class);
        $this->setContext('broken_context', null, 'no_such_processor');
        $this->shouldThrow(ResponseProcessorNotFoundException::class)->duringResolveContext('broken_context');
    }
}
