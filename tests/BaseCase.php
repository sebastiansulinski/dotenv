<?php namespace SSDTest;

use PHPUnit_Framework_TestCase;

abstract class BaseCase extends PHPUnit_Framework_TestCase
{
    /**
     * Absolute path to the files directory.
     *
     * @var string
     */
    protected $path = __DIR__ . DIRECTORY_SEPARATOR . 'files';

    protected $variables = [
        'ENVIRONMENT',
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
        'MAIL_SMTP',
        'MAIL_USER',
        'MAIL_PASS',
        'MAIL_PORT',
        'MAIL_API_KEY'
    ];

    /**
     * Absolute path to the file within files directory.
     *
     * @param $file
     * @return string
     */
    protected function pathname($file)
    {
        return $this->path . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Action to be performed after each test.
     *
     * @return void
     */
    protected function tearDown()
    {
        foreach($this->variables as $variable) {

            putenv($variable);
            unset($_ENV[$variable]);
            unset($_SERVER[$variable]);

        }
    }
}