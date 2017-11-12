<?php

namespace SSDTest;

use PHPUnit\Framework\TestCase;

abstract class BaseCase extends TestCase
{
    /**
     * Absolute path to the files directory.
     *
     * @var string
     */
    protected $path = __DIR__.DIRECTORY_SEPARATOR.'files';

    /**
     * Test variables.
     *
     * @var array
     */
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
        'MAIL_API_KEY',
        'DOUBLE_QUOTE_BOTH',
        'DOUBLE_QUOTE_LEFT',
        'DOUBLE_QUOTE_RIGHT',
        'SINGLE_QUOTE_BOTH',
        'SINGLE_QUOTE_LEFT',
        'SINGLE_QUOTE_RIGHT'
    ];

    /**
     * Absolute path to the file within files directory.
     *
     * @param  string $file
     * @return string
     */
    protected function pathname(string $file): string
    {
        return $this->path.DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Absolute path to the file within files/overwrite directory.
     *
     * @param  string $file
     * @return string
     */
    protected function overwrite_pathname(string $file): string
    {
        return $this->path.DIRECTORY_SEPARATOR.'overwrite'.DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Absolute path to the file within files/quotes directory.
     *
     * @param  string $file
     * @return string
     */
    protected function quotes_pathname(string $file): string
    {
        return $this->path.DIRECTORY_SEPARATOR.'quotes'.DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Action to be performed after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->variables as $variable) {

            putenv($variable);
            unset($_ENV[$variable]);
            unset($_SERVER[$variable]);
        }
    }
}
