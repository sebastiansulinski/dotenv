<?php

namespace SSD\DotEnv;

use Closure;

class DotEnv
{
    /**
     * Collection of files to process.
     *
     * @var array
     */
    private $files;

    /**
     * @var \SSD\DotEnv\Loader
     */
    private $loader;

    /**
     * @var string
     */
    const LOAD = 'load';

    /**
     * @var string
     */
    const OVERLOAD = 'overload';

    /**
     * DotEnv constructor.
     *
     * @param array $files
     */
    public function __construct(...$files)
    {
        $this->files = $files;
    }

    /**
     * Load the files and set new environment variables
     * without overwriting the existing ones.
     *
     * @return \SSD\DotEnv\DotEnv
     */
    public function load(): self
    {
        $this->loader = new Loader($this->files, true);
        $this->loader->load();

        return $this;
    }

    /**
     * Load the files and set new environment variables
     * overwriting the existing ones.
     *
     * @return \SSD\DotEnv\DotEnv
     */
    public function overload(): self
    {
        $this->loader = new Loader($this->files);
        $this->loader->load();

        return $this;
    }

    /**
     * Fetch variables as array without
     * setting environment variables.
     *
     * @param  string|null $loadingMethod
     * @return array
     */
    public function toArray(string $loadingMethod = null): array
    {
        $this->loader = new Loader($this->files);

        return $this->loader->toArray($loadingMethod);
    }

    /**
     * Validate entries for the existence of the given variables.
     *
     * @param  string|array $variable
     * @return \SSD\DotEnv\Validator
     */
    public function required($variable): Validator
    {
        return new Validator((array)$variable, $this->loader);
    }

    /**
     * Pull value associated with the environment variable.
     *
     * @param  string $name
     * @param  mixed|null $default
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        $value = getenv($name);

        if ($value === false) {
            return static::value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'null':
            case '(null)':
                return null;
        }

        return static::sanitise($value);
    }

    /**
     * Get value.
     *
     * @param  mixed $value
     * @return mixed
     */
    private static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Strip double quotes if found on both sides of the string.
     *
     * @param  mixed $value
     * @return mixed
     */
    private static function sanitise($value)
    {
        if ('"' === substr($value, 0, 1) && '"' === substr($value, -1)) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Check if variable with a given key
     * has a given value.
     *
     * @param  string $name
     * @param  mixed $value
     * @return bool
     */
    public static function is(string $name, $value): bool
    {
        if (!static::has($name)) {
            return false;
        }

        return static::get($name) == $value;
    }

    /**
     * Check if variable with given key is set.
     *
     * @param  string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return getenv($name) !== false;
    }

    /**
     * Set variable.
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function set(string $name, $value): void
    {
        $this->loader->setVariable($name, $value);
    }
}
