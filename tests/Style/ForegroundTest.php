<?php

namespace RayanLevert\Cli\Tests\Style;

use RayanLevert\Cli\Style\Foreground;

class ForegroundTest extends \PHPUnit\Framework\TestCase
{
    public function testBlack(): void
    {
        $this->assertSame(Foreground::BLACK, Foreground::tryFromTag('fgblack'));
    }

    public function testRed(): void
    {
        $this->assertSame(Foreground::RED, Foreground::tryFromTag('fgred'));
    }

    public function testGreen(): void
    {
        $this->assertSame(Foreground::GREEN, Foreground::tryFromTag('fggreen'));
    }

    public function testYellow(): void
    {
        $this->assertSame(Foreground::YELLOW, Foreground::tryFromTag('fgyellow'));
    }

    public function testBlue(): void
    {
        $this->assertSame(Foreground::BLUE, Foreground::tryFromTag('fgblue'));
    }

    public function testCyan(): void
    {
        $this->assertSame(Foreground::CYAN, Foreground::tryFromTag('fgcyan'));
    }

    public function testDarkgray(): void
    {
        $this->assertSame(Foreground::DARK_GRAY, Foreground::tryFromTag('fgdarkgray'));
    }

    public function testLightred(): void
    {
        $this->assertSame(Foreground::LIGHT_RED, Foreground::tryFromTag('fglightred'));
    }

    public function testLightgreen(): void
    {
        $this->assertSame(Foreground::LIGHT_GREEN, Foreground::tryFromTag('fglightgreen'));
    }

    public function testBrown(): void
    {
        $this->assertSame(Foreground::BROWN, Foreground::tryFromTag('fgbrown'));
    }

    public function testLightblue(): void
    {
        $this->assertSame(Foreground::LIGHT_BLUE, Foreground::tryFromTag('fglightblue'));
    }

    public function testPurple(): void
    {
        $this->assertSame(Foreground::PURPLE, Foreground::tryFromTag('fgpurple'));
    }

    public function testLightpurple(): void
    {
        $this->assertSame(Foreground::LIGHT_PURPLE, Foreground::tryFromTag('fglightpurple'));
    }

    public function testLightcyan(): void
    {
        $this->assertSame(Foreground::LIGHT_CYAN, Foreground::tryFromTag('fglightcyan'));
    }

    public function testLightgray(): void
    {
        $this->assertSame(Foreground::LIGHT_GRAY, Foreground::tryFromTag('fglightgray'));
    }

    public function testWhite(): void
    {
        $this->assertSame(Foreground::WHITE, Foreground::tryFromTag('fgwhite'));
    }

    public function testNull(): void
    {
        $this->assertNull(Foreground::tryFromTag('test'));
        $this->assertNull(Foreground::tryFromTag('bgred'));
        $this->assertNull(Foreground::tryFromTag('not-a-tag'));
    }
}
