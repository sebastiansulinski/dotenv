<?php namespace SSD\DotEnv;

use Closure;

class DotEnv
{
    /**
     * Collection of files to process.
     *
     * @var string|array
     */
    private $files;
    /**
     * @var Loader
     */
    private $loader;

    /**
     * DotEnv constructor.
     *
     * @param string|array $files
     */
    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * Load the files and set new environment variables
     * without overwriting the existing ones.
     *
     * @return DotEnv
     */
    public function load()
    {
        $this->loader = new Loader($this->files, true);
        $this->loader->load();

        return $this;
    }

    /**
     * Load the files and set new environment variables
     * overwriting the existing ones.
     *
     * @return DotEnv
     */
    public function overload()
    {
        $this->loader = new Loader($this->files);
        $this->loader->load();

        return $this;
    }

    /**
     * Validate entries for the existence of the given variables.
     *
     * @param string|array $variable
     * @return Validator
     */
    public function required($variable)
    {
        return new Validator((array) $variable, $this->loader);
    }

    /**
     * Pull value associated with the environment variable.
     *
     * @param $key
     * @param null $default
     * @return bool|null|mixed
     */
    public static function get($key, $default = null)
    {
        $value = getenv($key);

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

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return null;
        }

        return static::sanitise($value);
    }

    /**
     * Get value.
     *
     * @param $value
     * @return mixed
     */
    private static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Strip double quotes if found on both sides of the string.
     *
     * @param $value
     * @return string
     */
    private static function sanitise($value)
    {
        if ('"' === substr($value, 0, 1) && '"' === substr($value, -1)) {
            return substr($value, 1, -1);
        }

        return $value;
    }

}