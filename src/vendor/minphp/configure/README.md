# Minphp/Configure

[![Build Status](https://travis-ci.org/phillipsdata/minphp-configure.svg?branch=master)](https://travis-ci.org/phillipsdata/minphp-configure) [![Coverage Status](https://coveralls.io/repos/phillipsdata/minphp-configure/badge.svg)](https://coveralls.io/r/phillipsdata/minphp-configure)

A generic configuration library for getting and setting values for keys.

## Installation

Install via composer:

```sh
composer require minphp/configure:~2.0
```

## Basic Usage

```php
<?php
use Minphp\Configure\Configure;

$config = new Configure();
$config->set('key', 'value');
$config->get('key');
```

### Supported Actions

- ```$config->set($key, $value)``` - Add or update a value in the config
- ```$config->get($key)``` - Get a value from the config
- ```$config->exists($key)``` - Find if a key is set in the config
- ```$config->remove($key)``` - Remove a key from the config

## Using Config Files

Configure currently supports the following formats:

- PHP (a file that returns an array or object supported by \ArrayIterator)
- JSON

**config.php**
```php
<?php
return array(
    'key1' => 'value',
    'key2' => array('key' => 'value')
);

```

**config.json**
```json
{
    "key1": "value",
    "key2": {"property": "value"}
}
```

**usage.php**
```php
<?php
use Minphp\Configure\Configure;

$config = new Configure();
$config->load(new Reader\PhpReader(new \SplFileObject('config.php')));
echo $config->get('key1'); // prints "value"
echo $config->get('key2')['key']; // prints "value";

$config->load(new Reader\JsonReader(new \SplFileObject('config.json')));

echo $config->get('key1'); // prints "value"
echo $config->get('key2')->property; // prints "value";
```

**Note:** Configure won't mess with your data. JSON objects are returned as
actual objects, not hashes.

A literal translation of the above config.json file would be:
```php
return array(
    'key' => 'value',
    'key2' => (object)array('key' => 'value')
);
```
