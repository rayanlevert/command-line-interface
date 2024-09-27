<?php

namespace RayanLevert\Cli;

use RayanLevert\Cli\Arguments\Argument;
use RayanLevert\Cli\Arguments\Exception;
use RayanLevert\Cli\Arguments\ParseException;

use function count;
use function substr;
use function strncmp;
use function current;
use function key;
use function reset;
use function next;
use function strlen;
use function str_replace;
use function function_exists;

/**
 * Collection of `Arguments\Argument` possessing arguments of an CLI application (`$argv`)
 *
 * @implements \IteratorAggregate<string, Argument>
 */
class Arguments implements \IteratorAggregate, \Countable
{
    /**
     * @var array<string, Argument>
     */
    protected array $data = [];

    /**
     * Initializes the collection adding arguments
     */
    public function __construct(Argument ...$oArguments)
    {
        foreach ($oArguments as $oArgument) {
            $this->data[$oArgument->getName()] = $oArgument;
        }

        $this->assertOrderRequired();
    }

    /**
     * @return \Generator<string, Argument>
     */
    public function getIterator(): \Generator
    {
        yield from $this->data;
    }

    /**
     * Adds an argument
     */
    public function set(Argument $oArgument): void
    {
        $this->data[$oArgument->getName()] = $oArgument;

        $this->assertOrderRequired();
    }

    /**
     * Recovers an argument's value in the collection (gets its default value if not handled yet)
     *
     * @throws \RayanLevert\Cli\Arguments\Exception If the argument doesn't exist in the collection
     */
    public function get(string $argumentName): string|int|float|bool|null
    {
        if (!$oArgument = $this->retrieve($argumentName)) {
            throw new Exception("Argument $argumentName does not exist in the collection");
        }

        return $oArgument->getValue();
    }

    /**
     * Removes an argument
     */
    public function remove(string $argumentName): void
    {
        unset($this->data[$argumentName]);
    }

    /**
     * Returns the number of arguments
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Parses the arguments from the collection checking required arguments, with or without prefixes
     *
     * Sets each argument's value from the arguments passed to the method (...$argv)
     *
     * @param string ...$arguments Parsed arguments (`...$argv` for arguments from your CLI)
     *
     * @throws \RayanLevert\Cli\Arguments\Exception If a required argument has not been set or any argument error
     */
    public function parse(string ...$arguments): void
    {
        // If no argument has been added, no need to parse
        if (!$this->count()) {
            return;
        }

        // Loops a first time to recover all prefixed arguments
        foreach ($arguments as $index => $arg) {
            if (substr($arg, 0, 1) !== '-') {
                continue;
            }

            // Checks the presence of a prefixed argument, if the argument is not recovered from the collection -> skips
            if (!$this->setArgFromPrefix($arg, strncmp($arg, '--', 2) === 0 ? '--' : '-')) {
                continue;
            }

            unset($arguments[$index]);
        }

        // Loops to recover required arguments and set their values
        foreach ($this as $name => $oArgument) {
            if (!$oArgument->isRequired()) {
                continue;
            }

            if (($argValue = current($arguments)) === false) {
                throw new ParseException("Argument $name is required");
            }

            $oArgument->setValueParsed($argValue);

            unset($arguments[key($arguments)]);
        }

        reset($arguments);

        // Remains non required arguments
        $oNotHandled = $this->getNotHandled();

        if (!$oNotHandled->count() || !$arguments) {
            return;
        }

        foreach ($oNotHandled as $oArgument) {
            $arg = current($arguments);

            // Skips prefixes arguments, already handled in the first loop
            if ($oArgument->getLongPrefix() || $oArgument->getPrefix()) {
                continue;
            }

            $oArgument->setValueParsed($arg);

            if (!next($arguments)) {
                return;
            }
        }
    }

    /**
     * Returns required arguments
     */
    public function getRequired(): self
    {
        if (!$this->count()) {
            return $this;
        }

        $oSelf = new self();

        foreach ($this as $oArgument) {
            if ($oArgument->isRequired()) {
                $oSelf->set($oArgument);
            }
        }

        return $oSelf;
    }

    /**
     * Prints a clean display about the informations of arguments
     */
    public function printArguments(): void
    {
        $oSelf = clone $this;
        $oArgs = $oSelf->getRequired();

        // Prints required arguments
        if ($oArgs->count()) {
            print "Required arguments:";

            foreach ($oArgs as $name => $oArg) {
                print "\n\t" . $oArg->getInfos();

                $oSelf->remove($name);
            }
        }

        if (!$oSelf->count()) {
            return;
        }

        print "\n\Optional arguments:";

        foreach ($oSelf as $name => $oArg) {
            print "\n\t" . $oArg->getInfos();
        }
    }

    /**
     * Returns an argument from a prefixed one (-)
     */
    protected function getByShortPrefix(string $name): ?Argument
    {
        foreach ($this as $oArgument) {
            if ($oArgument->getPrefix() === $name) {
                return $oArgument;
            }
        }

        return null;
    }

    /**
     * Returns an argument from a long prefixed one (-)
     */
    protected function getByLongPrefix(string $name): ?Argument
    {
        foreach ($this as $oArgument) {
            if ($oArgument->getLongPrefix() === $name) {
                return $oArgument;
            }
        }

        return null;
    }

    /**
     * Returns not handled arguments
     */
    protected function getNotHandled(): self
    {
        $oSelf = new self();

        foreach ($this as $oArgument) {
            if (!$oArgument->hasBeenHandled()) {
                $oSelf->set($oArgument);
            }
        }

        return $oSelf;
    }

    /**
     * Returns an Argument or NULL from its name
     */
    protected function retrieve(string $argumentName): ?Argument
    {
        return $this->data[$argumentName] ?? null;
    }

    /**
     * Verifies the order of required arguments
     */
    protected function assertOrderRequired(): void
    {
        $argHasNotRequired = false;

        foreach ($this as $name => $oArgument) {
            if ($oArgument->getPrefix() || $oArgument->getLongPrefix()) {
                continue;
            }

            if (!$oArgument->isRequired()) {
                $argHasNotRequired = true;
            }

            if ($argHasNotRequired && $oArgument->isRequired()) {
                throw new Exception("Required argument $name follows a not required argument");
            }
        }
    }

    /**
     * Sets the value of an prefixed argument (starting either by - or --)
     */
    protected function setArgFromPrefix(string $arg, string $prefix): bool
    {
        $substrCallable = function_exists('mb_substr') ? \mb_substr(...) : substr(...);
        $strposCallable = function_exists('mb_strpos') ? \mb_strpos(...) : strpos(...);

        $countPrefix = strlen($prefix);

        // Removes quotes and first --
        $arg = $substrCallable(str_replace(['\'', '"'], '', $arg), $countPrefix);

        // Recovers the name of the argument (if an = is found, we slice before the sign, if not the complete value)
        $argName = $arg;
        if (($equalPosition = $strposCallable($arg, '=')) !== false) {
            $argName = $substrCallable($arg, 0, $equalPosition);
        }

        // Retrives the argument from its short or long prefix name
        if (!$oArgument = $countPrefix === 1 ? $this->getByShortPrefix($argName) : $this->getByLongPrefix($argName)) {
            return false;
        }

        // If the argument is a noValue one -> parses it
        if ($oArgument->hasNoValue()) {
            $oArgument->setValueParsed(true);

            return true;
        }

        // Argument must have an equal to recovers the value after it (noValue => false)
        if ($equalPosition === false) {
            throw new ParseException("Prefixed argument starting with $prefix ($arg) has no = sign");
        }

        $oArgument->setValueParsed($substrCallable($arg, $equalPosition + 1));

        return true;
    }
}
