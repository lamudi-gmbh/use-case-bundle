# Use Case Contexts
In [the previous chapter](02-use-cases-in-symfony.md) we have created an **Input Processor** and a **Response Processor**, 
and used them to provide a **Context** for the Use Case to be executed in.  It has been noted that the Use Case Layer must 
stay separated from the Application Layer in the way that the Use Cases, or any business logic, does not depend on the way 
that Input or Output is delivered. This means that we can replace the Use Case Context at any time, and the behavior of 
the application will stay intact. The most noteworthy benefit is the ability to test the application with functional tests. 
These tests would focus on verifying the behavior without resorting to performing fragile assertions that base on the UI, 
and it will be only necessary to change them when the business rules change, and nothing else.

In the example from the previous chapter, we used an annotation to define the Use Case Context. The Use Case Bundle 
provides several other means of configuring Contexts.

## Default Context
You can configure the default Context in config.yml:

```
# app/config/config.yml

lamudi_use_case:
    contexts:
        default:
            input:    my_input_processor
            response: my_response_processor
    
```

If you want to specify additional options with your default Context, the name of the Processor must be provided 
as a key, and the options must be provided as key-value pairs under that key:

```
# app/config/config.yml

lamudi_use_case:
    contexts:
        default:
            input:    
                my_input_processor:
                    foo:  bar
                    bar:  baz
            response: 
                my_response_processor:
                    format:   json
                    encoding: utf-8
    
```

These settings will be used as a fallback in case the Input Processor, the Response Processor, or their options have 
not been specified in any other way.

If you have not specified the defaults in config.yml, the default Input Processor is ```array``` and the default 
Response Processor is ```identity```.

You can also specify multiple Input and Response Processors. To do that, simply add another entry like described above.
If your Processor does not require any options, just put tilde next to the key:

```
# app/config/config.yml

lamudi_use_case:
    contexts:
        default:
            input:    
                my_input_processor:
                    foo:  bar
                    bar:  baz
                another_input_processor:
                    foo:  bar
                input_processor_without_options: ~
            response: 
                my_response_processor:
                    format:   json
                    encoding: utf-8
                another_response_processor: ~
    
```

For details about how multiple Processors work together, see chapter 
[Using multiple Input and Request Processors](05-using-multiple-input-and-request-processors.md)

## Named Contexts
Similarly to the default Context, you can define any Context and give it whatever name you wish. 

```
# app/config/config.yml

lamudi_use_case:
    contexts:
        web:
            input:    http
            response: twig
        behat:
        	input:
        	    fixture:
                    strategy: random_values
        	    
    
```

It is only possible to use named Contexts in the ```execute()``` method of the Executor.

```
<?php
$executor->execute('my_use_case', $input, 'behat');
```
Any options provided in the named Context will **override** options from the custom Context, if one exists for the 
Use Case. In case one of the Processor is not configured, the Executor will fall back to the defaults.

Once you have some named Contexts configured, it is possible to specify the name of the Context that will serve as 
the default one:

```
# app/config/config.yml

lamudi_use_case:
    contexts:
        web:
            input:    http
            response: twig
    default_context: web

```

## Custom Contexts

A Use Case-specific Context can be defined specifically for one Use Case using the ```@UseCase``` annotation:

```
@UseCase(
    "use_case_name",
    input="http",
    response="twig"
)
@UseCase(
    "another_use_case_name",
    input={
        "http"={"priority"="GPC"}
    },
    response={
        "twig"={"template"="MyBundle:default:index.html.twig"}
    }
)
```
It is possible to annotate a single Use Case class with multiple ```@UseCase``` annotations, thus configuring multiple 
Use Cases implemented with the same class.

You can also assign multiple Processors to a single Use Case:

```
@UseCase(
    "another_use_case_name",
    input={
        "my_input_processor"={"foo"="bar"},
        "input_processor_without_options"
    },
    response={
        "my_response_processor",
        "another_response_processor"
    }
)
```

For details about how multiple Processors work together, see chapter 
[Using multiple Input and Request Processors](05-using-multiple-input-and-request-processors.md)

## Anonymous Contexts

A Context can be defined ad hoc and passed as the third argument to the Executor's ```execute()``` method:

```
<?php
$executor->execute('my_use_case', $input, ['input' => 'http', 'response' => 'json']);
$executor->execute('my_use_case', $input, [
    'input' => [
        'http',
        'form' => ['name' => 'AppBundle\Form\MyForm', 'data_field' => 'myFormData']
    ],
    'response' => [
        'twig' => ['template' => 'MyBundle:default:index.html.twig']
    ]
]);
```
The options provided in an anonymous Context will be merged with those of any custom Context or the defaults.

In [the next chapter](04-toolkit.md) you will find a list of Input and Response Processors provided with the Use Case Bundle.
