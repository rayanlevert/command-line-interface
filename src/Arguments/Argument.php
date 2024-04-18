<?php

namespace RayanLevert\Cli\Arguments;

use function array_key_exists;
use function is_string;
use function is_bool;
use function is_double;
use function is_int;
use function gettype;
use function is_numeric;
use function implode;

/**
 * An argument from a console application viewpoint
 */
class Argument
{
    private string $description = '';

    private string|int|float|null $defaultValue = null;

    private bool $isRequired = false;

    private bool $noValue = false;

    private string $castTo = 'string';

    private string $prefix = '';

    private string $longPrefix = '';

    private string|int|float|bool $valueParsed = '';

    private bool $hasBeenHandled = false;

    /**
     * Creates an argument with a name and differents options
     *
     * @param array<string, string|bool|int|float> $options
     * - description (string) Description of the argument
     * - defaultValue (float|int|string) Default value if the argument is not handled
     * - required (bool) If the argument must be present and parsed
     * - castTo (string) PHP type - If the argument has a type other than string, its value will be casted
     * - noValue (bool) If a prefixed argument doesn't need a value -> boolean cast
     * - prefix (string) Short prefix (-u)
     * - longPrefix (string) Long prefix (--user)
     *
     * @throws \RayanLevert\Cli\Arguments\Exception If options are incompatible or incorrectes
     */
    final public function __construct(protected readonly string $name, array $options = [])
    {
        if (array_key_exists('description', $options) && is_string($options['description'])) {
            $this->description = $options['description'];
        }

        if (array_key_exists('required', $options) && is_bool($options['required'])) {
            $this->isRequired = $options['required'];
        }

        if (array_key_exists('noValue', $options) && is_bool($options['noValue'])) {
            $this->noValue = $options['noValue'];
        }

        if (array_key_exists('prefix', $options) && is_string($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }

        if (array_key_exists('longPrefix', $options) && is_string($options['longPrefix'])) {
            $this->longPrefix = $options['longPrefix'];
        }

        if (array_key_exists('castTo', $options) && is_string($options['castTo'])) {
            $this->castTo = match ($options['castTo']) {
                'int', 'integer'  => 'integer',
                'bool', 'boolean' => throw new Exception('castTo cannot be of type bool, use the option "noValue"'),
                'double', 'float' => 'double',
                'string'          => 'string',
                default           => throw new Exception($options['castTo'] . ' is not a native PHP type')
            };
        }

        if (array_key_exists('defaultValue', $options)) {
            $defaultValue = $options['defaultValue'];

            if (!is_string($defaultValue) && !is_double($defaultValue) && !is_int($defaultValue)) {
                throw new Exception('Default value must be of type float, integer or string');
            }

            $this->defaultValue = $defaultValue;

            // Asserts the default value type is the same as the castTo option
            if (gettype($this->defaultValue) !== $this->castTo) {
                throw new Exception("Default value is not the same type as castTo option ({$this->castTo})");
            }
        }

        if (($this->noValue || $this->isRequired) && $this->defaultValue) {
            throw new Exception('A noValue|required argument cannot have the default value');
        }

        if ($this->isRequired && ($this->prefix || $this->longPrefix)) {
            throw new Exception('A prefixed argument cannot be required');
        }
    }

    /**
     * Returns the value of the argument after parsed, if not returns the default value
     */
    public function getValue(): string|int|float|bool|null
    {
        if (!$this->hasBeenHandled) {
            return $this->noValue ? false : $this->defaultValue;
        }

        return $this->valueParsed;
    }

    /**
     * Parses the argument setting its value
     *
     * @param bool|string $value If string -> tries to cast, if bool -> must have its noValue option
     *
     * @throws \RayanLevert\Cli\Arguments\ParseException If the parsed value is not of casted type
     */
    public function setValueParsed(bool|string $value): void
    {
        if (is_bool($value) && $this->noValue) {
            $this->valueParsed    = $value;
            $this->hasBeenHandled = true;

            return;
        } elseif ($this->castTo === 'string') {
            $this->valueParsed    = $value;
            $this->hasBeenHandled = true;

            return;
        }

        // Thorws an exception if the value is not of casted type
        if ($this->castTo === 'integer') {
            if (!is_numeric($value)) {
                throw new ParseException("Argument {$this->name} is not a numeric string (must cast to integer)");
            }

            $this->valueParsed = intval($value);
        } elseif ($this->castTo === 'double') {
            if (!is_numeric($value)) {
                throw new ParseException("Argument {$this->name} is not a floating point number (must cast to float)");
            }

            $this->valueParsed = floatval($value);
        }

        $this->hasBeenHandled = true;
    }

    /**
     * Returns necessary informations of thr argument to display
     */
    public function getInfos(): string
    {
        $toPrint = [];

        if ($prefix = $this->prefix) {
            $toPrint[] = $this->noValue ? " -$prefix" : " -$prefix={$this->name}";
        }

        if ($prefix = $this->longPrefix) {
            $toPrint[] = $this->noValue ? " --$prefix" : " --$prefix={$this->name}";
        }

        $toPrint = implode(',', $toPrint);

        if (!$this->noValue) {
            $toPrint .= ' (type: ' . $this->castTo . ')';
        }

        if ($this->defaultValue) {
            $toPrint .= ' (default: ' . $this->defaultValue . ')';
        }

        if ($this->description) {
            $toPrint .= "\n\t  " . $this->description;
        }

        return $this->name . $toPrint;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLongPrefix(): string
    {
        return $this->longPrefix;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function hasNoValue(): bool
    {
        return $this->noValue;
    }

    public function getCastTo(): string
    {
        return $this->castTo;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function getDefaultValue(): string|int|float|null
    {
        return $this->defaultValue;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasBeenHandled(): bool
    {
        return $this->hasBeenHandled;
    }
}
