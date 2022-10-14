<?php

namespace DisDev\Cli\Style;

/**
 * Enum contenant les codes ANSI des couleurs de background
 */
enum Background: string
{
    case BLACK      = '40';
    case RED        = '41';
    case GREEN      = '42';
    case YELLOW     = '43';
    case BLUE       = '44';
    case MAGENTA    = '45';
    case CYAN       = '46';
    case LIGHT_GRAY = '47';
}
