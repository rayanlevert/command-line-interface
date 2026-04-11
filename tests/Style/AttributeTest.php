<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RayanLevert\Cli\Style\Attribute;

#[CoversClass(Attribute::class)]
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::BOLD for "b" and "bold"')]
    public function bold(): void
    {
        $this->assertSame(Attribute::BOLD, Attribute::tryFromTag('b'));
        $this->assertSame(Attribute::BOLD, Attribute::tryFromTag('bold'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::ITALIC for "i" and "italic"')]
    public function italic(): void
    {
        $this->assertSame(Attribute::ITALIC, Attribute::tryFromTag('i'));
        $this->assertSame(Attribute::ITALIC, Attribute::tryFromTag('italic'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::UNDERLINE for "u" and "underline"')]
    public function underline(): void
    {
        $this->assertSame(Attribute::UNDERLINE, Attribute::tryFromTag('u'));
        $this->assertSame(Attribute::UNDERLINE, Attribute::tryFromTag('underline'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::BLINK for "blink"')]
    public function blink(): void
    {
        $this->assertSame(Attribute::BLINK, Attribute::tryFromTag('blink'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::OUTLINE for "outline"')]
    public function outline(): void
    {
        $this->assertSame(Attribute::OUTLINE, Attribute::tryFromTag('outline'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::REVERSE for "reverse"')]
    public function reverse(): void
    {
        $this->assertSame(Attribute::REVERSE, Attribute::tryFromTag('reverse'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::NONDISP for "nondisp"')]
    public function nondisp(): void
    {
        $this->assertSame(Attribute::NONDISP, Attribute::tryFromTag('nondisp'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns Attribute::STRIKE for "strike"')]
    public function strike(): void
    {
        $this->assertSame(Attribute::STRIKE, Attribute::tryFromTag('strike'));
    }

    #[Test]
    #[TestDox('Attribute::tryFromTag() returns null for unknown tags')]
    public function null(): void
    {
        $this->assertNull(Attribute::tryFromTag('test'));
        $this->assertNull(Attribute::tryFromTag('red'));
        $this->assertNull(Attribute::tryFromTag('not-a-tag'));
    }
}
