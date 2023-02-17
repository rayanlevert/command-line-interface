<?php

namespace DisDev\Cli\Tests\ProgressBar;

use DisDev\Cli\ProgressBar;
use DisDev\Cli\Style;
use DisDev\Cli\Style\Foreground;

class ProgressBarTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test __construct()
     */
    public function testConstruct(): void
    {
        $oProgressBar = new ProgressBar(10);

        $this->assertSame(0, $oProgressBar->getCurrent());
    }

    /**
     * @test __construct() d'une valeur max négative
     */
    public function testMaxNegative(): void
    {
        $this->expectExceptionMessage('La valeur max de la barre de progression doit être positive');

        new ProgressBar(-1);
    }

    /**
     * @test __construct() d'une valeur max à 0
     */
    public function testMax0(): void
    {
        $this->expectExceptionMessage('La valeur max de la barre de progression doit être positive');

        new ProgressBar(0);
    }

    /**
     * @test __construct() d'un nombre de symbols nagatif
     */
    public function testNumberOfSymbolsNegative(): void
    {
        $this->expectExceptionMessage('Le nombre de symbols doit être positif');

        new ProgressBar(1, -1);
    }

    /**
     * @test __construct() d'un nombre de symbols à 0
     */
    public function testNumberOfSymbols0(): void
    {
        $this->expectExceptionMessage('Le nombre de symbols doit être positif');

        new ProgressBar(1, 0);
    }

    /**
     * @test ->start() avec un titre
     */
    public function testStartWithTitle(): void
    {
        (new ProgressBar(5))->setTitle('Titre en bleu')->start();

        $this->assertStringStartsWith(
            "\n\n\e[1A\e[1000D\33[2K" . Style::stylize("\tTitre en bleu", fg: Foreground::BLUE)
                . "\e[1B\e[1000D\33[2K\t0 / 5 [     ] 0%",
            ob_get_contents()
        );

        ob_clean();
    }

    /**
     * @test ->start() avec un titre d'une différente couleur
     */
    public function testStartWithTitleDifferentColor(): void
    {
        (new ProgressBar(5))->setTitle('Titre en vert', Style\Foreground::GREEN)->start();

        $this->assertStringStartsWith(
            "\n\n\e[1A\e[1000D\33[2K" . Style::stylize("\tTitre en vert", fg: Foreground::GREEN)
                . "\e[1B\e[1000D\33[2K\t0 / 5 [     ] 0%",
            ob_get_contents()
        );

        ob_clean();
    }

    /**
     * @test ->advance() sans avoir appelé ->start() -> ne fait rien
     */
    public function testAdvanceWithoutStarting(): void
    {
        $this->expectOutputString('');

        $oProgressBar = new ProgressBar(10);
        $oProgressBar->advance(1);
        $this->assertSame(0, $oProgressBar->getCurrent());
    }

    /**
     * @test L'affichage de la barre de progression de 1 à 10 par 1
     */
    public function testAdvance1Until10(): void
    {
        $oProgressBar = new ProgressBar(10);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [          ] 0%", ob_get_contents());

        foreach (range(1, 10) as $step) {
            ob_clean();

            $oProgressBar->advance(1);

            $this->assertStringStartsWith(
                "\e[1000D\33[2K\t$step / 10 [" . str_repeat('#', $step) . str_repeat(' ', 10 - $step) . '] '
                    . $step * 10 . '%',
                ob_get_contents()
            );

            if ($step === 10) {
                $this->assertTrue($oProgressBar->isFinished());
            } else {
                $this->assertFalse($oProgressBar->isFinished());
            }

            $this->assertSame($step, $oProgressBar->getCurrent());
        }

        $this->assertSame(31, $this->getCount());

        ob_clean();
    }

    /**
     * @test L'affichage de la barre de progression de 1 à 10 par 2
     */
    public function testAdvance2Until10(): void
    {
        $oProgressBar = new ProgressBar(10);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [          ] 0%", ob_get_contents());

        foreach (range(2, 10, 2) as $step) {
            ob_clean();

            $oProgressBar->advance(2);

            $this->assertStringStartsWith(
                "\e[1000D\33[2K\t$step / 10 [" . str_repeat('#', $step) . str_repeat(' ', 10 - $step) . '] '
                    . $step * 10 . '%',
                ob_get_contents()
            );

            if ($step === 10) {
                $this->assertTrue($oProgressBar->isFinished());
            } else {
                $this->assertFalse($oProgressBar->isFinished());
            }

            $this->assertSame($step, $oProgressBar->getCurrent());
        }

        $this->assertSame(16, $this->getCount());

        ob_clean();
    }

    /**
     * @test ->advance() quand l'iteration a déjà atteint le max
     */
    public function testAdvanceMoreThanMax(): void
    {
        $oProgressBar = new ProgressBar(2);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 2 [  ] 0%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(2);
        $this->assertStringStartsWith("\e[1000D\33[2K\t2 / 2 [##] 100%", ob_get_contents());
        ob_clean();

        $this->assertTrue($oProgressBar->isFinished());

        // Rien n'est affiché
        $this->expectOutputString('');

        $oProgressBar->advance(1);
    }

    /**
     * @test __construct() avec un nombre de symbols en dessous de la valeur max
     */
    public function testNumberOfSymbolsBelowMax(): void
    {
        // Un symbole toutes les 5 iterations (pair, 10 / 2)
        $oProgressBar = new ProgressBar(10, 2);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [  ] 0%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t3 / 10 [  ] 30%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(2);
        $this->assertStringStartsWith("\e[1000D\33[2K\t5 / 10 [# ] 50%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(4);
        $this->assertStringStartsWith("\e[1000D\33[2K\t9 / 10 [# ] 90%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(5);
        $this->assertStringStartsWith("\e[1000D\33[2K\t10 / 10 [##] 100%", ob_get_contents());
        ob_clean();

        // Un symbole toutes les 3 iterations (impair, floor(10 / 3))
        $oProgressBar = new ProgressBar(10, 3);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [   ] 0%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t3 / 10 [#  ] 30%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(2);
        $this->assertStringStartsWith("\e[1000D\33[2K\t5 / 10 [#  ] 50%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(1);
        $this->assertStringStartsWith("\e[1000D\33[2K\t6 / 10 [## ] 60%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t9 / 10 [###] 90%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t10 / 10 [###] 100%", ob_get_contents());
        ob_clean();
    }

    /**
     * @test __construct() avec un nombre de symbole au dessus de la valeur max
     */
    public function testNumberOfSymbolsAboveMax(): void
    {
        $oProgressBar = new ProgressBar(20, 25);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 20 [                    ] 0%", ob_get_contents());
        ob_clean();

        // 20 diez
        foreach (range(1, 20) as $step) {
            ob_clean();

            $oProgressBar->advance(1);

            $this->assertStringStartsWith(
                "\e[1000D\33[2K\t$step / 20 [" . str_repeat('#', $step) . str_repeat(' ', 20 - $step) . '] '
                    . 5 * $step . '%',
                ob_get_contents()
            );
        }

        ob_clean();
    }

    /**
     * @test L'affichage de la barre de progrès de 1000 élements
     */
    public function testMax1000Step1(): void
    {
        $oProgressBar = new ProgressBar(1000, 100);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 1000 [" . str_repeat(' ', 100) . ']', ob_get_contents());
        ob_clean();

        $expectedIterationDiez = 0;

        foreach (range(1, 1000) as $step) {
            ob_clean();

            $oProgressBar->advance(1);

            // 1 diez en plus toutes les 10 iterations
            if (fmod($step, 10) === 0.0) {
                $expectedIterationDiez++;
            }

            $this->assertStringStartsWith(
                "\e[1000D\33[2K\t$step / 1000 ["
                    . str_repeat('#', $expectedIterationDiez) . str_repeat(' ', 100 - $expectedIterationDiez) . '] '
                    . $step / 10 . '%',
                ob_get_contents()
            );
        }

        $oProgressBar = new ProgressBar(1000);
        $oProgressBar->start();

        $expectedIterationDiez = 0;

        foreach (range(1, 1000) as $step) {
            ob_clean();

            $oProgressBar->advance(1);

            // 1 diez en plus toutes les 20 iterations
            if (fmod($step, 20) === 0.0) {
                $expectedIterationDiez++;
            }

            $this->assertStringStartsWith(
                "\e[1000D\33[2K\t$step / 1000 ["
                    . str_repeat('#', $expectedIterationDiez) . str_repeat(' ', 50 - $expectedIterationDiez) . '] '
                    . $step / 10 . '%',
                ob_get_contents()
            );
        }

        ob_clean();
    }

    /**
     * @test ->finish() sans avoir ->start() -> n'affiche rien
     */
    public function testFinishWithoutStarting(): void
    {
        $oProgressBar = new ProgressBar(10);
        $oProgressBar->finish();

        $this->expectOutputString('');
        $oProgressBar->advance();
    }

    /**
     * @test ->finish()
     */
    public function testFinishWithAdvance(): void
    {
        $oProgressBar = new ProgressBar(10);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [          ] 0%", ob_get_contents());
        ob_clean();

        $oProgressBar->advance(7);
        $this->assertStringStartsWith(
            "\e[1000D\33[2K\t7 / 10 [" . str_repeat('#', 7) . '   ] 70%',
            ob_get_contents()
        );
        ob_clean();

        $oProgressBar->finish();

        $this->assertStringStartsWith("\e[1000D\33[2K\t10 / 10 [" . str_repeat('#', 10) . '] 100%', ob_get_contents());

        ob_clean();
    }

    /**
     * Include functional.php, ne teste rien cela sert juste à afficher la progression de la barre de progrès
     */
    public function testFunctional(): void
    {
        include 'functional.php';

        $this->assertTrue(true);
        ob_start();

        print "\n";
    }
}
