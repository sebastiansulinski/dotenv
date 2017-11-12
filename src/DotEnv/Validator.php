<?php

namespace SSD\DotEnv;

use RuntimeException;

class Validator
{
    /**
     * @var array
     */
    private $variables = [];

    /**
     * @var \SSD\DotEnv\Loader
     */
    private $loader;

    /**
     * Validator constructor.
     *
     * @param  array $variables
     * @param  \SSD\DotEnv\Loader $loader
     */
    public function __construct(array $variables, Loader $loader)
    {
        $this->variables = $variables;
        $this->loader = $loader;

        $this->notNull();
    }

    /**
     * Assert that the callback returns true for each variable.
     *
     * @param  callable $callback
     * @param  string $message
     * @return \SSD\DotEnv\Validator
     */
    private function assertCallback(callable $callback, string $message = 'failed callback assertion'): Validator
    {
        $variablesFailingAssertion = [];

        foreach ($this->variables as $variableName) {

            $variableValue = $this->loader->getVariable($variableName);

            if (call_user_func($callback, $variableValue) === false) {
                $variablesFailingAssertion[] = $variableName." $message";
            }
        }

        if (count($variablesFailingAssertion) > 0) {

            throw new RuntimeException(sprintf(
                'One or more environment variables failed validation: %s',
                implode(', ', $variablesFailingAssertion)
            ));
        }

        return $this;
    }

    /**
     * Assert that each variable is not null.
     *
     * @return \SSD\DotEnv\Validator
     */
    public function notNull(): Validator
    {
        return $this->assertCallback(
            function ($value) {
                return !is_null($value);
            },
            'is missing'
        );
    }

    /**
     * Assert that each variable is not an empty string.
     *
     * @return \SSD\DotEnv\Validator
     */
    public function notEmpty(): Validator
    {
        return $this->assertCallback(
            function ($value) {
                return (strlen(trim($value)) > 0);
            },
            'is empty'
        );
    }

    /**
     * Assert that each variable's value
     * matches one from the given collection.
     *
     * @param  array $choices
     * @return \SSD\DotEnv\Validator
     */
    public function allowedValues(array $choices): Validator
    {
        return $this->assertCallback(
            function ($value) use ($choices) {
                return in_array($value, $choices);
            },
            'is not an allowed value'
        );
    }

}
