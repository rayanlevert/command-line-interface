<?php

namespace RayanLevert\Cli\Tests\Arguments;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Cli\Arguments\Option;

#[CoversClass(Option::class)]
class OptionTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function verifiesType(): void
    {
        $this->assertTrue(Option::DESCRIPTION->verifiesType('A description'));
        $this->assertTrue(Option::REQUIRED->verifiesType(true));
        $this->assertTrue(Option::NO_VALUE->verifiesType(false));
        $this->assertTrue(Option::PREFIX->verifiesType('-u='));
        $this->assertTrue(Option::LONG_PREFIX->verifiesType('--user='));
        $this->assertTrue(Option::CAST_TO->verifiesType('int'));
        $this->assertTrue(Option::DEFAULT_VALUE->verifiesType('default'));
        $this->assertTrue(Option::DEFAULT_VALUE->verifiesType(42));
        $this->assertTrue(Option::DEFAULT_VALUE->verifiesType(3.14));

        $this->assertFalse(Option::DESCRIPTION->verifiesType(true));
        $this->assertFalse(Option::REQUIRED->verifiesType('yes'));
        $this->assertFalse(Option::NO_VALUE->verifiesType('no'));
        $this->assertFalse(Option::PREFIX->verifiesType(123));
        $this->assertFalse(Option::LONG_PREFIX->verifiesType(456.78));
        $this->assertFalse(Option::CAST_TO->verifiesType(false));
        $this->assertFalse(Option::DEFAULT_VALUE->verifiesType([]));
    }

    #[Test]
    public function getPhpProperty(): void
    {
        $this->assertSame('description', Option::DESCRIPTION->getPhpProperty());
        $this->assertSame('isRequired', Option::REQUIRED->getPhpProperty());
        $this->assertSame('noValue', Option::NO_VALUE->getPhpProperty());
        $this->assertSame('prefix', Option::PREFIX->getPhpProperty());
        $this->assertSame('longPrefix', Option::LONG_PREFIX->getPhpProperty());
        $this->assertSame('castTo', Option::CAST_TO->getPhpProperty());
        $this->assertSame('defaultValue', Option::DEFAULT_VALUE->getPhpProperty());
    }
}
