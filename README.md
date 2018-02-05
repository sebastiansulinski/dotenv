# DotEnv

Package which enables to load environment variables from multiple .env files at multiple locations

This package is a work that derived from package published by [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) with some additional functionality such as handling multiple `.env` files and setting up variables using instance of the class.

[![Build Status](https://travis-ci.org/sebastiansulinski/dotenv.svg?branch=master)](https://travis-ci.org/sebastiansulinski/dotenv)

## Installation

Install package using composer

```
composer require sebastiansulinski/dotenv
```

## Usage instructions

To use the plugin you'll need to have at least one `.env` file i.e.

```php
// .env

DB_HOST=localhost
DB_NAME=test
DB_USER=user
DB_PASS=password
```

You load all your `.env` files when instantiating the `SSD\DotEnv\DotEnv` object.

```php
require "vendor/autoload.php";

use SSD\DotEnv\DotEnv;

$dotEnv = new DotEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');
```

You can pass a single `.env` file, path to a directory with `.env.*` files or multiple paths / directories

```php
$dotEnv = new DotEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');
$dotEnv = new DotEnv(__DIR__);
$dotEnv = new DotEnv(
    __DIR__
    'another/path',
    'another/file/.env'
);
```

### Loading variables

To load process the variables there are two methods `load()` and `overload()`.

The `load()` method will only set the variables that do not already exist, while `overload()` will set them all - overwriting any existing ones.

```php
$dotEnv = new DotEnv(__DIR__);

// will only set variables
// that are not already set
$dotEnv->load();
```

```php
$dotEnv = new DotEnv(__DIR__);

// will set all variables from the files
// overwriting any duplicates
$dotEnv->overload();
```

### Required variables

To ensure that your system has all necessary variables available you can use `required()` method, which takes either a single variable name or an array of required variables.

```php
$dotEnv = new DotEnv(__DIR__);

// will only set variables
// that are not already set
$dotEnv->load();

// either a single variable
$dotEnv->required('DB_HOST');
```

```php
$dotEnv = new DotEnv(__DIR__);

// will only set variables
// that are not already set
$dotEnv->load();

// or an array of variables
$dotEnv->required([
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS'
]);
```

If any of the required variables does not exist in any of the `.env.*`files - system will throw a `RuntimeException`.

### Returning contents of `.env` file(s) as array

Use `toArray()` method to fetch the contents of the `.env` file(s), with or without setting up the environment variables.

```php
$dotEnv = new DotEnv(__DIR__);

// will not set environment variables
$variables = $dotEnv->toArray();

var_dump($variables);

// ['DB_HOST' => '127.0.0.1', 'DB_NAME' => 'blog', ...]


// will set environment variables using load() method
$variables = $dotEnv->toArray(DotEnv::LOAD);

var_dump($variables);

// ['DB_HOST' => '127.0.0.1', 'DB_NAME' => 'blog', ...]


// will set environment variables using overload() method
$variables = $dotEnv->toArray(DotEnv::OVERLOAD);

var_dump($variables);

// ['DB_HOST' => '127.0.0.1', 'DB_NAME' => 'blog', ...]
```

### Obtaining value stored in the variable

You can use a static `get()` method on the `DotEnv` object to retrieve the value stored in a given environment variable.

```php
DotEnv::get('DB_HOST');
```

When you associate the string `true`, `false` with the variables within your `.env` file, they will automatically be converted to boolean `true` / `false` when using `DotEnv::get`.
The same applies to the variable with `null` string, which will return `null` value.

If you specify a variable without any value associated (`MY_VARIABLE=`) - it will return an empty string `''`.

You can provide a second argument to the `get()` method, which will be returned if variable was not found.
The default value can be of a `scalar` or a `Closure` type.

```php
DotEnv::get('DB_HOST', 'localhost');

DotEnv::get('DB_HOST', function() {

    return DotEnv::get('ENVIRONMENT') == 'live' ? 'localhost' : 127.0.0.1;

});
```

### Checking if exists and equals

You can check if variable exists by using `has()` and whether it stores a given value by using `is()` methods.

```php
DotEnv::has('NON_EXISTENT_VARIABLE');
// false

DotEnv::is('ENVIRONMENT', 'live')
// true / false
```

### Setting variables

```php
$dotEnv = new DotEnv(__DIR__);
$dotEnv->load();
$dotEnv->set('CUSTOM_VARIABLE', 123);
$dotEnv->required('CUSTOM_VARIABLE');
```

### Variable referencing

If there is a variable that you'd like to inherit the value of you can use its name wrapped within the `${..}` i.e.

```php
MAIL_SMTP=true
MAIL_USER=mail@mail.com
MAIL_PASS=password
MAIL_PORT=587

MAIL_API_KEY=${MAIL_PASS}
```