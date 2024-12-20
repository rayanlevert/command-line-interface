<?php

namespace RayanLevert\Cli\Arguments;

use function is_bool;
use function gettype;
use function is_numeric;
use function implode;

/**
 * An argument from a console application viewpoint
 */
class Argument
{
    public private(set) string $description = '';

    public private(set) string|int|float|null $defaultValue = null;

    public private(set) bool $isRequired = false;

    public private(set) bool $noValue = false;

    public private(set) string $castTo = 'string';

    public private(set) string $prefix = '';

    public private(set) string $longPrefix = '';

    public private(set) string|int|float|bool $valueParsed = '';

    public private(set) bool $hasBeenHandled = false;

    /**
     * Creates an argument with a name and different options
     *
     * @param array<string, string|bool|int|float> $options See Arguments\Option cases for more informations
     *
     * @throws \RayanLevert\Cli\Arguments\Exception If options are incompatible or incorrect
     */
    public function __construct(public protected(set) readonly string $name, array $options = [])
    {
        foreach ($options as $name => $value) {
            if (!($option = Option::tryFrom($name)) || !$option->verifiesType($value)) {
                continue;
            }

            $this->{$option->getPhpProperty()} = $value;
        }

        if ($this->castTo) {
            $this->castTo = match ($this->castTo) {
                'int', 'integer'  => 'integer',
                'bool', 'boolean' => throw new Exception('castTo cannot be of type bool, use the option "noValue"'),
                'double', 'float' => 'double',
                'string'          => 'string',
                default           => throw new Exception($this->castTo . ' is not a native PHP type')
            };
        }

        // Asserts the default value type is the same as the castTo option
        if ($this->defaultValue && gettype($this->defaultValue) !== $this->castTo) {
            throw new Exception("Default value is not the same type as castTo option ({$this->castTo})");
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

        // Throws an exception if the value is not of casted type
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
}
