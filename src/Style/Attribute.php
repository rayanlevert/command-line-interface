<?php

namespace DisDev\Cli\Style;

/**
 * Enum contenant les codes ANSI des attributs disponibles
 */
enum Attribute: string implements AnsiInterface
{
    case NORMAL    = '0';
    case BOLD      = '1';
    case ITALIC    = '3';
    case UNDERLINE = '4';
    case BLINK     = '5';
    case OUTLINE   = '6';
    case REVERSE   = '7';
    case NONDISP   = '8';
    case STRIKE    = '9';

    public static function tryFromTag(string $tagName): ?self
    {
        return match ($tagName) {
            'b', 'bold'      => self::BOLD,
            'i', 'italic'    => self::ITALIC,
            'u', 'underline' => self::UNDERLINE,
            'blink'          => self::BLINK,
            'outline'        => self::OUTLINE,
            'reverse'        => self::REVERSE,
            'nondisp'        => self::NONDISP,
            'strike'         => self::STRIKE,
            default          => null
        };
    }
}
