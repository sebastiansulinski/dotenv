# DotEnv

Enables you to load environment variables from multiple .env files at multiple locations

This package is a work that derived form the package published by [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) and it adds functionality to handle multiple `.env` files.

## Usage instructions

To use the plugin you'll need to have at least one `.env` file i.e.

```
// .env

DB_HOST=localhost
DB_NAME=test
DB_USER=user
DB_PASS=password
```

You load all your `.env` files when instantiating the `SSD\DotEnv\DotEnv` object.

```
require "vendor/autoload.php";

use SSD\DotEnv\DotEnv;

$dotenv = new DotEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');
```

You can pass a single `.env` file, path to a directory with `.env.*` files or array of paths / directories

```
$dotenv = new DotEnv(__DIR__ . DIRECTORY_SEPARATOR . '.env');
$dotenv = new DotEnv(__DIR__);
$dotenv = new DotEnv([
    __DIR__
    'another/path',
    'another/file/.env'
]);
```

### Loading variables

To load process the variables there are two methods `load()` and `overload()`.

The `load()` method will only set the variables that do not already exist, while `overload()` will set them all - overwriting any existing ones.

```
$dotenv = new DotEnv(__DIR__);

// will only set variables
// that are not already set
$dotenv->load();
```

```
$dotenv = new DotEnv(__DIR__);

// will set all variables from the files
// overwriting any duplicates
$dotenv->overload();
```

### Required variables

To ensure that your system has all necessary variables available you can use `required()` method, which takes either a single variable name or an array of required variables.

```
$dotenv = new DotEnv(__DIR__);

// will only set variables
// that are not already set
$dotenv->load();

// either a single variable
$dotenv->required('DB_HOST');
```

```
$dotenv = new DotEnv(__DIR__);

// will only set variables
// that are not already set
$dotenv->load();

// or an array of variables
$dotenv->required([
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS'
]);
```

If any of the required variables does not exist in any of the `.env.*`files - system will throw a `RuntimeException`.

### Obtaining value stored in the variable

You can use a static `get()` method on the `DotEnv` object to retrieve the value stored in a given environment variable.

```
DotEnv::get('DB_HOST');
```

When you associate the string `true`, `false` with the variables within your `.env` file, they will automatically be converted to boolean `true` / `false` when using `DotEnv::get`.
The same applies to the variable with `null` string, which will return `null` value.

If you specify a variable without any value associated (`MY_VARIABLE=`) - it will return an empty string `''`.

You can provide a second argument to the `get()` method, which will be returned if variable was not found.
The default value can be of a `scalar` or a `Closure` type.

```
DotEnv::get('DB_HOST', 'localhost');

DotEnv::get('DB_HOST', function() {

    return DotEnv::get('ENVIRONMENT') == 'live' ? 'localhost' : 127.0.0.1;

});
```

### Checking if exists and equals

You can check if variable exists by using `has()` and whether it stores a given value by using `is()` methods.

```
DotEnv::has('ENVIRONMENT');
// true / false

DotEnv::is('ENVIRONMENT', 'live')
// true / false
```


### Variable referencing

If there is a variable that you'd like to inherit the value of you can use its name wrapped within the `${..}` i.e.

```
MAIL_SMTP=true
MAIL_USER=mail@mail.com
MAIL_PASS=password
MAIL_PORT=587

MAIL_API_KEY=${MAIL_PASS}
```