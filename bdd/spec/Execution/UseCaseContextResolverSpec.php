<?php

namespace spec\Lamudi\UseCaseBundle\Execution;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Execution\InvalidConfigurationException;
use Lamudi\UseCaseBundle\Execution\UseCaseConfiguration;
use Lamudi\UseCaseBundle\Execution\UseCaseContext;
use Lamudi\UseCaseBundle\Execution\UseCaseContextResolver;
use Lamudi\UseCaseBundle\Execution\InputProcessorNotFoundException;
use Lamudi\UseCaseBundle\Execution\ResponseProcessorNotFoundException;
use Lamudi\UseCaseBundle\Container\ItemNotFoundException;
use Lamudi\UseCaseBundle\Processor\Input\InputProcessorInterface;
use Lamudi\UseCaseBundle\Processor\Response\ResponseProcessorInterface;
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
        InputProcessorInterface $cliInputProcessor, ResponseProcessorInterface $cliResponseProcessor,
        InputProcessorInterface $compositeInputProcessor, ResponseProcessorInterface $compositeResponseProcessor
    )
    {
        $this->beConstructedWith($inputProcessorContainer, $responseProcessorContainer);

        $inputProcessorContainer->get(UseCaseContextResolver::DEFAULT_INPUT_PROCESSOR)->willReturn($defaultInputProcessor);
        $inputProcessorContainer->get('http')->willReturn($httpInputProcessor);
        $inputProcessorContainer->get('cli')->willReturn($cliInputProcessor);
        $inputProcessorContainer->get('composite')->willReturn($compositeInputProcessor);
        $responseProcessorContainer->get(UseCaseContextResolver::DEFAULT_RESPONSE_PROCESSOR)->willReturn($defaultResponseProcessor);
        $responseProcessorContainer->get('twig')->willReturn($twigResponseProcessor);
        $responseProcessorContainer->get('cli')->willReturn($cliResponseProcessor);
        $responseProcessorContainer->get('composite')->willReturn($compositeResponseProcessor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Execution\UseCaseContextResolver');
    }

    public function it_resolves_context_that_contains_input_and_response_processor(
        InputProcessorInterface $httpInputProcessor, InputProcessorInterface $cliInputProcessor,
        ResponseProcessorInterface $twigResponseProcessor, ResponseProcessorInterface $cliResponseProcessor
    )
    {
        $this->addContextDefinition('web', 'http', 'twig');
        $this->addContextDefinition('console', 'cli', 'cli');

        $webContext = $this->resolveContext('web');
        $webContext->shouldHaveType(UseCaseContext::class);
        $webContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $webContext->getResponseProcessor()->shouldBe($twigResponseProcessor);

        $consoleContext = $this->resolveContext('console');
        $consoleContext->shouldHaveType(UseCaseContext::class);
        $consoleContext->getInputProcessor()->shouldBe($cliInputProcessor);
        $consoleContext->getResponseProcessor()->shouldBe($cliResponseProcessor);
    }

    public function it_throws_an_exception_if_context_does_not_exist()
    {
        $this->shouldThrow(InvalidConfigurationException::class)->duringResolveContext('no_such_context');
        $this->setDefaultContextName('nothing_here');
        $this->shouldThrow(InvalidConfigurationException::class)->duringGetDefaultConfiguration();
    }

    public function it_resolves_context_with_options(
        InputProcessorInterface $compositeInputProcessor, ResponseProcessorInterface $compositeResponseProcessor
    )
    {
        $this->addContextDefinition('web', ['http' => ['accept' => 'text/html']], ['twig' => ['template' => 'none']]);

        $webContext = $this->resolveContext('web');
        $webContext->shouldHaveType(UseCaseContext::class);
        $webContext->getInputProcessor()->shouldBe($compositeInputProcessor);
        $webContext->getInputProcessorOptions()->shouldBe(['http' => ['accept' => 'text/html']]);
        $webContext->getResponseProcessor()->shouldBe($compositeResponseProcessor);
        $webContext->getResponseProcessorOptions()->shouldBe(['twig' => ['template' => 'none']]);
    }

    public function it_falls_back_to_default_configuration_when_context_is_incomplete(
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor
    )
    {
        $this->addContextDefinition('only_input', 'http');
        $this->addContextDefinition('only_response', null, 'twig');

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
        $this->addContextDefinition('web', 'http', 'twig');
        $this->addContextDefinition('only_input', 'cli');
        $this->addContextDefinition('only_response', null, 'cli');
        $this->setDefaultContextName('web');

        $onlyInputContext = $this->resolveContext('only_input');
        $onlyInputContext->getInputProcessor()->shouldBe($cliInputProcessor);
        $onlyInputContext->getResponseProcessor()->shouldBe($twigResponseProcessor);
        $onlyResponseContext = $this->resolveContext('only_response');
        $onlyResponseContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $onlyResponseContext->getResponseProcessor()->shouldBe($cliResponseProcessor);
    }

    public function it_overrides_the_options_of_the_default_context(
        InputProcessorInterface $compositeInputProcessor, ResponseProcessorInterface $compositeResponseProcessor
    )
    {
        $this->addContextDefinition(
            'default',
            [UseCaseContextResolver::DEFAULT_INPUT_PROCESSOR => ['option' => 'foo']],
            [UseCaseContextResolver::DEFAULT_RESPONSE_PROCESSOR => ['foo' => 'bar']]
        );
        $this->addContextDefinition('only_input', ['http' => ['accept' => 'text/html']]);
        $this->addContextDefinition('only_response', null, ['twig' => ['template' => 'none']]);

        $onlyInputContext = $this->resolveContext('only_input');
        $onlyInputContext->getInputProcessor()->shouldBe($compositeInputProcessor);
        $onlyInputContext->getInputProcessorOptions()->shouldBe(['http' => ['accept' => 'text/html']]);
        $onlyInputContext->getResponseProcessor()->shouldBe($compositeResponseProcessor);
        $onlyInputContext->getResponseProcessorOptions()->shouldBe(
            [UseCaseContextResolver::DEFAULT_RESPONSE_PROCESSOR => ['foo' => 'bar']]
        );

        $onlyResponseContext = $this->resolveContext('only_response');
        $onlyResponseContext->getInputProcessor()->shouldBe($compositeInputProcessor);
        $onlyResponseContext->getInputProcessorOptions()->shouldBe(
            [UseCaseContextResolver::DEFAULT_INPUT_PROCESSOR => ['option' => 'foo']]
        );
        $onlyResponseContext->getResponseProcessor()->shouldBe($compositeResponseProcessor);
        $onlyResponseContext->getResponseProcessorOptions()->shouldBe(['twig' => ['template' => 'none']]);
    }

    public function it_resolves_contexts_from_configuration_array(
        InputProcessorInterface $defaultInputProcessor, ResponseProcessorInterface $defaultResponseProcessor,
        InputProcessorInterface $httpInputProcessor, ResponseProcessorInterface $twigResponseProcessor
    )
    {
        $defaultContext = $this->resolveContext([
            'input' => UseCaseContextResolver::DEFAULT_INPUT_PROCESSOR,
            'response' => UseCaseContextResolver::DEFAULT_RESPONSE_PROCESSOR
        ]);
        $defaultContext->getInputProcessor()->shouldBe($defaultInputProcessor);
        $defaultContext->getResponseProcessor()->shouldBe($defaultResponseProcessor);

        $webContext = $this->resolveContext(['input' => 'http', 'response' => 'twig']);
        $webContext->getInputProcessor()->shouldBe($httpInputProcessor);
        $webContext->getResponseProcessor()->shouldBe($twigResponseProcessor);
    }

    public function it_merges_given_options_with_defaults(
        InputProcessorInterface $compositeInputProcessor, ResponseProcessorInterface $compositeResponseProcessor
    )
    {
        $this->addContextDefinition('default', ['cli' => ['yes' => true, 'maybe' => 5]], ['cli' => ['maybe' => 3, 'no' => false]]);
        $context = $this->resolveContext([
            'input' => ['http' => ['yes' => false, 'probably' => 'not']],
            'response' => ['twig' => ['yes' => true, 'maybe' => 10]]
        ]);

        $context->getInputProcessor()->shouldBe($compositeInputProcessor);
        $context->getInputProcessorOptions()->shouldBe([
            'cli' => ['yes' => true, 'maybe' => 5],
            'http' => ['yes' => false, 'probably' => 'not']
        ]);
        $context->getResponseProcessor()->shouldBe($compositeResponseProcessor);
        $context->getResponseProcessorOptions()->shouldBe([
            'cli' => ['maybe' => 3, 'no' => false],
            'twig' => ['yes' => true, 'maybe' => 10]
        ]);
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
        $inputProcessorContainer->get('no_such_processor')->willThrow(ItemNotFoundException::class);
        $this->addContextDefinition('broken_context', 'no_such_processor');
        $this->shouldThrow(InputProcessorNotFoundException::class)->duringResolveContext('broken_context');
    }

    public function it_throws_an_exception_if_response_processor_does_not_exist(ContainerInterface $responseProcessorContainer)
    {
        $responseProcessorContainer->get('no_such_processor')->willThrow(ItemNotFoundException::class);
        $this->addContextDefinition('broken_context', null, 'no_such_processor');
        $this->shouldThrow(ResponseProcessorNotFoundException::class)->duringResolveContext('broken_context');
    }
}
