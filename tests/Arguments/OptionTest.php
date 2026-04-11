<?php

namespace RayanLevert\Cli\Tests\Arguments;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RayanLevert\Cli\Arguments\Option;

#[CoversClass(Option::class)]
class OptionTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[TestDox('Option cases return expected enum and values')]
    public function cases(): void
    {
        $this->assertSame(Option::DESCRIPTION, Option::tryFrom('description'));
        $this->assertSame('description', Option::DESCRIPTION->value);

        $this->assertSame(Option::REQUIRED, Option::tryFrom('required'));
        $this->assertSame('required', Option::REQUIRED->value);

        $this->assertSame(Option::NO_VALUE, Option::tryFrom('noValue'));
        $this->assertSame('noValue', Option::NO_VALUE->value);

        $this->assertSame(Option::PREFIX, Option::tryFrom('prefix'));
        $this->assertSame('prefix', Option::PREFIX->value);

        $this->assertSame(Option::LONG_PREFIX, Option::tryFrom('longPrefix'));
        $this->assertSame('longPrefix', Option::LONG_PREFIX->value);

        $this->assertSame(Option::CAST_TO, Option::tryFrom('castTo'));
        $this->assertSame('castTo', Option::CAST_TO->value);

        $this->assertSame(Option::DEFAULT_VALUE, Option::tryFrom('defaultValue'));
        $this->assertSame('defaultValue', Option::DEFAULT_VALUE->value);
    }

    #[Test]
    #[TestDox('verifiesType() returns correct boolean for allowed values')]
    public function verifiesType(): void
    {
        $this->assertTrue(Option::DESCRIPTION->verifiesType('test'));

        $this->assertTrue(Option::REQUIRED->verifiesType(true));
        $this->assertTrue(Option::REQUIRED->verifiesType(false));

        $this->assertTrue(Option::NO_VALUE->verifiesType(false));

        $this->assertTrue(Option::PREFIX->verifiesType('test'));
        $this->assertTrue(Option::LONG_PREFIX->verifiesType('test'));
        $this->assertTrue(Option::CAST_TO->verifiesType('test'));

        $this->assertTrue(Option::DEFAULT_VALUE->verifiesType('test'));
        $this->assertTrue(Option::DEFAULT_VALUE->verifiesType(12));
        $this->assertTrue(Option::DEFAULT_VALUE->verifiesType(12.5));
    }

    #[Test]
    #[TestDox('getPhpProperty() returns expected PHP property names')]
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
