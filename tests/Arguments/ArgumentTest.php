<?php

namespace RayanLevert\Cli\Tests\Arguments;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Cli\Arguments\Argument;
use RayanLevert\Cli\Arguments\Exception;
use RayanLevert\Cli\Arguments\ParseException;

#[CoversClass(Argument::class)]
class ArgumentTest extends \PHPUnit\Framework\TestCase
{
    /** __construct() and exception throws of incompatible options */
    #[Test]
    public function constructIncompatiblesOptions(): void
    {
        try {
            new Argument('testArgument', [
                'required'      => true,
                'defaultValue'  => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertSame('A noValue|required argument cannot have the default value', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            new Argument('testArgument', [
                'noValue'      => true,
                'defaultValue' => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertSame('A noValue|required argument cannot have the default value', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            new Argument('testArgument', [
                'required' => true,
                'prefix'   => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertSame('A prefixed argument cannot be required', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            new Argument('testArgument', [
                'required'   => true,
                'longPrefix' => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertSame('A prefixed argument cannot be required', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /** __construct() each option with getters */
    #[Test]
    public function constructGetters(): void
    {
        // with no option
        $oArgument = new Argument('test');

        $this->assertSame('test', $oArgument->getName());
        $this->assertSame('', $oArgument->getDescription());
        $this->assertNull($oArgument->getDefaultValue());
        $this->assertFalse($oArgument->isRequired());
        $this->assertFalse($oArgument->hasNoValue());
        $this->assertSame('string', $oArgument->getCastTo());
        $this->assertSame('', $oArgument->getPrefix());
        $this->assertSame('', $oArgument->getLongPrefix());
        $this->assertFalse($oArgument->hasBeenHandled());

        $oArgument = new Argument('test', ['description' => 'testDescription']);
        $this->assertSame('testDescription', $oArgument->getDescription());

        $oArgument = new Argument('test', ['required' => true]);
        $this->assertTrue($oArgument->isRequired());

        $oArgument = new Argument('test', ['noValue' => true]);
        $this->assertTrue($oArgument->hasNoValue());

        $oArgument = new Argument('test', ['prefix' => 't']);
        $this->assertSame('t', $oArgument->getPrefix());

        $oArgument = new Argument('test', ['longPrefix' => 'test']);
        $this->assertSame('test', $oArgument->getLongPrefix());

        // castTo to integer
        $oArgument = new Argument('test', ['castTo' => 'int']);
        $this->assertSame('integer', $oArgument->getCastTo());

        $oArgument = new Argument('test', ['castTo' => 'integer']);
        $this->assertSame('integer', $oArgument->getCastTo());

        // castTo to double
        $oArgument = new Argument('test', ['castTo' => 'float']);
        $this->assertSame('double', $oArgument->getCastTo());

        $oArgument = new Argument('test', ['castTo' => 'double']);
        $this->assertSame('double', $oArgument->getCastTo());

        // castTo to string
        $oArgument = new Argument('test', ['castTo' => 'string']);
        $this->assertSame('string', $oArgument->getCastTo());

        // castTo to boolean => exception throw
        try {
            $oArgument = new Argument('test', ['castTo' => 'bool']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('castTo cannot be of type bool, use the option "noValue"', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            $oArgument = new Argument('test', ['castTo' => 'boolean']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('castTo cannot be of type bool, use the option "noValue"', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /** option defaultValue */
    #[Test]
    public function defaultValue(): void
    {
        // integer
        $oArgument = new Argument('test', ['castTo' => 'int', 'defaultValue' => 10]);
        $this->assertIsInt($oArgument->getDefaultValue());
        $this->assertSame(10, $oArgument->getDefaultValue());

        // float
        $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 5.5]);
        $this->assertIsFloat($oArgument->getDefaultValue());
        $this->assertSame(5.5, $oArgument->getDefaultValue());

        // string
        $oArgument = new Argument('test', ['defaultValue' => 'defaultValue']);
        $this->assertIsString($oArgument->getDefaultValue());
        $this->assertSame('defaultValue', $oArgument->getDefaultValue());

        // no value set = NULL
        $oArgument = new Argument('test');
        $this->assertNull($oArgument->getDefaultValue());

        // we recover every incorrect PHP type = does not assign the value
        foreach ([true, false, [], new \stdClass(), fopen(__FILE__, 'r')] as $incorrectValue) {
            $oArgument = new Argument('test', ['defaultValue' => $incorrectValue]);

            $this->assertNull($oArgument->getDefaultValue());
        }

        // castTo string and defaultValue string OK
        $oArgument = new Argument('test', ['defaultValue' => 'test']);

        try {
            // castTo string and defaultValue integer => exception
            $oArgument = new Argument('test', ['defaultValue' => 12]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Default value is not the same type as castTo option (string)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            // castTo string and defaultValue float => exception
            $oArgument = new Argument('test', ['defaultValue' => 45.5]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Default value is not the same type as castTo option (string)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // castTo integer and defaultValue integer => OK
        $oArgument = new Argument('test', ['castTo' => 'integer', 'defaultValue' => 12]);

        try {
            // castTo integer and defaultValue string => exception
            $oArgument = new Argument('test', ['castTo' => 'integer', 'defaultValue' => '12']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Default value is not the same type as castTo option (integer)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            // castTo integer and defaultValue float => exception
            $oArgument = new Argument('test', ['castTo' => 'integer', 'defaultValue' => 45.5]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Default value is not the same type as castTo option (integer)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // castTo float and defaultValue float => OK
        $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 12.4]);

        try {
            // castTo float and defaultValue string => exception
            $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => '12']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Default value is not the same type as castTo option (double)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            // castTo integer and defaultValue integer => exception
            $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 45]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Default value is not the same type as castTo option (double)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /** __construct with an incorrect castTo */
    #[Test]
    public function castToNotCorrectType(): void
    {
        $this->expectExceptionObject(new Exception('incorrect-type is not a native PHP type'));

        new Argument('test', ['castTo' => 'incorrect-type']);
    }

    /** ->setValueParsed() with a string argument */
    #[Test]
    public function parseArgumentString(): void
    {
        // parses a string argument without castTo => string
        $oArgument = new Argument('test');
        $oArgument->setValueParsed('value');

        $this->assertSame('value', $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());
    }

    /** ->setValueParsed() with an integer argument */
    #[Test]
    public function parseArgumentInteger(): void
    {
        // parses a numeric string argument with castTo integer => value int
        $oArgument = new Argument('test', ['castTo' => 'integer']);
        $oArgument->setValueParsed('12');

        $this->assertIsInt($oArgument->getValue());
        $this->assertSame(12, $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());

        // parses a non numeric string argument with castTo integer => throws an exception
        $oArgument = new Argument('test', ['castTo' => 'integer']);

        try {
            $oArgument->setValueParsed('stringwithnonumber');

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertSame('Argument test is not a numeric string (must cast to integer)', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /** ->setValueParsed() with a float argument */
    #[Test]
    public function parseArgumentFloat(): void
    {
        // parses a integer string with castTo float => value float
        $oArgument = new Argument('test', ['castTo' => 'float']);
        $oArgument->setValueParsed('12');

        $this->assertIsFloat($oArgument->getValue());
        $this->assertSame(12.0, $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());

        // parses a numeric string argument with castTo float => value float
        $oArgument = new Argument('test', ['castTo' => 'float']);
        $oArgument->setValueParsed('12.8');

        $this->assertIsFloat($oArgument->getValue());
        $this->assertSame(12.8, $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());

        // parses a numeric string argument with castTo float avec virgule => throws exception
        $oArgument = new Argument('test', ['castTo' => 'float']);

        try {
            $oArgument->setValueParsed('12,8');

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertSame(
                'Argument test is not a floating point number (must cast to float)',
                $e->getMessage()
            );

            $this->assertInstanceOf(ParseException::class, $e);
        }

        // parses a non numeric string with castTo integer => throw
        $oArgument = new Argument('test', ['castTo' => 'float']);

        try {
            $oArgument->setValueParsed('stringwithnonumber');

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertSame(
                'Argument test is not a floating point number (must cast to float)',
                $e->getMessage()
            );

            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /** ->getInfos() with no option */
    #[Test]
    public function getInfosOptionsEmpty(): void
    {
        $this->assertSame('test (type: string)', new Argument('test')->getInfos());
    }

    /** ->getInfos() with required arguments */
    #[Test]
    public function getInfosRequired(): void
    {
        $this->assertSame('test (type: string)', new Argument('test')->getInfos());

        $this->assertSame('test (type: double)', new Argument('test', ['castTo' => 'float'])->getInfos());
    }

    /** ->getInfos() with only prefixes */
    #[Test]
    public function getInfosWithPrefixes(): void
    {
        // Only shortPrefix
        $oArgument = new Argument('test', ['prefix' => 't']);
        $this->assertSame('test -t=test (type: string)', $oArgument->getInfos());

        // Only longPrefix
        $oArgument = new Argument('test', ['longPrefix' => 'longtest']);
        $this->assertSame('test --longtest=test (type: string)', $oArgument->getInfos());

        // longPrefix and prefix
        $oArgument = new Argument('test', ['longPrefix' => 'longtest', 'prefix' => 't']);
        $this->assertSame('test -t=test, --longtest=test (type: string)', $oArgument->getInfos());

        // Only shortPrefix noValue
        $oArgument = new Argument('test', ['prefix' => 't', 'noValue' => true]);
        $this->assertSame('test -t', $oArgument->getInfos());

        // Only longPrefix with noValue
        $oArgument = new Argument('test', ['longPrefix' => 'longtest', 'noValue' => true]);
        $this->assertSame('test --longtest', $oArgument->getInfos());

        // longPrefix and shortPrefix with noValue
        $oArgument = new Argument('test', ['longPrefix' => 'longtest', 'prefix' => 't', 'noValue' => true]);
        $this->assertSame('test -t, --longtest', $oArgument->getInfos());
    }

    /** ->getInfos() with a description */
    #[Test]
    public function getInfosDescription(): void
    {
        $oArgument = new Argument('test', ['description' => 'Test description']);

        $this->assertSame("test (type: string)\n\t  Test description", $oArgument->getInfos());
    }

    /** ->getInfos() with a default value */
    #[Test]
    public function getInfosDefaultValue(): void
    {
        $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 56.56]);

        $this->assertSame('test (type: double) (default: 56.56)', $oArgument->getInfos());
    }

    /** ->getInfos() with a cast type */
    #[Test]
    public function getInfosCastTo(): void
    {
        $oArgument = new Argument('test', ['castTo' => 'float']);

        $this->assertSame('test (type: double)', $oArgument->getInfos());
    }

    /** ->getInfos() with every option mixed */
    #[Test]
    public function getInfosAllOptions(): void
    {
        // With prefix noValue
        $oArgument = new Argument('test', [
            'description'  => 'Test description',
            'castTo'       => 'int',
            'prefix'       => 't',
            'noValue'      => true,
            'longPrefix'   => 'longtest'
        ]);

        $this->assertSame(
            "test -t, --longtest\n\t  Test description",
            $oArgument->getInfos()
        );

        //  With prefix
        $oArgument = new Argument('test', [
            'description'  => 'Test description',
            'defaultValue' => 12,
            'castTo'       => 'int',
            'prefix'       => 't',
            'longPrefix'   => 'longtest'
        ]);

        $this->assertSame(
            "test -t=test, --longtest=test (type: integer) (default: 12)\n\t  Test description",
            $oArgument->getInfos()
        );

        // Without a prefix
        $oArgument = new Argument('test', [
            'description'  => 'Test description',
            'defaultValue' => 'test',
            'castTo'       => 'string',
        ]);

        $this->assertSame(
            "test (type: string) (default: test)\n\t  Test description",
            $oArgument->getInfos()
        );
    }
}
