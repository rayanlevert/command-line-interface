<?php

namespace RayanLevert\Cli;

use RayanLevert\Cli\Style\Foreground;
use UnexpectedValueException;

use function microtime;
use function sprintf;
use function str_repeat;
use function intval;
use function floor;
use function ceil;
use function round;
use function memory_get_usage;

/** Displays progression output through a progress bar */
class ProgressBar
{
    public const string UP     = "\e[%dA";
    public const string DOWN   = "\e[%dB";
    public const string RIGHT  = "\e[%dC";
    public const string LEFT   = "\e[%dD";

    public int $max {
        set {
            if ($value <= 0) {
                throw new UnexpectedValueException('The max value must be positive');
            }

            $this->max = $value;
        }
        get => $this->max;
    }

    public int $numberOfSymbols {
        set {
            if ($value <= 0) {
                throw new UnexpectedValueException('The number of symbols must be positive');
            }

            $this->numberOfSymbols = $value;
        }
        get {
            return $this->max <= $this->numberOfSymbols ? $this->max : $this->numberOfSymbols;
        }
    }

    /** Current iteration */
    public protected(set) int $iteration = 0;

    /** If the progress bar has been started */
    protected bool $hasBeenStartedOnce = false;

    /** If the progress bar has been finished (exceeded the max value) */
    public protected(set) bool $isFinished = true;

    /** Title of the current progress bar */
    protected string $title = '';

    protected float $startTime = 0.0;

    protected float $totalTime = 0.0;

    protected float $lastIterationTime = 0.0;

    /**
     * Returns a formatted allocated memory to PHP
     *
     * @param int $memory Allocated memory in bytes
     */
    public static function getFormattedMemory(int $memory): string
    {
        return match (true) {
            $memory <= 1024    => $memory . ' B',
            $memory <= 1048576 => round($memory / 1024, 2) . ' KB',
            default            => round($memory / 1048576, 2) . ' MB'
        };
    }

    /**
     * Returns a formatted time (in ms, sec, min..)
     *
     * @param float $time Time in millisecond
     */
    public static function getFormattedTime(float $time): string
    {
        return strval(match (true) {
            $time < 1000   => round($time, 2) . 'ms',
            $time < 60000  => round($time / 1000, 2) . 'sec',
            $time < 3.6e+6 => round((int) ($time / 1000 / 60) % 60, 2) . 'min' . round((int) ($time / 1000) % 60) . 's',
            default        => floor($time / 3.6e+6) . 'h'
                . round((int) ($time / 1000 / 60) % 60, 2) . 'min'
                . round((int) ($time / 1000) % 60) . 's'
        });
    }

    /**
     * Initializes the progress bar
     *
     * @param int $max Maximum value of iterations
     * @param int $numberOfSymbols Number of symbols added after each iteration
     *
     * @throws UnexpectedValueException If `$max` or `$numberOfSymbols` are negative values
     */
    public function __construct(int $max, int $numberOfSymbols = 50)
    {
        $this->max             = $max;
        $this->numberOfSymbols = $numberOfSymbols;
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
            print sprintf(self::UP . self::LEFT . "\33[2K" . $this->title . self::DOWN, 1, 1000, 1);
        }

        // Resets the line placing the cursor leftmost + the current iteration / max
        print sprintf(self::LEFT, 1000) . "\33[2K\t{$this->iteration} / {$this->max} [";

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
            $actualDiezes = intval(floor($this->iteration / intval(ceil($this->max / $this->numberOfSymbols))));

            print str_repeat('#', $actualDiezes) . str_repeat(' ', ($this->numberOfSymbols - $actualDiezes));
        }

        if (!$this->lastIterationTime) {
            $this->totalTime = 0.0;
        } else {
            $this->totalTime += (microtime(true) - $this->lastIterationTime) * 1000;
        }

        // Displays the pourcentage of iterations, the time and allocated memory
        print '] ' . round($this->iteration / $this->max * 100, 2)  . '%';

        $this->printTime();

        $this->lastIterationTime = microtime(true);
    }

    /** Finishes the progress bar (advances to the max value) */
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

    /** Displays the total time of the progression and PHP memory on bottom of the progress bar */
    private function printTime(): void
    {
        // Time color by its total time
        $time = Style::stylize(self::getFormattedTime($this->totalTime), fg: match (true) {
            $this->totalTime <= 500  => Foreground::GREEN,
            $this->totalTime <= 2000 => Foreground::YELLOW,
            default                  => Foreground::RED
        });

        // Allocated memory by its usage
        $memoryUsage = memory_get_usage(true);
        $memory      = Style::stylize(self::getFormattedMemory($memoryUsage), fg: match (true) {
            $memoryUsage <= 268435456  => Foreground::LIGHT_GREEN, // 256MB
            $memoryUsage <= 536870912  => Foreground::YELLOW, // 512MB
            default                    => Foreground::RED
        });

        print sprintf(
            self::DOWN . self::LEFT . "\t\33[2K%s" . self::UP . self::LEFT,
            1,
            1000,
            $time . str_repeat(' ', $this->numberOfSymbols + 2) . $memory,
            1,
            1000
        );
    }
}
