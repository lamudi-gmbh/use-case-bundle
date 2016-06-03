<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Processor\Response\ResponseProcessorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Processor\Response\CompositeResponseProcessor
 */
class CompositeResponseProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Lamudi\UseCaseBundle\Processor\Response\CompositeResponseProcessor');
    }

    public function it_is_a_response_processor()
    {
        $this->shouldHaveType(ResponseProcessorInterface::class);
    }

    public function it_passes_the_result_of_the_previous_response_processing_to_the_next_processor(
        ResponseProcessorInterface $responseProcessor1, ResponseProcessorInterface $responseProcessor2
    )
    {
        $response = ['some' => 'response'];
        $output1 = ['some' => 'processed output'];
        $output2 = ['some' => 'further processed output'];

        $responseProcessor1->processResponse($response, [])->willReturn($output1);
        $responseProcessor2->processResponse($output1, [])->willReturn($output2);

        $this->addResponseProcessor($responseProcessor1);
        $this->addResponseProcessor($responseProcessor2);

        $this->processResponse($response)->shouldBe($output2);
    }

    public function it_uses_options_per_response_processor(
        ResponseProcessorInterface $responseProcessor1, ResponseProcessorInterface $responseProcessor2
    )
    {
        $response = ['some' => 'response'];
        $output1 = ['some' => 'output processed with options'];
        $output2 = ['some' => 'further processed output with more options'];

        $responseProcessor1->processResponse($response, ['option1' => 'value1'])->willReturn($output1);
        $responseProcessor2->processResponse($output1, ['option2' => 'value2'])->willReturn($output2);

        $this->addResponseProcessor($responseProcessor1, ['option1' => 'value1']);
        $this->addResponseProcessor($responseProcessor2, ['option2' => 'value2']);

        $this->processResponse($response)->shouldBe($output2);
    }

    public function it_merges_its_options_with_options_specified_per_processor(
        ResponseProcessorInterface $responseProcessor1, ResponseProcessorInterface $responseProcessor2
    )
    {
        $response = ['some' => 'response'];
        $output1 = ['some' => 'output processed with options'];
        $output2 = ['some' => 'further processed output with more options'];

        $responseProcessor1->processResponse($response, ['option1' => 'value1', 'global_option' => 'global value'])
            ->willReturn($output1);
        $responseProcessor2->processResponse($output1, ['option2' => 'value2', 'global_option' => 'global value'])
            ->willReturn($output2);

        $this->addResponseProcessor($responseProcessor1, ['option1' => 'value1', 'global_option' => 'is overridden']);
        $this->addResponseProcessor($responseProcessor2, ['option2' => 'value2']);

        $this->processResponse($response, ['global_option' => 'global value'])->shouldBe($output2);
    }

    public function it_handles_exceptions_and_forwards_the_result_to_the_next_processor(
        ResponseProcessorInterface $responseProcessor1, ResponseProcessorInterface $responseProcessor2
    )
    {
        $exception = new UseCaseException();
        $output1 = ['some' => 'output processed with options'];
        $output2 = ['some' => 'further processed output with more options'];

        $responseProcessor1->handleException($exception, [])->willReturn($output1);
        $responseProcessor2->processResponse($output1, [])->willReturn($output2);

        $this->addResponseProcessor($responseProcessor1);
        $this->addResponseProcessor($responseProcessor2);

        $this->handleException($exception)->shouldBe($output2);
    }

    public function it_handles_exceptions_using_options(
        ResponseProcessorInterface $responseProcessor1, ResponseProcessorInterface $responseProcessor2
    )
    {
        $exception1 = new UseCaseException();
        $output1 = ['some' => 'output processed with options'];
        $output2 = ['some' => 'further processed output with more options'];

        $responseProcessor1->handleException($exception1, ['option1' => 'value1'])->willReturn($output1);
        $responseProcessor2->processResponse($output1, ['option2' => 'value2'])->willReturn($output2);

        $this->addResponseProcessor($responseProcessor1, ['option1' => 'value1']);
        $this->addResponseProcessor($responseProcessor2, ['option2' => 'value2']);

        $this->handleException($exception1)->shouldBe($output2);
    }
}
