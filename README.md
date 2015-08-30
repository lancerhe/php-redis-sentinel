PHP Redis Sentinel
============

[![Latest Stable Version](https://poser.pugx.org/lancerhe/php-redis-sentinel/v/stable)](https://packagist.org/packages/lancerhe/php-redis-sentinel) [![Total Downloads](https://poser.pugx.org/lancerhe/php-redis-sentinel/downloads)](https://packagist.org/packages/lancerhe/php-redis-sentinel) [![Latest Unstable Version](https://poser.pugx.org/lancerhe/php-redis-sentinel/v/unstable)](https://packagist.org/packages/lancerhe/php-redis-sentinel) [![License](https://poser.pugx.org/lancerhe/php-redis-sentinel/license)](https://packagist.org/packages/lancerhe/php-redis-sentinel)

Crypt for AES, RSA, 3DES and some special algorithms.

Requirements
------------

**PHP5.3.0 or later**

Installation
------------

Create or modify your composer.json

``` json
{
    "require": {
        "lancerhe/php-redis-sentinel": "dev-master"
    }
}
```

Usage
-----

``` php
<?php
require('./vendor/autoload.php');

$master_name = 'my_master';
$sentinel = new \RedisSentinel\Sentinel($master_name);
$sentinel->add(new \RedisSentinel\Client('192.168.1.2', 26379));
$sentinel->add(new \RedisSentinel\Client('192.168.1.3', 26379));
$sentinel->add(new \RedisSentinel\Client('192.168.1.4', 26379));

var_dump( $sentinel->getMaster() );
var_dump( $sentinel->getSlaves() );
var_dump( $sentinel->getSlave() ); // Random, one of slaves.
```