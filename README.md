Installation
============

Add the following content in your composer.json file

    "require": {
        "lamudi/use-case-bundle" : "~0.2"
    },
    "repositories": [{
        "type": "vcs",
        "url":  "ssh://git@bitbucket.lamudi.com:7999/lmd/use-case-bundle.git"
    }]

then run 

    $ composer update lamudi/use-case-bundle

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

Register your use case as a Symfony service:

```
# app/services.yml

app.my_use_case:
    class: AppBundle\UseCase\MyUseCase
```

Using an annotation, name the use case and optionally assign an input processor and a response processor to it.
Make sure that the use case class contains an ```execute()``` method with one type-hinted parameter.

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

Use the use case executor to retrieve your use cases:

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

Refer to [the documentation](https://confluence.lamudi.com/display/AP/UseCaseBundle) for more details.
