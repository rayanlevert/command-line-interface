<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Cli\Style\Background;

#[CoversClass(Background::class)]
class BackgroundTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function black(): void
    {
        $this->assertSame(Background::BLACK, Background::tryFromTag('bgblack'));
    }

    #[Test]
    public function red(): void
    {
        $this->assertSame(Background::RED, Background::tryFromTag('bgred'));
    }

    #[Test]
    public function green(): void
    {
        $this->assertSame(Background::GREEN, Background::tryFromTag('bggreen'));
    }

    #[Test]
    public function yellow(): void
    {
        $this->assertSame(Background::YELLOW, Background::tryFromTag('bgyellow'));
    }

    #[Test]
    public function blue(): void
    {
        $this->assertSame(Background::BLUE, Background::tryFromTag('bgblue'));
    }

    #[Test]
    public function magenta(): void
    {
        $this->assertSame(Background::MAGENTA, Background::tryFromTag('bgmagenta'));
    }

    #[Test]
    public function cyan(): void
    {
        $this->assertSame(Background::CYAN, Background::tryFromTag('bgcyan'));
    }

    #[Test]
    public function lightgray(): void
    {
        $this->assertSame(Background::LIGHT_GRAY, Background::tryFromTag('bglightgray'));
    }

    #[Test]
    public function null(): void
    {
        $this->assertNull(Background::tryFromTag('test'));
        $this->assertNull(Background::tryFromTag('underline'));
        $this->assertNull(Background::tryFromTag('not-a-tag'));
    }
}
