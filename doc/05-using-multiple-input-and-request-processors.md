# Using multiple Input and Request Processors
The Use Case Bundle allows you to use more than one Input Processor to get the data from the Input, and more than
one Response Processor to process the Response and create the appropriate Output. The instructions on how to 
configure the Use Case Executor to use multiple Processors have been written in chapter 
[Use Case Contexts](03-use-case-contexts.md). In this chapter you will find details about the workflow of multiple
Processors.

## Input Processors Workflow

The Input Processors will try to initialize the Use Case Request in the specified order. The fields initialized
by the Processors executed earlier may be overriden by the Processors executed later.

After the `initializeRequest()` method of the last Processor in chain has been executed, the Request is passed
to the Use Case's `execute()` method.

## Response Processors Workflow

The Response Processors handle two different cases: success and failure. This makes using multiple Response
Processors a little more complicated than using multiple Input Processors.

It is also important to note that, unlike Input Processors, only the last Response Processor actually knows 
what object will be returned by the Executor. All the previous Processors have to be configured in a way
that they will provide the next Processor the kind of object that the next Processor can handle. 
For example, the Twig Response Processor always returns instances of Symfony HTTP Response, so the Response
Processor that is configured to run after it must work with Symfony HTTP Responses. Conversely, if a Response
Processor is to run before the Twig Response Processor, it must return an instance of `\stdClass` or another
kind of object with public fields, because that's what Twig Response Processor expects to receive in its
`processResponse()` method.

### Scenario 1: Everything went well

If the Use Case returns a Response object, the first Response Processor in chain processes it with
`processResponse()` method, then passes the created object to the next Processor's `processResponse()` method. 
The last Processor's result is returned by the Executor.

### Scenario 2: Something went wrong in the Use Case

If the Use Case throws an Exception, the first Response Processor in chain catches it and tries to handle it 
with the `handleException()` method. If the handling is successful (i.e. `handleException()` returns an object, 
rather than throwing an Exception), the next Processor in chain will use its `processResponse()` method to handle
that object and the chain of Response Processor execution continues as in scenario 1.
 
If no Processor has been able to properly handle the Exception, it is thrown by the Executor.

### Scenario 3: Something went wrong while processing the Response

If the Use Case returns a Response, but one of the Response Processors throws an Exception, it is thrown by
the Executor. The purpose of `handleException()` method is to handle the alternative courses of Use Case execution,
and Exceptions thrown by Processors should be unrelated to the business logic of the application.
