<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RayanLevert\Cli\Style\Background;

#[CoversClass(Background::class)]
class BackgroundTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[TestDox('Background::tryFromTag returns BLACK for bgblack')]
    public function black(): void
    {
        $this->assertSame(Background::BLACK, Background::tryFromTag('bgblack'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns RED for bgred')]
    public function red(): void
    {
        $this->assertSame(Background::RED, Background::tryFromTag('bgred'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns GREEN for bggreen')]
    public function green(): void
    {
        $this->assertSame(Background::GREEN, Background::tryFromTag('bggreen'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns YELLOW for bgyellow')]
    public function yellow(): void
    {
        $this->assertSame(Background::YELLOW, Background::tryFromTag('bgyellow'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns BLUE for bgblue')]
    public function blue(): void
    {
        $this->assertSame(Background::BLUE, Background::tryFromTag('bgblue'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns MAGENTA for bgmagenta')]
    public function magenta(): void
    {
        $this->assertSame(Background::MAGENTA, Background::tryFromTag('bgmagenta'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns CYAN for bgcyan')]
    public function cyan(): void
    {
        $this->assertSame(Background::CYAN, Background::tryFromTag('bgcyan'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns LIGHT_GRAY for bglightgray')]
    public function lightgray(): void
    {
        $this->assertSame(Background::LIGHT_GRAY, Background::tryFromTag('bglightgray'));
    }

    #[Test]
    #[TestDox('Background::tryFromTag returns null for unknown tags')]
    public function null(): void
    {
        $this->assertNull(Background::tryFromTag('test'));
        $this->assertNull(Background::tryFromTag('underline'));
        $this->assertNull(Background::tryFromTag('not-a-tag'));
    }
}
