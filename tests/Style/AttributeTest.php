<?php

namespace DisDev\Cli\Tests\Style;

use DisDev\Cli\Style\Attribute;

class AttributeTest extends \PHPUnit\Framework\TestCase
{
    public function testBold(): void
    {
        $this->assertSame(Attribute::BOLD, Attribute::tryFromTag('b'));
        $this->assertSame(Attribute::BOLD, Attribute::tryFromTag('bold'));
    }

    public function testItalic(): void
    {
        $this->assertSame(Attribute::ITALIC, Attribute::tryFromTag('i'));
        $this->assertSame(Attribute::ITALIC, Attribute::tryFromTag('italic'));
    }

    public function testUnderline(): void
    {
        $this->assertSame(Attribute::UNDERLINE, Attribute::tryFromTag('u'));
        $this->assertSame(Attribute::UNDERLINE, Attribute::tryFromTag('underline'));
    }

    public function testBlink(): void
    {
        $this->assertSame(Attribute::BLINK, Attribute::tryFromTag('blink'));
    }

    public function testOutline(): void
    {
        $this->assertSame(Attribute::OUTLINE, Attribute::tryFromTag('outline'));
    }

    public function testReverse(): void
    {
        $this->assertSame(Attribute::REVERSE, Attribute::tryFromTag('reverse'));
    }

    public function testNondisp(): void
    {
        $this->assertSame(Attribute::NONDISP, Attribute::tryFromTag('nondisp'));
    }

    public function testStrike(): void
    {
        $this->assertSame(Attribute::STRIKE, Attribute::tryFromTag('strike'));
    }

    public function testNull(): void
    {
        $this->assertNull(Attribute::tryFromTag('test'));
        $this->assertNull(Attribute::tryFromTag('red'));
        $this->assertNull(Attribute::tryFromTag('not-a-tag'));
    }
}
