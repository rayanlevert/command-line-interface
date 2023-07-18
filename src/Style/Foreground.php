<?php

namespace DisDev\Cli\Style;

/**
 * Enum contenant les codes ANSI des couleurs de texte
 */
enum Foreground: string implements AnsiInterface
{
    case BLACK        = '0;30';
    case DARK_GRAY    = '1;30';
    case RED          = '0;31';
    case LIGHT_RED    = '1;31';
    case GREEN        = '0;32';
    case LIGHT_GREEN  = '1;32';
    case BROWN        = '0;33';
    case YELLOW       = '1;33';
    case BLUE         = '0;34';
    case LIGHT_BLUE   = '1;34';
    case PURPLE       = '0;35';
    case LIGHT_PURPLE = '1;35';
    case CYAN         = '0;36';
    case LIGHT_CYAN   = '1;36';
    case LIGHT_GRAY   = '0;37';
    case WHITE        = '1;37';

    /**
     * On ajoute fg à chaque début de tag pour les différencier du background
     */
    public static function tryFromTag(string $tagName): ?self
    {
        return match ($tagName) {
            'fgblack'       => self::BLACK,
            'fgdarkgray'    => self::DARK_GRAY,
            'fgred'         => self::RED,
            'fglightred'    => self::LIGHT_RED,
            'fggreen'       => self::GREEN,
            'fglightgreen'  => self::LIGHT_GREEN,
            'fgbrown'       => self::BROWN,
            'fgyellow'      => self::YELLOW,
            'fgblue'        => self::BLUE,
            'fglightblue'   => self::LIGHT_BLUE,
            'fgpurple'      => self::PURPLE,
            'fglightpurple' => self::LIGHT_PURPLE,
            'fgcyan'        => self::CYAN,
            'fglightcyan'   => self::LIGHT_CYAN,
            'fglightgray'   => self::LIGHT_GRAY,
            'fgwhite'       => self::WHITE,
            default         => null
        };
    }
}
