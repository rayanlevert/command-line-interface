<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Cli\Style\Attribute;

#[CoversClass(Attribute::class)]
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function bold(): void
    {
        $this->assertSame(Attribute::BOLD, Attribute::tryFromTag('b'));
        $this->assertSame(Attribute::BOLD, Attribute::tryFromTag('bold'));
    }

    #[Test]
    public function italic(): void
    {
        $this->assertSame(Attribute::ITALIC, Attribute::tryFromTag('i'));
        $this->assertSame(Attribute::ITALIC, Attribute::tryFromTag('italic'));
    }

    #[Test]
    public function underline(): void
    {
        $this->assertSame(Attribute::UNDERLINE, Attribute::tryFromTag('u'));
        $this->assertSame(Attribute::UNDERLINE, Attribute::tryFromTag('underline'));
    }

    #[Test]
    public function blink(): void
    {
        $this->assertSame(Attribute::BLINK, Attribute::tryFromTag('blink'));
    }

    #[Test]
    public function outline(): void
    {
        $this->assertSame(Attribute::OUTLINE, Attribute::tryFromTag('outline'));
    }

    #[Test]
    public function reverse(): void
    {
        $this->assertSame(Attribute::REVERSE, Attribute::tryFromTag('reverse'));
    }

    #[Test]
    public function nondisp(): void
    {
        $this->assertSame(Attribute::NONDISP, Attribute::tryFromTag('nondisp'));
    }

    #[Test]
    public function strike(): void
    {
        $this->assertSame(Attribute::STRIKE, Attribute::tryFromTag('strike'));
    }

    #[Test]
    public function null(): void
    {
        $this->assertNull(Attribute::tryFromTag('test'));
        $this->assertNull(Attribute::tryFromTag('red'));
        $this->assertNull(Attribute::tryFromTag('not-a-tag'));
    }
}
