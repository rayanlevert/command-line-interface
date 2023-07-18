<?php

namespace DisDev\Cli\Style;

/**
 * Enum contenant les codes ANSI des couleurs de background
 */
enum Background: string implements AnsiInterface
{
    case BLACK      = '40';
    case RED        = '41';
    case GREEN      = '42';
    case YELLOW     = '43';
    case BLUE       = '44';
    case MAGENTA    = '45';
    case CYAN       = '46';
    case LIGHT_GRAY = '47';

    /**
     * On ajoute bg à chaque début de tag pour les différencier du foreground
     */
    public static function tryFromTag(string $tagName): ?self
    {
        return match ($tagName) {
            'bgblack'     => self::BLACK,
            'bgred'       => self::RED,
            'bggreen'     => self::GREEN,
            'bgyellow'    => self::YELLOW,
            'bgblue'      => self::BLUE,
            'bgmagenta'   => self::MAGENTA,
            'bgcyan'      => self::CYAN,
            'bglightgray' => self::LIGHT_GRAY,
            default     => null
        };
    }
}
