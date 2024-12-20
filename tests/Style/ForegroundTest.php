<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Cli\Style\Foreground;

#[CoversClass(Foreground::class)]
class ForegroundTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function black(): void
    {
        $this->assertSame(Foreground::BLACK, Foreground::tryFromTag('fgblack'));
    }

    #[Test]
    public function red(): void
    {
        $this->assertSame(Foreground::RED, Foreground::tryFromTag('fgred'));
    }

    #[Test]
    public function green(): void
    {
        $this->assertSame(Foreground::GREEN, Foreground::tryFromTag('fggreen'));
    }

    #[Test]
    public function yellow(): void
    {
        $this->assertSame(Foreground::YELLOW, Foreground::tryFromTag('fgyellow'));
    }

    #[Test]
    public function blue(): void
    {
        $this->assertSame(Foreground::BLUE, Foreground::tryFromTag('fgblue'));
    }

    #[Test]
    public function cyan(): void
    {
        $this->assertSame(Foreground::CYAN, Foreground::tryFromTag('fgcyan'));
    }

    #[Test]
    public function darkgray(): void
    {
        $this->assertSame(Foreground::DARK_GRAY, Foreground::tryFromTag('fgdarkgray'));
    }

    #[Test]
    public function lightred(): void
    {
        $this->assertSame(Foreground::LIGHT_RED, Foreground::tryFromTag('fglightred'));
    }

    #[Test]
    public function lightgreen(): void
    {
        $this->assertSame(Foreground::LIGHT_GREEN, Foreground::tryFromTag('fglightgreen'));
    }

    #[Test]
    public function brown(): void
    {
        $this->assertSame(Foreground::BROWN, Foreground::tryFromTag('fgbrown'));
    }

    #[Test]
    public function lightblue(): void
    {
        $this->assertSame(Foreground::LIGHT_BLUE, Foreground::tryFromTag('fglightblue'));
    }

    #[Test]
    public function purple(): void
    {
        $this->assertSame(Foreground::PURPLE, Foreground::tryFromTag('fgpurple'));
    }

    #[Test]
    public function lightpurple(): void
    {
        $this->assertSame(Foreground::LIGHT_PURPLE, Foreground::tryFromTag('fglightpurple'));
    }

    #[Test]
    public function lightcyan(): void
    {
        $this->assertSame(Foreground::LIGHT_CYAN, Foreground::tryFromTag('fglightcyan'));
    }

    #[Test]
    public function lightgray(): void
    {
        $this->assertSame(Foreground::LIGHT_GRAY, Foreground::tryFromTag('fglightgray'));
    }

    #[Test]
    public function white(): void
    {
        $this->assertSame(Foreground::WHITE, Foreground::tryFromTag('fgwhite'));
    }

    #[Test]
    public function null(): void
    {
        $this->assertNull(Foreground::tryFromTag('test'));
        $this->assertNull(Foreground::tryFromTag('bgred'));
        $this->assertNull(Foreground::tryFromTag('not-a-tag'));
    }
}
