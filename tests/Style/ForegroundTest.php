<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RayanLevert\Cli\Style\Foreground;

#[CoversClass(Foreground::class)]
class ForegroundTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[TestDox('fgblack returns Foreground::BLACK')]
    public function black(): void
    {
        $this->assertSame(Foreground::BLACK, Foreground::tryFromTag('fgblack'));
    }

    #[Test]
    #[TestDox('fgred returns Foreground::RED')]
    public function red(): void
    {
        $this->assertSame(Foreground::RED, Foreground::tryFromTag('fgred'));
    }

    #[Test]
    #[TestDox('fggreen returns Foreground::GREEN')]
    public function green(): void
    {
        $this->assertSame(Foreground::GREEN, Foreground::tryFromTag('fggreen'));
    }

    #[Test]
    #[TestDox('fgyellow returns Foreground::YELLOW')]
    public function yellow(): void
    {
        $this->assertSame(Foreground::YELLOW, Foreground::tryFromTag('fgyellow'));
    }

    #[Test]
    #[TestDox('fgblue returns Foreground::BLUE')]
    public function blue(): void
    {
        $this->assertSame(Foreground::BLUE, Foreground::tryFromTag('fgblue'));
    }

    #[Test]
    #[TestDox('fgcyan returns Foreground::CYAN')]
    public function cyan(): void
    {
        $this->assertSame(Foreground::CYAN, Foreground::tryFromTag('fgcyan'));
    }

    #[Test]
    #[TestDox('fgdarkgray returns Foreground::DARK_GRAY')]
    public function darkgray(): void
    {
        $this->assertSame(Foreground::DARK_GRAY, Foreground::tryFromTag('fgdarkgray'));
    }

    #[Test]
    #[TestDox('fglightred returns Foreground::LIGHT_RED')]
    public function lightred(): void
    {
        $this->assertSame(Foreground::LIGHT_RED, Foreground::tryFromTag('fglightred'));
    }

    #[Test]
    #[TestDox('fglightgreen returns Foreground::LIGHT_GREEN')]
    public function lightgreen(): void
    {
        $this->assertSame(Foreground::LIGHT_GREEN, Foreground::tryFromTag('fglightgreen'));
    }

    #[Test]
    #[TestDox('fgbrown returns Foreground::BROWN')]
    public function brown(): void
    {
        $this->assertSame(Foreground::BROWN, Foreground::tryFromTag('fgbrown'));
    }

    #[Test]
    #[TestDox('fglightblue returns Foreground::LIGHT_BLUE')]
    public function lightblue(): void
    {
        $this->assertSame(Foreground::LIGHT_BLUE, Foreground::tryFromTag('fglightblue'));
    }

    #[Test]
    #[TestDox('fgpurple returns Foreground::PURPLE')]
    public function purple(): void
    {
        $this->assertSame(Foreground::PURPLE, Foreground::tryFromTag('fgpurple'));
    }

    #[Test]
    #[TestDox('fglightpurple returns Foreground::LIGHT_PURPLE')]
    public function lightpurple(): void
    {
        $this->assertSame(Foreground::LIGHT_PURPLE, Foreground::tryFromTag('fglightpurple'));
    }

    #[Test]
    #[TestDox('fglightcyan returns Foreground::LIGHT_CYAN')]
    public function lightcyan(): void
    {
        $this->assertSame(Foreground::LIGHT_CYAN, Foreground::tryFromTag('fglightcyan'));
    }

    #[Test]
    #[TestDox('fglightgray returns Foreground::LIGHT_GRAY')]
    public function lightgray(): void
    {
        $this->assertSame(Foreground::LIGHT_GRAY, Foreground::tryFromTag('fglightgray'));
    }

    #[Test]
    #[TestDox('fgwhite returns Foreground::WHITE')]
    public function white(): void
    {
        $this->assertSame(Foreground::WHITE, Foreground::tryFromTag('fgwhite'));
    }

    #[Test]
    #[TestDox('Returns null for invalid tags')]
    public function null(): void
    {
        $this->assertNull(Foreground::tryFromTag('test'));
        $this->assertNull(Foreground::tryFromTag('bgred'));
        $this->assertNull(Foreground::tryFromTag('not-a-tag'));
    }
}
