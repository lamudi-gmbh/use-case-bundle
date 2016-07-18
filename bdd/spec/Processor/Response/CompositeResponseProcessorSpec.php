<?php

namespace spec\Lamudi\UseCaseBundle\Processor\Response;

use Lamudi\UseCaseBundle\Container\ContainerInterface;
use Lamudi\UseCaseBundle\Exception\UseCaseException;
use Lamudi\UseCaseBundle\Processor\Exception\EmptyCompositeProcessorException;
use Lamudi\UseCaseBundle\Processor\Response\ResponseProcessorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \Lamudi\UseCaseBundle\Processor\Response\CompositeResponseProcessor
 */
class CompositeResponseProcessorSpec extends ObjectBehavior
{
    public function let(
        ResponseProcessorInterface $responseProcessor1, ResponseProcessorInterface $responseProcessor2,
        ResponseProcessorInterface $responseProcessor3, ResponseProcessorInterface $responseProcessor4,
        ContainerInterface $responseProcessorContainer
    )
    {
        $this->beConstructedWith($responseProcessorContainer);
        $responseProcessorContainer->get('processor_1')->willReturn($responseProcessor1);
        $responseProcessorContainer->get('processor_2')->willReturn($responseProcessor2);
        $responseProcessorContainer->get('processor_3')->willReturn($responseProcessor3);
        $responseProcessorContainer->get('processor_4')->willReturn($responseProcessor4);
    }

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

        $this->processResponse($response, ['processor_1', 'processor_2'])->shouldBe($output2);
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

        $this->processResponse($response, [
            'processor_1' => ['option1' => 'value1'],
            'processor_2' => ['option2' => 'value2']
        ])->shouldBe($output2);
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

        $this->handleException($exception, ['processor_1', 'processor_2'])->shouldBe($output2);
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

        $this->handleException($exception1, [
            'processor_1' => ['option1' => 'value1'],
            'processor_2' => ['option2' => 'value2']
        ])->shouldBe($output2);
    }

    public function it_continues_handling_exception_until_no_longer_getting_one(
        ResponseProcessorInterface $responseProcessor1, ResponseProcessorInterface $responseProcessor2,
        ResponseProcessorInterface $responseProcessor3
    )
    {
        $exception1 = new \OutOfBoundsException();
        $exception2 = new \InvalidArgumentException();

        $output1 = ['exception' => 'has been finally handled'];
        $output2 = ['some' => 'processed output'];

        $responseProcessor1->handleException($exception1, [])->willThrow($exception2);
        $responseProcessor2->handleException($exception2, [])->willReturn($output1);
        $responseProcessor3->processResponse($output1, [])->willReturn($output2);

        $this->handleException(new \OutOfBoundsException(), ['processor_1', 'processor_2', 'processor_3'])->shouldBe($output2);
    }

    public function it_throws_an_exception_if_no_processors_have_been_added()
    {
        $this->shouldThrow(EmptyCompositeProcessorException::class)->duringProcessResponse('this is irrelevant here', []);
        $this->shouldThrow(EmptyCompositeProcessorException::class)->duringHandleException(new \Exception(), []);
    }
}
