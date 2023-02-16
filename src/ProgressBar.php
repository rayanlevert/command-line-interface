<?php

namespace DisDev\Cli;

/**
 * Barre de progrès permettant l'affichage d'une progression pour le CLI
 */
class ProgressBar
{
    protected string $up = "\e[%dA";

    protected string $down = "\e[%dB";

    protected string $right = "\e[%dC";

    protected string $left = "\e[%dD";

    /**
     * Nombre d'itération requis pour ajouter un charactère à la barre de progrès
     */
    protected int $numberOfEachIterations;

    /**
     * Courante itération
     */
    protected int $iteration = 0;

    /**
     * Si l'itération a dépassé ou égalé la valeur max de la barre de progrès
     */
    protected bool $isFinished = true;

    /**
     * Initialise une barre de progrès en settant le max
     *
     * @param int $max Valeur max d'itérations
     * @param int $numberOfSymbols Nombre équivalent de symbols (#) qui sont ajoutés à chaque itération
     *
     * @throws \UnexpectedValueException Si `$max` ou `$numberOfSymbols` sont négatifs
     */
    public function __construct(protected int $max, protected int $numberOfSymbols = 50)
    {
        if ($max <= 0) {
            throw new \UnexpectedValueException('La valeur max de la barre de progrès doit être positive');
        } elseif ($numberOfSymbols <= 0) {
            throw new \UnexpectedValueException('Le nombre de symbols doit être positif');
        }

        $this->numberOfEachIterations = floor($this->max / $this->numberOfSymbols);
    }

    /**
     * Commence la barre de progrès et passe une ligne
     *
     * @param string $title Si un texte en bleu au dessus de la barre doit être affiché
     * @param Style\Foreground $fg Si une couleur de texte est souhaitée (bleue par défaut)
     */
    public function start(string $title = '', Style\Foreground $fg = Style\Foreground::BLUE): void
    {
        $this->isFinished = false;

        print "\n";

        if ($title) {
            Style::outline("\t$title", fg: $fg);
        }
    }

    /**
     * Avance la barre de progrès de `$toAdvance` iteration et met à jour la progression
     *
     * @param int $toAdvance Nombre d'itération à avancer
     */
    public function advance(int $toAdvance = 1): void
    {
        if ($this->isFinished || $toAdvance < 0) {
            return;
        }

        // On reset la ligne en revenant le cursor tout à gauche
        print sprintf($this->left, 1000);

        // Si la position courante est égale ou supérieure à max, à la prochaine iteration on ne fera rien
        if (($this->iteration += $toAdvance) >= $this->max) {
            $this->isFinished = true;
        }

        print "\t{$this->iteration} / {$this->max} [";

        // Si on est arrivé à la fin, on affiche toute la ligne de #
        if ($this->isFinished) {
            print str_repeat('#', $this->max <= $this->numberOfSymbols ? $this->max : $this->numberOfSymbols) . ']';

            return;
        }

        /**
         * Si la valeur max est inférieure au nombre de symboles, on affiche x ->iteration
         * sinon on prend une moyenne de chaque itération par rapport au max
         */
        if ($this->max <= $this->numberOfSymbols) {
            print str_repeat('#', $this->iteration) . str_repeat(' ', $this->max - $this->iteration) . ']';
        } else {
            $actualDiezes = floor($this->iteration / $this->numberOfEachIterations);

            print str_repeat('#', $actualDiezes) . str_repeat(' ', ($this->numberOfSymbols - $actualDiezes)) . "]";
        }
    }

    /**
     * Si la barre de progrès a atteint sa valeur maximale ou n'a pas commencé
     */
    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * Retourne la position courante de la barre de progrès
     */
    public function getCurrent(): int
    {
        return $this->iteration;
    }
}
