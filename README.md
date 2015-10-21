Installation
============

Add the following content in your composer.json file

    "require": {
        "lamudi/angi-client" : "dev-master"
    },
    "repositories": [{
        "type": "vcs",
        "url":  "ssh://git@stash.lamudi.net:7999/lmd/angi-client.git"
    }]

then run 

    $ composer update lamudi/angi-client
