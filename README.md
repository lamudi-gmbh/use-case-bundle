# Use Case Bundle

Use Case Bundle is a Symfony bundle that supports Use Case Driven Development with Symfony framework. It encourages 
designing your class in a fashion reflects the intention of your application. The tools provided by Use Case Bundle 
relieve you of the repetitive task of extracting the information required to perform the right behavior from the 
application input, which helps you output the results in the desired way. 

Installation
============

Just run 

    $ composer require lamudi/use-case-bundle

Configuration
=============

Register your bundle in AppKernel.php:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Lamudi\UseCase\LamudiUseCaseBundle(),
        );

        // ...
    }

    // ...
}
```

Enable serializer in app/config.yml:

```
# app/config.yml

framework:
    serializer: ~
    
```

Basic usage
===========

Register your Use Case as a Symfony service:

```
# app/services.yml

app.my_use_case:
    class: AppBundle\UseCase\MyUseCase
```

Using an annotation, name the Use Case and optionally assign an Input Processor and a Response Processor to it.
Make sure that the Use Case class contains an ```execute()``` method with one type-hinted parameter.

```php
<?php
// src/AppBundle/UseCase/MyUseCase.php

namespace AppBundle\UseCase;

use Lamudi\UseCaseBundle\Annotation\UseCase;

/**
 * @UseCase("My Use Case", input="http", response="json")
 */
class MyUseCase
{
    public function execute(MyUseCaseRequest $request)
    {
        // ...
    }
}
```

Use the Use Case Executor to execute your Use Cases:

```php
<?php
// src/AppBundle/Controller/MyController.php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MyController extends Controller
{
    public function myAction(Request $request)
    {
        return $this->get('lamudi_use_case.executor')->execute('My Use Case', $request);
    }
}

```

Documentation
=============

* [Concept](doc/01-concept.md) - Use Cases, Requests, and Responses explained, basic architecture and Bundle usage examples
* [Use Cases in Symfony](doc/02-use-cases-in-symfony.md) - Differences between Application and Use Case layers explained, introducing concepts of Input and Output 
* [Use Case Contexts](doc/03-use-case-contexts.md) - How to configure the way your Use Cases are executed
* [Toolkit](doc/04-toolkit.md) - Input and Response Processors shipped with the bundle
 

