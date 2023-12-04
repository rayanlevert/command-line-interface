<?php

namespace RayanLevert\Cli;

use RayanLevert\Cli\Style\Foreground;

/**
 * Displays progression output through a progress bar
 */
class ProgressBar
{
    protected string $up = "\e[%dA";

    protected string $down = "\e[%dB";

    protected string $right = "\e[%dC";

    protected string $left = "\e[%dD";

    /**
     * Number of required iterations to add a character
     */
    protected int $numberOfEachIterations;

    /**
     * Current iteration
     */
    protected int $iteration = 0;

    /**
     * If the progress bar has been started
     */
    protected bool $hasBeenStartedOnce = false;

    /**
     * If the progress bar has been finished (exceeded the max value)
     */
    protected bool $isFinished = true;

    /**
     * Title of the current progress bar
     */
    protected string $title = '';

    protected float $startTime = 0.0;

    protected float $totalTime = 0.0;

    protected float $lastIterationTime = 0.0;

    /**
     * Initializes the progress bar
     *
     * @param int $max Maximum value of iterations
     * @param int $numberOfSymbols Number of symbols added after each iteration
     *
     * @throws \UnexpectedValueException Si `$max` ou `$numberOfSymbols` sont négatifs
     */
    public function __construct(protected int $max, protected int $numberOfSymbols = 50)
    {
        $this->setMax($max, $numberOfSymbols);
    }

    /**
     * Starts the progress bar (or restarts it, if not breaks two lines)
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
     * Advances the progress bar of `$toAdvance` iterations updating the progression
     *
     * @param int $toAdvance Number of iteration to progress
     */
    public function advance(int $toAdvance = 1): void
    {
        if ($this->isFinished || $toAdvance < 0) {
            return;
        }

        /**
         * If the current position is superior or greater than the max value
         * -> nothing will be processed in the next iteration
         */
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

        // Resets the line placing the cursor leftmost + the current iteration / max
        print sprintf($this->left, 1000) . "\33[2K\t{$this->iteration} / {$this->max} [";

        /**
         * - Progress bar is finished -> displays the complete line of #
         * - Max value est inferior to the number of symbols -> displays # of current iteration
         * - Else establishes the average of iteration from the number of each iteration
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

        // Displays the pourcentage of iterations, the time and allocated memory
        print '] ' . $this->iteration / $this->max * 100  . '%';

        $this->printTime();

        $this->lastIterationTime = microtime(true);
    }

    /**
     * Finishes the progress bar (advances to the max value)
     */
    public function finish(): void
    {
        if ($this->isFinished) {
            return;
        }

        $this->advance($this->max);
    }

    /**
     * Sets the displayed title on top of the progress bar
     *
     * @param Foreground $fg If a foreground color is wished (blue by default)
     */
    public function setTitle(string $title, Foreground $fg = Foreground::BLUE): self
    {
        $this->title = Style::stylize("\t$title", fg: $fg);

        return $this;
    }

    /**
     * Max value of iterations to set
     *
     * @throws \UnexpectedValueException If `$max` or `$numberOfSymbols` are nagative values
     */
    public function setMax(int $max, int $numberOfSymbols = 50): self
    {
        if ($max <= 0) {
            throw new \UnexpectedValueException('The max value must be positive');
        } elseif ($numberOfSymbols <= 0) {
            throw new \UnexpectedValueException('The number of symbols must be positive');
        }

        $this->max              = $max;
        $this->numberOfSymbols  = $numberOfSymbols;

        if ($max <= $this->numberOfSymbols) {
            $this->numberOfSymbols = $max;
        }

        $this->numberOfEachIterations = ceil($this->max / $this->numberOfSymbols);

        return $this;
    }

    /**
     * Returns the maximum value of iterations
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * If the progress bar reached its max value or hasn't started yet
     */
    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * Returns the current iteration
     */
    public function getCurrent(): int
    {
        return $this->iteration;
    }

    /**
     * Displays the total time of the progression et PHP memory on bottom of the progress bar
     */
    private function printTime(): void
    {
        // Time color by its total time
        $time = Style::stylize((string) round($this->totalTime, 2) . 'ms', fg: match (true) {
            $this->totalTime <= 500  => Foreground::GREEN,
            $this->totalTime <= 2000 => Foreground::YELLOW,
            default                  => Foreground::RED
        });

        // Allocated memory by its usage
        $memoryUsage = memory_get_usage(true);
        $memory      = Style::stylize($this->getFormattedMemory(), fg: match (true) {
            $memoryUsage <= 268435456  => Foreground::LIGHT_GREEN, // 256Mo
            $memoryUsage <= 536870912  => Foreground::YELLOW, // 512Mo
            default                    => Foreground::RED
        });

        print sprintf(
            $this->down . $this->left . "\t\33[2K%s" . $this->up . $this->left,
            1,
            1000,
            $time . str_repeat(' ', $this->numberOfSymbols + 2) . $memory,
            1,
            1000
        );
    }

    /**
     * Returns a formatted allocated memory to PHP
     */
    private function getFormattedMemory(): string
    {
        $memory = memory_get_usage(true);

        return match (true) {
            $memory <= 1024         => $memory . ' B',
            $memory <= 1048576      => round($memory / 1024, 2) . ' KB',
            default                 => round($memory / 1048576, 2) . ' MB'
        };
    }
}
