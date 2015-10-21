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

Usage
=====

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
