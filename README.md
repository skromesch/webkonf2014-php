webkonf2014-php
===============

Contact Service in PHP + ZF2


Installation
------------

Using Composer (recommended)
----------------------------

    php composer.phar self-update
    php composer.phar install

(The `self-update` directive is to ensure you have an up-to-date `composer.phar`
available.)

Configuration
------------

Set your AWS key in config/autoload/aws.global.php

Web Server Setup
----------------

### PHP CLI Server

The simplest way to get started if you are using PHP 5.4 or above is to start the internal PHP cli-server in the root directory:

    php -S 0.0.0.0:8080 -t public/ public/index.php

This will start the cli-server on port 8080, and bind it to all network
interfaces.

