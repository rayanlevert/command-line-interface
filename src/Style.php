<?php

namespace RayanLevert\Cli;

/**
 * Class qui personnalise et rend plus clair l'output du CLI qui print un texte et le stylise selon la méthode
 */
class Style
{
    public const START_TAG = "\e[";

    public const END_TAG = "\e[0m";

    /**
     * Print le string stylisé d'un background, couleur de texte et/ou d'un attribut
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
     * Print le string + \n stylisé d'un background, couleur de texte et/ou d'un attribut
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
     * Print le titre formaté (par les soins de Yann)
     */
    public static function title(string $title): void
    {
        $repeatedTitleChar = self::stylize(str_repeat('=', strlen($title) + 10)) . "\n";

        print $repeatedTitleChar . self::stylize("｡◕‿◕｡ $title ｡◕‿◕｡") . "\n" . $repeatedTitleChar;
    }

    /**
     * Print le message imbriqué d'un caractère répété (ex: --- message ---) et passe un ligne
     */
    public static function flank(string $message, string $char = '-', int $length = 3): void
    {
        $repeated = str_repeat($char, $length);

        print self::stylize("$repeated $message $repeated") . "\n";
    }

    /**
     * Print 'Terminé' stylisé
     */
    public static function termine(): void
    {
        print "\n｡◕‿◕｡ Terminé ｡◕‿◕｡\n";
    }

    /**
     * Print le message flanké de ｡◕‿◕｡
     */
    public static function flankStyle(string $message): void
    {
        self::flank($message, '｡◕‿◕｡', 1);
    }

    /**
     * Print un message en rouge suivi de (◍•﹏•)
     */
    public static function error(string $message): void
    {
        self::outline('  (◍•﹏•) ' . $message, fg: Style\Foreground::LIGHT_RED);
    }

    /**
     * Print un message en jaune suivi de (◍•﹏•)
     */
    public static function warning(string $message): void
    {
        self::outline('  (◍•﹏•) ' . $message, fg: Style\Foreground::YELLOW);
    }

    /**
     * Print le texte en rouge et passe une ligne
     */
    public static function red(string $message): void
    {
        print self::stylize($message, fg: Style\Foreground::LIGHT_RED) . "\n";
    }

    /**
     * Print le texte en jaune et passe une ligne
     */
    public static function yellow(string $message): void
    {
        print self::stylize($message, fg: Style\Foreground::YELLOW) . "\n";
    }

    /**
     * Print le texte en vert et passe une ligne
     */
    public static function green(string $message): void
    {
        print self::stylize($message, fg: Style\Foreground::GREEN) . "\n";
    }

    /**
     * Affiche un message selon un boolean en affichant soit le texte en vert ou rouge
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
     * Print les détails de l'exception en rouge + sa trace en texte blanc
     */
    public static function exception(\Exception $e, bool $withoutTrace = false): void
    {
        print "\n";

        // On affiche les infos de l'exception sur une ligne en rouge
        self::error(sprintf(
            "%s thrown in file %s (line n°%d)",
            get_class($e),
            $e->getFile(),
            $e->getLine()
        ));

        // On passe une ligne et affiche le message de l'exception en gras
        print self::stylize('          ' . $e->getMessage(), at: Style\Attribute::BOLD) . "\n";

        if (!$withoutTrace) {
            self::outline("\nTrace : " . $e->getTraceAsString());
        }
    }

    /**
     * Print le string stylisé grâce aux tags HTML des codes ANSI
     *
     * @see RayanLevert\Cli\Style{Attribute, Background, Foreground} et la méthode `tryFromTag`
     */
    public static function tag(string $tag): void
    {
        // On récupère tous les tags ouvrants et fermants ainsi que leur inner valeur
        preg_match_all('/<([\w]+)[^>]*>(.*?)<\/\1>/', $tag, $aMatches);

        // Aucun tag n'a été reconnu
        if (!$aMatches[0]) {
            print $tag;

            return;
        }

        /**
         * On boucle à travers chaque tag
         *
         * - 0: tag ouvrant et fermant
         * - 1: nom du tag
         * - 2: inner value
         */
        foreach ($aMatches[0] as $index => $match) {
            $tagName    = $aMatches[1][$index];
            $innerValue = $aMatches[2][$index];

            // On récupère une instance AnsiInterface par le tag
            $oAnsi = match (substr($tagName, 0, 2)) {
                'fg'    => Style\Foreground::tryFromTag($tagName),
                'bg'    => Style\Background::tryFromTag($tagName),
                default => Style\Attribute::tryFromTag($tagName)
            };

            if (!$oAnsi) {
                trigger_error(get_called_class() . " : nom du tag '$tagName' est incorrect");

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
     * Retourne le string stylisé d'un ou plusieurs enum de style
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
