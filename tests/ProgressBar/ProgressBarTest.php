<?php

namespace RayanLevert\Cli\Tests\ProgressBar;

use RayanLevert\Cli\ProgressBar;
use RayanLevert\Cli\Style;
use RayanLevert\Cli\Style\Foreground;

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
     * @test __construct() with a negative max value
     */
    public function testMaxNegative(): void
    {
        $this->expectExceptionMessage('The max value must be positive');

        new ProgressBar(-1);
    }

    /**
     * @test __construct() with a max value at 0
     */
    public function testMax0(): void
    {
        $this->expectExceptionMessage('The max value must be positive');

        new ProgressBar(0);
    }

    /**
     * @test __construct() with a negative number of symbols
     */
    public function testNumberOfSymbolsNegative(): void
    {
        $this->expectExceptionMessage('The number of symbols must be positive');

        new ProgressBar(1, -1);
    }

    /**
     * @test __construct() with 0 symbol
     */
    public function testNumberOfSymbols0(): void
    {
        $this->expectExceptionMessage('The number of symbols must be positive');

        new ProgressBar(1, 0);
    }

    /**
     * @test ->start() with a title
     */
    public function testStartWithTitle(): void
    {
        (new ProgressBar(5))->setTitle('Titre in blue')->start();

        $this->assertStringStartsWith(
            "\n\n\e[1A\e[1000D\33[2K" . Style::stylize("\tTitre in blue", fg: Foreground::BLUE)
                . "\e[1B\e[1000D\33[2K\t0 / 5 [     ] 0%",
            ob_get_contents()
        );

        $this->assertMemoryInOutput();

        ob_clean();
    }

    /**
     * @test ->start() with a title from a different color
     */
    public function testStartWithTitleDifferentColor(): void
    {
        (new ProgressBar(5))->setTitle('Titre in green', Style\Foreground::GREEN)->start();

        $this->assertStringStartsWith(
            "\n\n\e[1A\e[1000D\33[2K" . Style::stylize("\tTitre in green", fg: Foreground::GREEN)
                . "\e[1B\e[1000D\33[2K\t0 / 5 [     ] 0%",
            ob_get_contents()
        );

        $this->assertMemoryInOutput();

        ob_clean();
    }

    /**
     * @test ->advance() without calling ->start() -> does not do anything
     */
    public function testAdvanceWithoutStarting(): void
    {
        $this->expectOutputString('');

        $oProgressBar = new ProgressBar(10);
        $oProgressBar->advance(1);
        $this->assertSame(0, $oProgressBar->getCurrent());
    }

    /**
     * @test Display from 1 to 10
     */
    public function testAdvance1Until10(): void
    {
        $oProgressBar = new ProgressBar(10);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [          ] 0%", ob_get_contents());
        $this->assertMemoryInOutput();

        foreach (range(1, 10) as $step) {
            ob_clean();

            $oProgressBar->advance();

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

            $this->assertMemoryInOutput();
        }

        $this->assertSame(42, $this->getCount());

        ob_clean();
    }

    /**
     * @test Display from 1 to 2 every 2 iteration
     */
    public function testAdvance2Until10(): void
    {
        $oProgressBar = new ProgressBar(10);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [          ] 0%", ob_get_contents());
        $this->assertMemoryInOutput();

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

            $this->assertMemoryInOutput();
        }

        $this->assertSame(22, $this->getCount());

        ob_clean();
    }

    /**
     * @test ->advance() with the iteration has already maxed out
     */
    public function testAdvanceMoreThanMax(): void
    {
        $oProgressBar = new ProgressBar(2);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 2 [  ] 0%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(2);
        $this->assertStringStartsWith("\e[1000D\33[2K\t2 / 2 [##] 100%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $this->assertTrue($oProgressBar->isFinished());

        // Nothing is displayed
        $this->expectOutputString('');

        $oProgressBar->advance(1);
    }

    /**
     * @test __construct() with a number of symbols below the max value
     */
    public function testNumberOfSymbolsBelowMax(): void
    {
        // 1 symbol every 2 iterations (even, 10 / 2)
        $oProgressBar = new ProgressBar(10, 2);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [  ] 0%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t3 / 10 [  ] 30%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(2);
        $this->assertStringStartsWith("\e[1000D\33[2K\t5 / 10 [# ] 50%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(4);
        $this->assertStringStartsWith("\e[1000D\33[2K\t9 / 10 [# ] 90%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(5);
        $this->assertStringStartsWith("\e[1000D\33[2K\t10 / 10 [##] 100%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        // 1 symbol every 4 iterations (odd, ceil(10 / 3))
        $oProgressBar = new ProgressBar(10, 3);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 10 [   ] 0%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t3 / 10 [   ] 30%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(2);
        $this->assertStringStartsWith("\e[1000D\33[2K\t5 / 10 [#  ] 50%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(1);
        $this->assertStringStartsWith("\e[1000D\33[2K\t6 / 10 [#  ] 60%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t9 / 10 [## ] 90%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->advance(3);
        $this->assertStringStartsWith("\e[1000D\33[2K\t10 / 10 [###] 100%", ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();
    }

    /**
     * @test __construct() with a number of sumbol above the max value
     */
    public function testNumberOfSymbolsAboveMax(): void
    {
        $oProgressBar = new ProgressBar(20, 25);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 20 [                    ] 0%", ob_get_contents());
        $this->assertMemoryInOutput();
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
            $this->assertMemoryInOutput();
        }

        ob_clean();
    }

    /**
     * @test Display from 1 to 1000
     */
    public function testMax1000Step1(): void
    {
        $oProgressBar = new ProgressBar(1000, 100);
        $oProgressBar->start();

        $this->assertStringStartsWith("\n\n\e[1000D\33[2K\t0 / 1000 [" . str_repeat(' ', 100) . ']', ob_get_contents());
        $this->assertMemoryInOutput();
        ob_clean();

        $expectedIterationDiez = 0;

        foreach (range(1, 1000) as $step) {
            ob_clean();

            $oProgressBar->advance(1);

            // 1 more diez every 10 iterations
            if (fmod($step, 10) === 0.0) {
                $expectedIterationDiez++;
            }

            $this->assertStringStartsWith(
                "\e[1000D\33[2K\t$step / 1000 ["
                    . str_repeat('#', $expectedIterationDiez) . str_repeat(' ', 100 - $expectedIterationDiez) . '] '
                    . $step / 10 . '%',
                ob_get_contents()
            );
            $this->assertMemoryInOutput();
        }

        $oProgressBar = new ProgressBar(1000);
        $oProgressBar->start();

        $expectedIterationDiez = 0;

        foreach (range(1, 1000) as $step) {
            ob_clean();

            $oProgressBar->advance(1);

            // 1 more diez every 20 iterations
            if (fmod($step, 20) === 0.0) {
                $expectedIterationDiez++;
            }

            $this->assertStringStartsWith(
                "\e[1000D\33[2K\t$step / 1000 ["
                    . str_repeat('#', $expectedIterationDiez) . str_repeat(' ', 50 - $expectedIterationDiez) . '] '
                    . $step / 10 . '%',
                ob_get_contents()
            );
            $this->assertMemoryInOutput();
        }

        ob_clean();
    }

    /**
     * @test ->finish() without calling ->start() -> does not do anything
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
        $this->assertMemoryInOutput();
        ob_clean();

        $oProgressBar->finish();

        $this->assertStringStartsWith("\e[1000D\33[2K\t10 / 10 [" . str_repeat('#', 10) . '] 100%', ob_get_contents());
        $this->assertMemoryInOutput();

        ob_clean();
    }

    /**
     * @test ->setMax()
     */
    public function testSetMax(): void
    {
        $oProgressBar = new ProgressBar(10);
        $oProgressBar->start();
        $this->assertSame(10, $oProgressBar->getMax());

        ob_clean();
        $oProgressBar->finish();
        $this->assertStringStartsWith("\e[1000D\33[2K\t10 / 10 [##########] 100%", ob_get_contents());
        ob_clean();

        $oProgressBar->setMax(5)->start();
        $this->assertSame(5, $oProgressBar->getMax());
        $this->assertStringStartsWith("\e[1000D\33[2K\t0 / 5 [     ] 0%", ob_get_contents());

        ob_clean();
        $oProgressBar->setMax(3, 2)->start();
        $this->assertSame(3, $oProgressBar->getMax());
        $this->assertStringStartsWith("\e[1000D\33[2K\t0 / 3 [  ] 0%", ob_get_contents());
        ob_clean();

        ob_clean();
        $oProgressBar->finish();
        $this->assertStringStartsWith("\e[1000D\33[2K\t3 / 3 [##] 100%", ob_get_contents());
        ob_clean();
    }

    public function testGetFormattedTime(): void
    {
        $this->assertSame('1ms', ProgressBar::getFormattedTime(1));
        $this->assertSame('99ms', ProgressBar::getFormattedTime(99));
        $this->assertSame('105.55ms', ProgressBar::getFormattedTime(105.554));
        $this->assertSame('999.99ms', ProgressBar::getFormattedTime(999.99));

        $this->assertSame('1sec', ProgressBar::getFormattedTime(1000));
        $this->assertSame('1sec', ProgressBar::getFormattedTime(1001));
        $this->assertSame('1.01sec', ProgressBar::getFormattedTime(1010));
        $this->assertSame('1.02sec', ProgressBar::getFormattedTime(1019));
        $this->assertSame('1.02sec', ProgressBar::getFormattedTime(1020));
        $this->assertSame('1.1sec', ProgressBar::getFormattedTime(1100));
        $this->assertSame('59sec', ProgressBar::getFormattedTime(59000));
        $this->assertSame('59.99sec', ProgressBar::getFormattedTime(59994));

        $this->assertSame('1min0s', ProgressBar::getFormattedTime(60000));
        $this->assertSame('1min6s', ProgressBar::getFormattedTime(66000));
        $this->assertSame('2min0s', ProgressBar::getFormattedTime(120000));
        $this->assertSame('59min59s', ProgressBar::getFormattedTime(3599999));

        $this->assertSame('1h0min0s', ProgressBar::getFormattedTime(3600000));
        $this->assertSame('1h20min0s', ProgressBar::getFormattedTime(4800000));
        $this->assertSame('1h23min20s', ProgressBar::getFormattedTime(5000000));
    }

    /**
     * Include functional.php, does not test anything, only displays progress bars
     */
    public function testFunctional(): void
    {
        if (getenv('display_functional') !== 'true') {
            $this->markTestSkipped();
        }

        include 'functional.php';

        $this->assertTrue(true);
        ob_start();

        print "\n";
    }

    /**
     * Asserts the memory is displayed in the progress bar
     */
    private function assertMemoryInOutput(): void
    {
        $this->assertStringContainsString(
            Style::stylize(round(memory_get_usage(true) / 1048576, 2) . ' MB'),
            ob_get_contents()
        );
    }
}
