<?php

namespace RayanLevert\Cli\Style;

/**
 * Interface for ANSI data
 */
interface AnsiInterface
{
    /**
     * Returns an ANSI instance from a tag name
     */
    public static function tryFromTag(string $tagName): ?self;
}
