<?php

namespace SSD\DotEnv;

use DirectoryIterator;
use InvalidArgumentException;

class Loader
{
    /**
     * @var string|array
     */
    private $files;

    /**
     * @var bool
     */
    private $immutable = false;

    /**
     * @var array
     */
    private $lines = [];

    /**
     * @var bool
     */
    private $setter = true;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * Loader constructor.
     *
     * @param  array $files
     * @param  bool|false $immutable
     */
    public function __construct(array $files, $immutable = false)
    {
        $this->files = $files;
        $this->immutable = $immutable;
    }

    /**
     * Load the files and return all variables as array
     * without setting environment variables.
     *
     * @param  string|null $loadingMethod
     * @return array
     */
    public function toArray(string $loadingMethod = null): array
    {
        if (in_array($loadingMethod, [DotEnv::LOAD, DotEnv::OVERLOAD])) {

            $this->immutable = $loadingMethod === DotEnv::LOAD;

            $this->setter = true;

        } else {

            $this->setter = false;

        }
        
        $this->getContent();

        $this->processEntries();

        return $this->attributes;
    }

    /**
     * Load files and set variables.
     *
     * @return void
     */
    public function load(): void
    {
        $this->setter = true;

        $this->getContent();

        $this->processEntries();
    }

    /**
     * Process given file(s).
     *
     * @return void
     */
    private function getContent(): void
    {
        foreach ($this->files as $item) {

            if (is_file($item)) {
                $this->readFileContent($item);
            }

            if (is_dir($item)) {
                $this->readDirectoryContent($item);
            }
        }
    }

    /**
     * Read content of a file
     * and add its lines to the collection.
     *
     * @param  string $file
     * @return void
     */
    private function readFileContent(string $file): void
    {
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        $this->lines = array_merge($this->lines, $lines);
    }

    /**
     * Read content of a directory
     * and add each qualifying file's
     * content lines to the collection.
     *
     * @param  string $directory
     * @return void
     */
    private function readDirectoryContent(string $directory): void
    {
        $items = new DirectoryIterator($directory);

        foreach ($items as $item) {

            if ($item->isFile() && $this->isFile($item->getFilename())) {
                $this->readFileContent($item->getPathname());
            }
        }
    }

    /**
     * Check if the given file name
     * qualifies as the environment file.
     *
     * @param  string $name
     * @return bool
     */
    private function isFile(string $name): bool
    {
        return substr($name, 0, 4) === '.env';
    }

    /**
     * Validate lines fetch from the files
     * and set variables accordingly.
     *
     * @return void
     */
    private function processEntries(): void
    {
        if (empty($this->lines)) {
            return;
        }

        foreach ($this->lines as $line) {

            if ($this->isLineComment($line) || !$this->isLineSetter($line)) {
                continue;
            }

            $this->setVariable($line);
        }
    }

    /**
     * Determine if the given line contains a "#" symbol at the beginning.
     *
     * @param  string $line
     * @return bool
     */
    private function isLineComment(string $line): bool
    {
        return strpos(trim($line), '#') === 0;
    }

    /**
     * Determine if the given line contains an "=" symbol.
     *
     * @param  string $line
     * @return bool
     */
    protected function isLineSetter(string $line): bool
    {
        return strpos($line, '=') !== false;
    }

    /**
     * Set environment variable
     * using: putenv, $_ENV and $_SERVER.
     *
     * Value is stripped of single and double quotes.
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function setVariable(string $name, $value = null): void
    {
        [$name, $value] = $this->normaliseVariable($name, $value);

        $this->attributes[$name] = $value;

        if (!$this->setter || ($this->immutable && !is_null($this->getVariable($name)))) {
            return;
        }

        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    public function destroyVariable()
    {
        
    }

    /**
     * Normalise given name and value.
     *
     * - Splits the string using "=" symbol
     * - Strips the quotes from name and value
     * - Resolves nested variables
     *
     * @param  string $name
     * @param  mixed $value
     * @return array
     */
    private function normaliseVariable(string $name, $value): array
    {
        [$name, $value] = $this->sanitiseVariableValue(
            ...$this->sanitiseVariableName(
                ...$this->splitStringIntoParts($name, $value)
            )
        );

        $value = $this->resolveNestedVariables($value);

        return [$name, $value];
    }

    /**
     * Split name into parts.
     *
     * If the "$name" contains a "=" symbol
     * we split it into "$name" and "$value"
     * disregarding "$value" argument of the method.
     *
     * @param  string $name
     * @param  mixed $value
     * @return array
     */
    private function splitStringIntoParts(string $name, $value): array
    {
        if (strpos($name, '=') !== false) {
            [$name, $value] = array_map('trim', explode('=', $name, 2));
        }

        return [$name, $value];
    }

    /**
     * Strip quotes and the optional leading "export"
     * from the name.
     *
     * @param  string $name
     * @param  mixed $value
     * @return array
     */
    private function sanitiseVariableName(string $name, $value): array
    {
        $name = trim(str_replace(['export ', '\'', '"'], '', $name));

        return [$name, $value];
    }

    /**
     * Strip quotes from the value.
     *
     * @param  string $name
     * @param  mixed $value
     * @return array
     */
    private function sanitiseVariableValue(string $name, $value): array
    {
        $value = trim($value);

        if (!$value) {
            return [$name, $value];
        }

        $value = $this->getSanitisedValue($value);

        return [$name, trim($value)];
    }

    /**
     * Return value without the quotes.
     *
     * @param  mixed $value
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function getSanitisedValue($value)
    {
        if ($this->beginsWithQuote($value)) {

            $quote = $value[0];

            $regexPattern = sprintf(
                '/^
                %1$s          # match a quote at the start of the value
                (             # capturing sub-pattern used
                 (?:          # we do not need to capture this
                  [^%1$s\\\\] # any character other than a quote or backslash
                  |\\\\\\\\   # or two backslashes together
                  |\\\\%1$s   # or an escaped quote e.g \"
                 )*           # as many characters that match the previous rules
                )             # end of the capturing sub-pattern
                %1$s          # and the closing quote
                .*$           # and discard any string after the closing quote
                /mx',
                $quote
            );

            $value = preg_replace($regexPattern, '$1', $value);
            $value = str_replace("\\$quote", $quote, $value);
            $value = str_replace('\\\\', '\\', $value);

            return $value;
        }

        $parts = explode(' #', $value, 2);
        $value = trim($parts[0]);

        if (preg_match('/\s+/', $value) > 0) {
            throw new InvalidArgumentException('DotEnv values containing spaces must be surrounded by quotes.');
        }

        return $value;
    }

    /**
     * Determine if the given value begins with a single or double quote.
     *
     * @param  mixed $value
     * @return bool
     */
    private function beginsWithQuote($value): bool
    {
        return strpbrk($value[0], '"\'') !== false;
    }

    /**
     * Return nested variable.
     *
     * Look for {$varname} patterns in the variable value
     * and replace with an existing environment variable.
     *
     * @param  mixed $value
     * @return mixed
     */
    private function resolveNestedVariables($value)
    {
        if (strpos($value, '$') === false) {
            return $value;
        }

        $loader = $this;

        return preg_replace_callback(
            '/^\${([a-zA-Z0-9_]+)}/',
            function ($matchedPatterns) use ($loader) {
                $nestedVariable = $loader->getVariable($matchedPatterns[1]);
                if (is_null($nestedVariable)) {
                    return $matchedPatterns[0];
                }

                return $nestedVariable;
            },
            $value
        );
    }

    /**
     * Get environment variable by name
     * from either: $_ENV, $_SERVER or getenv.
     *
     * @param  string $name
     * @return mixed
     */
    public function getVariable(string $name)
    {
        switch (true) {
            case array_key_exists($name, $_ENV):
                return $_ENV[$name];
            case array_key_exists($name, $_SERVER):
                return $_SERVER[$name];
            default:
                $value = getenv($name);

                return $value === false ? null : $value;
        }
    }

    /**
     * Clear environment variable by name.
     *
     * @param  string $name
     */
    public function clearVariable(string $name)
    {
        if ($this->immutable) {
            return;
        }

        putenv($name);
        unset($_ENV[$name]);
        unset($_SERVER[$name]);
    }

}
