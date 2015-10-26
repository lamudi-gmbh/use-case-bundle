Installation
============

Add the following content in your composer.json file

    "require": {
        "lamudi/use-case-bundle" : "dev-master"
    },
    "repositories": [{
        "type": "vcs",
        "url":  "ssh://git@stash.lamudi.net:7999/lmd/use-case-bundle.git"
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
// app/config.yml

framework:
    serializer: ~
    
```

Usage
=====

1. Register your use cases as Symfony services and tag them as "use_case":

```
// app.services.yml

app.my_use_case:
    class: AppBundle\UseCase\MyUseCase
    tags:
        - { name: use_case }

```

2. Use the use case container to retrieve your use cases:

```php
$this->get('lamudi_use_case.container')->get('app.my_use_case');
```
