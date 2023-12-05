<?php

namespace RayanLevert\Cli\Tests\Arguments;

use RayanLevert\Cli\Arguments;
use RayanLevert\Cli\Arguments\Argument;
use RayanLevert\Cli\Arguments\Exception;
use RayanLevert\Cli\Arguments\ParseException;

class ArgumentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test ->count() with a different number of arguments to the __construct and ->set()
     */
    public function testCount(): void
    {
        $oArguments = new Arguments();
        $this->assertSame(0, $oArguments->count());

        $oArguments = new Arguments(new Argument('test'));
        $this->assertSame(1, $oArguments->count());

        $oArguments = new Arguments(new Argument('test'), new Argument('test2'));
        $this->assertSame(2, $oArguments->count());

        $oArguments->set(new Argument('test3'));
        $this->assertSame(3, $oArguments->count());

        // On ajoute un argument au même nom
        $oArguments->set(new Argument('test'));
        $this->assertSame(3, $oArguments->count());

        // On en supprime un
        $oArguments->remove('test');
        $this->assertSame(2, $oArguments->count());

        $this->expectExceptionMessage('Argument test does not exist in the collection');

        $oArguments->get('test');
    }

    /**
     * @test required arguments and order
     */
    public function testRequiredArguments(): void
    {
        $oArguments = new Arguments(new Argument('test1', ['required' => true]));
        $oArguments->parse('testValeur1');

        $this->assertSame('testValeur1', $oArguments->get('test1'));

        // 2nd argument is required but the first no => exception
        try {
            $oArguments = new Arguments(new Argument('test1'), new Argument('test2', ['required' => true]));

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Required argument test2 follows a not required argument', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // 3rd argument is required but the first no => exception
        try {
            $oArguments = new Arguments(
                new Argument('test1', ['required' => true]),
                new Argument('test2'),
                new Argument('test3', ['required' => true])
            );

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Required argument test3 follows a not required argument', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // 3rd argument is required and the 2nd one is a longPrefix => correct
        $oArguments = new Arguments(
            new Argument('test1', ['required' => true]),
            new Argument('test2', ['longPrefix' => 'test2']),
            new Argument('test3', ['required' => true])
        );

        $oArguments->parse('testValue1', '--test2=test2', 'testValue3');

        $this->assertSame('testValue1', $oArguments->get('test1'));
        $this->assertSame('testValue3', $oArguments->get('test3'));
        $this->assertSame('test2', $oArguments->get('test2'));

        // Two prefixed arguments and the third is required => correct
        $oArguments = new Arguments(
            new Argument('test1', ['longPrefix' => 'test1']),
            new Argument('test2', ['longPrefix' => 'test2']),
            new Argument('test3', ['required' => true])
        );

        // Parses only the prefixed one => exception
        try {
            $oArguments->parse('--test1=t', '--test2=a');

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Argument test3 is required', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }

        // Parses a prefixed and a value one => correct
        $oArguments->parse('--test1=t1', 'testValue3');

        $this->assertSame('t1', $oArguments->get('test1'));
        $this->assertSame('testValue3', $oArguments->get('test3'));

        // 2 arguments required
        $oArguments = new Arguments(
            new Argument('test1', ['required' => true]),
            new Argument('test2', ['required' => true])
        );

        // With the setter
        try {
            $oArguments->set(new Argument('test1'));

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Required argument test2 follows a not required argument', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        $oArguments = new Arguments(
            new Argument('test1', ['prefix' => 't', 'longPrefix' => 'test']),
            new Argument('test2', ['required' => true])
        );

        try {
            $oArguments->parse();
        } catch (\Exception $e) {
            $this->assertSame('Argument test2 is required', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }

        // Second argument is useless, argument test2 required takes the value of the first parsed
        $oArguments->parse('testValeur', 'testValeur2');

        $this->assertSame('testValeur', $oArguments->get('test2'));
        $this->assertNull($oArguments->get('test1'));

        // Two parsed arguments with a same prefix => last one is set
        $oArguments->parse('test', '-t=test1', 'test3', '-t=test2');

        $this->assertSame('test', $oArguments->get('test2'));
        $this->assertSame('test2', $oArguments->get('test1'));
    }

    /**
     * @test ->parse() with no argument
     */
    public function testParseNoArgument(): void
    {
        // Aucun argument => parse est skip
        $oArguments = new Arguments();

        $oArguments->parse();
        $oArguments->parse('test', '4.5', '1');

        $this->assertSame(0, $oArguments->count());
    }

    /**
     * @test ->parse() where the collection has a single argument
     */
    public function testParseOneArgument(): void
    {
        // One non required argument
        $oArguments = new Arguments(new Argument('test'));

        // Does not throw an exception
        $argumentValue = $oArguments->get('test');
        $this->assertNull($argumentValue);

        // 1st argument setting the value, we retrieve the value testValue
        $oArguments->parse('testValue');
        $this->assertSame('testValue', $oArguments->get('test'));

        // We parse an already set argument => first one is set
        $oArguments->parse('premiereValeur');
        $this->assertSame('testValue', $oArguments->get('test'));

        // A required argument
        $oArguments = new Arguments($oArgument = new Argument('test', ['required' => true]));

        $this->assertTrue($oArgument->isRequired());
        $this->assertFalse($oArgument->hasBeenHandled());
        $this->assertNull($oArguments->get('test'));

        // Parses 0 argument => throws an exception
        try {
            $oArguments->parse();

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Argument test is required', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }

        // Non required argument with a defaultValue different from null
        $oArguments->set(new Argument('test', ['castTo' => 'int', 'defaultValue' => 25]));
        $oArguments->parse();

        $this->assertSame(25, $oArguments->get('test'));

        // Required argument with multiple parsed ones => takes the first one
        $oArguments = new Arguments($oArgument = new Argument('test1', ['required' => true]));

        $oArguments->parse('test', 'test2');
        $this->assertSame('test', $oArguments->get('test1'));

        // Non required argument with multiple parsed ones => takes the first one
        $oArguments = new Arguments($oArgument = new Argument('test1'));

        $oArguments->parse('test', 'test2');
        $this->assertSame('test', $oArguments->get('test1'));
    }

    /**
     * @test ->parse() where the collection has 2 arguments
     */
    public function testParseTwoArguments(): void
    {
        $oArguments = new Arguments(new Argument('test1'), new Argument('test2'));

        // Without defaultValue
        $oArguments->parse();

        $this->assertNull($oArguments->get('test1'));
        $this->assertNull($oArguments->get('test2'));

        $oArguments->set(new Argument('test1', ['castTo' => 'int', 'defaultValue' => 1]));
        $oArguments->set(new Argument('test2', ['castTo' => 'float', 'defaultValue' => 2.5]));

        // With defaultValue
        $oArguments->parse();

        $this->assertSame(1, $oArguments->get('test1'));
        $this->assertSame(2.5, $oArguments->get('test2'));

        // 1 required one
        $oArguments = new Arguments(new Argument('test1', ['required' => true]), new Argument('test2'));
        $oArguments->parse('test', 'testValue2');

        $this->assertSame('testValue2', $oArguments->get('test2'));

        // 2 parsed arguments => correct
        $oArguments->parse('testValue1', 'testValue2');

        $this->assertSame('testValue1', $oArguments->get('test1'));
        $this->assertSame('testValue2', $oArguments->get('test2'));
    }

    /**
     * @test ->parse() with 2 first prefixed arguments and parses two valued ones
     */
    public function testTwoFirstArgumentsPrefixParseNotRequired(): void
    {
        $oArguments = new Arguments(
            new Argument('test', ['prefix' => '-t']),
            new Argument('test2', ['longPrefix' => '--t']),
            new Argument('test3'),
            new Argument('test4')
        );

        $oArguments->parse('test1', 'test2');

        $this->assertSame($oArguments->get('test3'), 'test1');
        $this->assertSame($oArguments->get('test4'), 'test2');
    }

    /**
     * @test ->parse() with a prefixed argument value not present in the collection -> handled
     */
    public function testArgPrefixNotInCollection(): void
    {
        $oArguments = new Arguments(new Argument('test'));
        $oArguments->parse('-value');

        $this->assertSame('-value', $oArguments->get('test'));
    }

    /**
     * @test ->parse() with a long prefix argument not present in the collection -> handled
     */
    public function testArgLongPrefixNotInCollection(): void
    {
        $oArguments = new Arguments(new Argument('test'));
        $oArguments->parse('--test1');

        $this->assertSame('--test1', $oArguments->get('test'));
    }

    /**
     * @test ->parse() with multiple prefixed arguments not in the collection
     */
    public function testMultiplePrefixNotInCollection(): void
    {
        $oArguments = new Arguments(new Argument('test'), new Argument('test2'), new Argument('test3'));
        $oArguments->parse('test', '-test', '--test');

        $this->assertSame('test', $oArguments->get('test'));
        $this->assertSame('-test', $oArguments->get('test2'));
        $this->assertSame('--test', $oArguments->get('test3'));

        $oArguments = new Arguments(
            new Argument('test', ['prefix' => 'test']),
            new Argument('test2', ['longPrefix' => 'longtest', 'defaultValue' => 'default'])
        );

        $oArguments->parse('-value1', '-value2');
        $this->assertNull($oArguments->get('test'));
        $this->assertSame('default', $oArguments->get('test2'));
    }

    /**
     * @test Parsed long and short prefixed arguments (- and --, with value or not)
     */
    public function testParseShortAndLongPrefix(): void
    {
        foreach (['prefix' => '-t', 'longPrefix' => '--t'] as $optionName => $optionValue) {
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));

            // No argument parsed => defaultValue
            $oArguments->parse();
            $this->assertSame(null, $oArguments->get('test'));

            // Prses arguments with no prefix => defaultValue
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse('test', 'ae');
            $this->assertSame(null, $oArguments->get('test'));

            // Parses arguments with a prefix reverse from the iterator => defaultValue
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse($optionName === 'prefix' ? '--t' : '-t' . '=value');
            $this->assertSame(null, $oArguments->get('test'));

            // Parses arguments with a prefix without a value from an argument with value => throws exception
            try {
                $oArguments->parse($optionValue);

                $this->fail('exception expected');
            } catch (\Exception $e) {
                $this->assertSame(
                    sprintf(
                        'Prefixed argument starting with %s (t) has no = sign',
                        $optionName === 'prefix' ? '-' : '--'
                    ),
                    $e->getMessage()
                );

                $this->assertInstanceOf(ParseException::class, $e);
            }

            // without ' or "
            $oArguments->parse($optionValue . '=testValue');
            $this->assertSame('testValue', $oArguments->get('test'));

            // with ""
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse($optionValue . '="test valeur"');

            $this->assertSame('test valeur', $oArguments->get('test'));

            // with ''
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse($optionValue . "='test valeur 2'");

            $this->assertSame('test valeur 2', $oArguments->get('test'));

            // Argument not parsed with a defaultValue
            $oArguments = new Arguments(new Argument('test', [
                $optionName     => 't',
                'defaultValue'  => 20,
                'castTo'        => 'int'
            ]));

            $oArguments->parse('test');
            $this->assertSame(20, $oArguments->get('test'));

            // Argument noValue
            $oArguments = new Arguments(new Argument('test', [$optionName => 't', 'noValue' => true]));

            $oArguments->parse();
            $this->assertSame(false, $oArguments->get('test'));

            $oArguments = new Arguments(new Argument('test', [$optionName => 't', 'noValue' => true]));

            // Argument noValue with a reverse prefix from the iterator => false
            $oArguments->parse($optionName === 'prefix' ? '--t' : '-t');
            $this->assertSame(false, $oArguments->get('test'));

            // Argument noValue => true
            $oArguments = new Arguments(new Argument('test', [$optionName => 't', 'noValue' => true]));

            $oArguments->parse($optionValue);
            $this->assertSame(true, $oArguments->get('test'));
        }
    }

    /**
     * @test Parsed arguments being casted to float
     */
    public function testParseCastToFloat(): void
    {
        $oArguments = new Arguments(new Argument('test', ['castTo' => 'float']));

        $oArguments->parse();
        $this->assertNull($oArguments->get('test'));

        $oArguments->parse('200');
        $this->assertSame(200.0, $oArguments->get('test'));

        $oArguments = new Arguments(new Argument('test', ['castTo' => 'float']));

        $oArguments->parse('23.23');
        $this->assertSame(23.23, $oArguments->get('test'));

        $oArguments = new Arguments(new Argument('test', ['castTo' => 'float']));

        try {
            $oArguments->parse('string');

            $this->fail('expect exception');
        } catch (\Exception $e) {
            $this->assertSame(
                'Argument test is not a floating point number (must cast to float)',
                $e->getMessage()
            );

            $this->assertInstanceOf(ParseException::class, $e);
        }

        // 2 arguments
        $oArguments = new Arguments(new Argument('test1'), new Argument('test2', ['castTo' => 'float']));

        $oArguments->parse('testValue1', '45.18');

        $this->assertSame('testValue1', $oArguments->get('test1'));
        $this->assertSame(45.18, $oArguments->get('test2'));

        $oArguments = new Arguments(new Argument('test'), new Argument('test2', ['castTo' => 'float']));

        try {
            $oArguments->parse('testValue1', '45,18');

            $this->fail('expect exception');
        } catch (\Exception $e) {
            $this->assertSame(
                'Argument test2 is not a floating point number (must cast to float)',
                $e->getMessage()
            );

            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /**
     * @test Parsed arguments being casted to integer
     */
    public function testParseCastToInt(): void
    {
        $oArguments = new Arguments(new Argument('test', ['castTo' => 'int']));

        $oArguments->parse();
        $this->assertNull($oArguments->get('test'));

        $oArguments->parse('200');
        $this->assertSame(200, $oArguments->get('test'));

        $oArguments = new Arguments(new Argument('test', ['castTo' => 'int']));

        $oArguments->parse('23.23');
        $this->assertSame(23, $oArguments->get('test'));

        $oArguments = new Arguments(new Argument('test', ['castTo' => 'int']));

        try {
            $oArguments->parse('string');

            $this->fail('expect exception');
        } catch (\Exception $e) {
            $this->assertSame('Argument test is not a numeric string (must cast to integer)', $e->getMessage());

            $this->assertInstanceOf(ParseException::class, $e);
        }

        // 2 arguments
        $oArguments = new Arguments(new Argument('test1'), new Argument('test2', ['castTo' => 'int']));

        $oArguments->parse('testValue1', '45');

        $this->assertSame('testValue1', $oArguments->get('test1'));
        $this->assertSame(45, $oArguments->get('test2'));

        $oArguments = new Arguments(new Argument('test'), new Argument('test2', ['castTo' => 'int']));

        try {
            $oArguments->parse('testValue1', 'string');

            $this->fail('expect exception');
        } catch (\Exception $e) {
            $this->assertSame('Argument test2 is not a numeric string (must cast to integer)', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /**
     * @test Parsed arguments with a false string value ('0', '')
     */
    public function testParseStringFalsable(): void
    {
        $oArguments = new Arguments(new Argument('test', ['castTo' => 'int']));
        $oArguments->parse('0');

        $this->assertSame(0, $oArguments->get('test'));

        $oArguments = new Arguments(new Argument('test', ['castTo' => 'float']));
        $oArguments->parse('0');

        $this->assertSame(0.0, $oArguments->get('test'));

        $oArguments = new Arguments(new Argument('test', ['required' => true]));
        $oArguments->parse('');

        $this->assertSame('', $oArguments->get('test'));
    }

    /**
     * @test ->printArguments() with no argument
     */
    public function testPrintNoArgument(): void
    {
        $oArguments = new Arguments();

        $this->expectOutputString('');

        $oArguments->printArguments();
    }

    /**
     * @test ->printArguments() with a required argument
     */
    public function testPrintOneRequiredArgument(): void
    {
        $oArguments = new Arguments(new Argument('test', ['required' => true]));

        $this->expectOutputString("Required arguments:\n\ttest (type: string)");

        $oArguments->printArguments();
    }

    /**
     * @test ->printArguments() one required and one optional
     */
    public function testPrintOneRequiredOneOptional(): void
    {
        $oArguments = new Arguments(
            new Argument('test', ['required' => true]),
            new Argument('test2', ['defaultValue' => 12, 'castTo' => 'int'])
        );

        $this->expectOutputString(
            "Required arguments:\n\ttest (type: string)\n\Optional arguments:\n\ttest2 (type: integer) (default: 12)"
        );

        $oArguments->printArguments();
    }

    /**
     * @test ->printArguments() two required and one optional
     */
    public function testPrintTwoRequiredTwoOptional(): void
    {
        $oArguments = new Arguments(
            new Argument('test', ['required' => true]),
            new Argument('test2', ['required' => true, 'castTo' => 'integer']),
            new Argument('test3', ['longPrefix' => 'test3', 'castTo' => 'int']),
            new Argument('test4', ['prefix' => 'test4', 'noValue' => true])
        );

        $this->expectOutputString(
            "Required arguments:\n\ttest (type: string)\n\ttest2 (type: integer)"
                . "\n\Optional arguments:\n\ttest3 --test3=test3 (type: integer)\n\ttest4 -test4"
        );

        $oArguments->printArguments();
    }
}
