<?php

namespace RayanLevert\Cli;

/**
 * Personalizes the command line interface by changing the color and format of displayed text
 */
class Style
{
    public const START_TAG = "\e[";

    public const END_TAG = "\e[0m";

    /**
     * Prints a string of a background color, text color and/or an attribute
     */
    public static function inline(
        string $string,
        Style\Background $bg = null,
        Style\Foreground $fg = null,
        Style\Attribute $at = null
    ): void {
        print self::stylize($string, $bg, $fg, $at);
    }

    /**
     * Prints a string and breaks a line of a background color, text color and/or an attribute
     */
    public static function outline(
        string $string,
        Style\Background $bg = null,
        Style\Foreground $fg = null,
        Style\Attribute $at = null
    ): void {
        print self::stylize($string, $bg, $fg, $at) . PHP_EOL;
    }

    /**
     * Prints a string nested with a repeated character (`--- message ---`) and breaks a line
     */
    public static function flank(string $message, string $char = '-', int $length = 3): void
    {
        $repeated = str_repeat($char, $length);

        print self::stylize("$repeated $message $repeated") . "\n";
    }

    /**
     * Prints a title-like string (flanked by ｡◕‿◕｡)
     */
    public static function title(string $title): void
    {
        $repeatedTitleChar = self::stylize(str_repeat('=', strlen($title) + 10)) . "\n";

        print $repeatedTitleChar . self::stylize("｡◕‿◕｡ $title ｡◕‿◕｡") . "\n" . $repeatedTitleChar;
    }

    /**
     * Prints an red colored message followed by (◍•﹏•)
     */
    public static function error(string $message): void
    {
        self::outline('  (◍•﹏•) ' . $message, fg: Style\Foreground::LIGHT_RED);
    }

    /**
     * Prints an yellow colored message followed by (◍•﹏•)
     */
    public static function warning(string $message): void
    {
        self::outline('  (◍•﹏•) ' . $message, fg: Style\Foreground::YELLOW);
    }

    /**
     * Prints a red colored message and breaks a line
     */
    public static function red(string $message): void
    {
        print self::stylize($message, fg: Style\Foreground::LIGHT_RED) . "\n";
    }

    /**
     * Prints a yellow colored message and breaks a line
     */
    public static function yellow(string $message): void
    {
        print self::stylize($message, fg: Style\Foreground::YELLOW) . "\n";
    }

    /**
     * Prints a green colored message and breaks a line
     */
    public static function green(string $message): void
    {
        print self::stylize($message, fg: Style\Foreground::GREEN) . "\n";
    }

    /**
     * Displays according to a boolean status, a red or green text colored message
     *
     * @param string $toPrecede (optional) Text preceding the `$ifTrue/$ifFalse` message
     */
    public static function outlineWithBool(bool $status, string $ifTrue, string $ifFalse, string $toPrecede = ''): void
    {
        if ($toPrecede) {
            self::inline($toPrecede);
        }

        if ($status) {
            self::green($ifTrue);

            return;
        }

        self::red($ifFalse);
    }

    /**
     * Prints the details of an exception in red + its trace in white
     */
    public static function exception(\Exception $e, bool $withoutTrace = false): void
    {
        print "\n";

        // Displays the exception infos in a line of red colored text
        self::error(sprintf(
            "%s thrown in file %s (line n°%d)",
            get_class($e),
            $e->getFile(),
            $e->getLine()
        ));

        // Breaks a line and displays the message in a bold text
        print self::stylize('          ' . $e->getMessage(), at: Style\Attribute::BOLD) . "\n";

        if (!$withoutTrace) {
            self::outline("\nTrace : " . $e->getTraceAsString());
        }
    }

    /**
     * Prints a formatted string thanks to its tags of ANSI codes
     *
     * @see RayanLevert\Cli\Style{Attribute, Background, Foreground} and their `tryFromTag` method
     */
    public static function tag(string $tag): void
    {
        // We recover the tags name and their inner value
        preg_match_all('/<([\w]+)[^>]*>(.*?)<\/\1>/', $tag, $aMatches);

        // If no tag is recovered
        if (!$aMatches[0]) {
            print $tag;

            return;
        }

        /**
         * Loop through each recovered tag
         *
         * - 0: full value from opening to closing tag
         * - 1: tag name
         * - 2: inner value
         */
        foreach ($aMatches[0] as $index => $match) {
            $tagName    = $aMatches[1][$index];
            $innerValue = $aMatches[2][$index];

            // Recovers the AnsiInterface instance from the tag name
            $oAnsi = match (substr($tagName, 0, 2)) {
                'fg'    => Style\Foreground::tryFromTag($tagName),
                'bg'    => Style\Background::tryFromTag($tagName),
                default => Style\Attribute::tryFromTag($tagName)
            };

            if (!$oAnsi) {
                trigger_error(get_called_class() . " : tag name '$tagName' is incorrect");

                continue;
            }

            $tag = str_replace(
                $match,
                self::START_TAG . $oAnsi->value . 'm' . $innerValue . self::END_TAG,
                $tag
            );
        }

        print $tag;
    }

    /**
     * Returns a background color, text color and/or an attribute formatted string
     */
    public static function stylize(
        string $string,
        Style\Background $bg = null,
        Style\Foreground $fg = null,
        Style\Attribute $at = null
    ): string {
        if (!$bg && !$fg && !$at) {
            return $string;
        }

        $styled = '';

        if ($fg) {
            $styled .= self::START_TAG . $fg->value . 'm';
        }

        if ($bg) {
            $styled .= self::START_TAG . $bg->value . 'm';
        }

        if ($at) {
            $styled .= self::START_TAG . $at->value . 'm';
        }

        return $styled . $string . self::END_TAG;
    }
}
