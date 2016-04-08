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

1. Register your bundle in AppKernel.php:

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

2. Enable serializer in app/config.yml:

```
# app/config.yml

framework:
    serializer: ~
    
```

Basic usage
===========

1. Register your use case as a Symfony service:

```
// app.services.yml

app.my_use_case:
    class: AppBundle\UseCase\MyUseCase

```


2. Using an annotation, name the use case and optionally assign an input processor and a response processor to it.
Make sure that the use case class contains an execute() method with one type-hinted parameter.

```php
// AppBundle/UseCase/MyUseCase.php

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


3. Use the use case executor to retrieve your use cases:

```php
$this->get('lamudi_use_case.executor')->get('app.my_use_case');
```
