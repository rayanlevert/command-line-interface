<?php

namespace DisDev\Cli\Style;

/**
 * Enum contenant les codes ANSI des attributs disponibles
 */
enum Attribute: string
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

    public function getTag(): string
    {
        return match ($this) {
            self::NORMAL    => 'normal',
            self::BOLD      => 'b',
            self::ITALIC    => 'i',
            self::UNDERLINE => 'u',
            self::BLINK     => 'blink',
            self::OUTLINE   => 'outline',
            self::REVERSE   => 'reverse',
            self::NONDISP   => 'nondisp',
            self::STRIKE    => 'strike'
        };
    }
}
