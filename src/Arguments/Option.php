<?php

namespace RayanLevert\Cli\Arguments;

use function is_string;
use function is_bool;
use function is_float;

/**
 * Enumeration of all defined options for Arguments\Argument
 */
enum Option: string
{
    /** (string) Description of an argument */
    case DESCRIPTION = 'description';

    /** (bool) If the argument must be present and parsed */
    case REQUIRED = 'required';

    /** (bool) If a prefixed argument does not need a value -> verifies only its presence by a boolean status (ex: --help) */
    case NO_VALUE = 'noValue';

    /** (string) Short prefix (-u=) */
    case PREFIX = 'prefix';

    /** (string) Long prefix (--user=) */
    case LONG_PREFIX = 'longPrefix';

    /** (string) PHP type - If the argument has a type other than string -> its value will be casted */
    case CAST_TO = 'castTo';

    /** (string|int|float) Default value if the argument is not handled */
    case DEFAULT_VALUE = 'defaultValue';

    /** From a value, verifies the type the option must require */
    public function verifiesType(mixed $value): bool
    {
        return match ($this) {
            self::DESCRIPTION   => is_string($value),
            self::REQUIRED      => is_bool($value),
            self::NO_VALUE      => is_bool($value),
            self::PREFIX        => is_string($value),
            self::LONG_PREFIX   => is_string($value),
            self::CAST_TO       => is_string($value),
            self::DEFAULT_VALUE => is_string($value) || is_int($value) || is_float($value)
        };
    }

    /** Returns the PHP property for the Arguments\Argument class */
    public function getPhpProperty(): string
    {
        return match ($this) {
            self::REQUIRED => 'isRequired',
            default        => $this->value
        };
    }
}
