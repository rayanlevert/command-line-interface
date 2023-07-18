<?php

namespace DisDev\Cli\Tests\Style;

use DisDev\Cli\Style\Background;

class BackgroundTest extends \PHPUnit\Framework\TestCase
{
    public function testBlack(): void
    {
        $this->assertSame(Background::BLACK, Background::tryFromTag('bgblack'));
    }

    public function testRed(): void
    {
        $this->assertSame(Background::RED, Background::tryFromTag('bgred'));
    }

    public function testGreen(): void
    {
        $this->assertSame(Background::GREEN, Background::tryFromTag('bggreen'));
    }

    public function testYellow(): void
    {
        $this->assertSame(Background::YELLOW, Background::tryFromTag('bgyellow'));
    }

    public function testBlue(): void
    {
        $this->assertSame(Background::BLUE, Background::tryFromTag('bgblue'));
    }

    public function testMagenta(): void
    {
        $this->assertSame(Background::MAGENTA, Background::tryFromTag('bgmagenta'));
    }

    public function testCyan(): void
    {
        $this->assertSame(Background::CYAN, Background::tryFromTag('bgcyan'));
    }

    public function testLightgray(): void
    {
        $this->assertSame(Background::LIGHT_GRAY, Background::tryFromTag('bglightgray'));
    }

    public function testNull(): void
    {
        $this->assertNull(Background::tryFromTag('test'));
        $this->assertNull(Background::tryFromTag('underline'));
        $this->assertNull(Background::tryFromTag('not-a-tag'));
    }
}
