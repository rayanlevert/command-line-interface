<?php

namespace DisDev\Cli;

use DisDev\Cli\Style\Foreground;

/**
 * barre de progression permettant l'affichage d'une progression pour le CLI
 */
class ProgressBar
{
    protected string $up = "\e[%dA";

    protected string $down = "\e[%dB";

    protected string $right = "\e[%dC";

    protected string $left = "\e[%dD";

    /**
     * Nombre d'itération requis pour ajouter un charactère à la barre de progression
     */
    protected int $numberOfEachIterations;

    /**
     * Courante itération
     */
    protected int $iteration = 0;

    /**
     * Si la barre de progression a déjà été commencé au moins une fois
     */
    protected bool $hasBeenStartedOnce = false;

    /**
     * Si l'itération a dépassé ou égalé la valeur max de la barre de progression
     */
    protected bool $isFinished = true;

    /**
     * Titre de la barre de progression
     */
    protected string $title = '';

    protected float $startTime = 0.0;

    protected float $totalTime = 0.0;

    protected float $lastIterationTime = 0.0;

    /**
     * Initialise une barre de progression en settant le max
     *
     * @param int $max Valeur max d'itérations
     * @param int $numberOfSymbols Nombre équivalent de symbols (#) qui sont ajoutés à chaque itération
     *
     * @throws \UnexpectedValueException Si `$max` ou `$numberOfSymbols` sont négatifs
     */
    public function __construct(protected int $max, protected int $numberOfSymbols = 50)
    {
        if ($max <= 0) {
            throw new \UnexpectedValueException('La valeur max de la barre de progression doit être positive');
        } elseif ($numberOfSymbols <= 0) {
            throw new \UnexpectedValueException('Le nombre de symbols doit être positif');
        }

        if ($max <= $numberOfSymbols) {
            $this->numberOfSymbols = $max;
        }

        $this->numberOfEachIterations = floor($this->max / $this->numberOfSymbols);
    }

    /**
     * Commence la barre de progression (ou recommence si déjà started, sinon passe deux lignes)
     */
    public function start(): void
    {
        if (!$this->hasBeenStartedOnce) {
            $this->hasBeenStartedOnce = true;

            print "\n\n";
        }

        $this->isFinished = false;
        $this->iteration  = 0;
        $this->startTime  = microtime(true);
        $this->totalTime  = 0.0;

        $this->advance(0);
    }

    /**
     * Avance la barre de progression de `$toAdvance` iteration et met à jour la progression
     *
     * @param int $toAdvance Nombre d'itération à avancer
     */
    public function advance(int $toAdvance = 1): void
    {
        if ($this->isFinished || $toAdvance < 0) {
            return;
        }

        // Si la position courante est égale ou supérieure à max, à la prochaine iteration on ne fera rien
        $toAdvance += $this->iteration;

        if ($toAdvance >= $this->max) {
            $this->iteration  = $this->max;
            $this->isFinished = true;
        } else {
            $this->iteration = $toAdvance;
        }

        if ($this->title) {
            print sprintf($this->up . $this->left . "\33[2K" . $this->title . $this->down, 1, 1000, 1);
        }

        // On reset la ligne en revenant le cursor tout à gauche + la courante iteration / max
        print sprintf($this->left, 1000) . "\33[2K\t{$this->iteration} / {$this->max} [";

        /**
         * Si on est arrivé à la fin, on affiche toute la ligne de #
         * Si la valeur max est inférieure au nombre de symboles, on affiche x ->iteration
         * sinon on prend une moyenne de chaque itération par rapport au max
         */
        if ($this->iteration >= $this->max) {
            print str_repeat('#', $this->numberOfSymbols);
        } elseif ($this->max === $this->numberOfSymbols) {
            print str_repeat('#', $this->iteration) . str_repeat(' ', $this->max - $this->iteration);
        } else {
            $actualDiezes = floor($this->iteration / $this->numberOfEachIterations);

            print str_repeat('#', $actualDiezes) . str_repeat(' ', ($this->numberOfSymbols - $actualDiezes));
        }

        if (!$this->lastIterationTime) {
            $this->totalTime = 0.0;
        } else {
            $this->totalTime += (microtime(true) - $this->lastIterationTime) * 1000;
        }

        // Affichage du poucentage de l'itération
        print '] ' . $this->iteration / $this->max * 100  . '%';

        $this->printTime();

        $this->lastIterationTime = microtime(true);
    }

    /**
     * Termine la barre de progression
     */
    public function finish(): void
    {
        if ($this->isFinished) {
            return;
        }

        $this->advance($this->max);
    }

    /**
     * Set le titre à afficher au dessus de la barre de progression
     *
     * @param Style\Foreground $fg Si une couleur de texte est souhaitée (bleue par défaut)
     */
    public function setTitle(string $title, Style\Foreground $fg = Style\Foreground::BLUE): self
    {
        $this->title = Style::stylize("\t$title", fg: $fg);

        return $this;
    }

    /**
     * Si la barre de progression a atteint sa valeur maximale ou n'a pas commencé
     */
    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * Retourne la position courante de la barre de progression
     */
    public function getCurrent(): int
    {
        return $this->iteration;
    }

    /**
     * Affiche le temps total de la progression en dessous de la barre
     */
    private function printTime(): void
    {
        // Couleur du temps en fonction de la progression de la barre
        $time = Style::stylize((string) round($this->totalTime, 2) . 'ms', fg: match (true) {
            $this->totalTime <= 500  => Foreground::GREEN,
            $this->totalTime <= 2000 => Foreground::YELLOW,
            default                  => Foreground::RED
        });

        print sprintf(
            $this->down . $this->left . "\t\33[2K%s" . $this->up . $this->left,
            1,
            1000,
            $time,
            1,
            1000
        );
    }
}
