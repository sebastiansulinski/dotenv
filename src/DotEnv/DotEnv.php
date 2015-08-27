<?php namespace SSD\DotEnv;


class DotEnv
{

    /**
     * Collection of files to process.
     *
     * @var array
     */
    private $files = [];
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @param array $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
        $this->loader = new Loader($this->files, true);
    }

    /**
     * Load the files and set new environment variables
     * without overwriting the existing ones.
     *
     * @return void
     */
    public function load()
    {
        $loader = new Loader($this->files, true);
        $loader->load();
    }

    /**
     * Load the files and set new environment variables
     * overwriting the existing ones.
     *
     * @return void
     */
    public function overload()
    {
        $loader = new Loader($this->files);
        $loader->load();
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

}