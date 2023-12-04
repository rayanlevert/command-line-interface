<?php

namespace RayanLevert\Cli\Style;

/**
 * Interface pour les données ANSI du CLI
 */
interface AnsiInterface
{
    /**
     * Retourne une instance ANSI (ou null) selon le nom d'un tag
     */
    public static function tryFromTag(string $tagName): ?self;
}
