<?php

namespace RayanLevert\Cli\Tests\Arguments;

use RayanLevert\Cli\Arguments;
use RayanLevert\Cli\Arguments\Argument;
use RayanLevert\Cli\Arguments\Exception;
use RayanLevert\Cli\Arguments\ParseException;

class ArgumentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test Test du ->count() avec un nombre différent d'argument au __construct()/->set()
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

        $this->expectExceptionMessage('L\'argument test n\'existe pas dans la collection');

        $oArguments->get('test');
    }

    /**
     * @test Test la présence d'un ou plusieurs arguments required et l'ordre
     */
    public function testRequiredArguments(): void
    {
        $oArguments = new Arguments(new Argument('test1', ['required' => true]));
        $oArguments->parse('testValeur1');

        $this->assertSame('testValeur1', $oArguments->get('test1'));

        // 2ème argument required alors que le premier non => exception
        try {
            $oArguments = new Arguments(new Argument('test1'), new Argument('test2', ['required' => true]));

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Argument test2 required succède d\'un argument non required', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // 3ème argument required alors que le deuxième non => exception
        try {
            $oArguments = new Arguments(
                new Argument('test1', ['required' => true]),
                new Argument('test2'),
                new Argument('test3', ['required' => true])
            );

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Argument test3 required succède d\'un argument non required', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // 3ème argument required mais le 2ème est un longPrefix => correct
        $oArguments = new Arguments(
            new Argument('test1', ['required' => true]),
            new Argument('test2', ['longPrefix' => 'test2']),
            new Argument('test3', ['required' => true])
        );

        $oArguments->parse('testValue1', '--test2=test2', 'testValue3');

        $this->assertSame('testValue1', $oArguments->get('test1'));
        $this->assertSame('testValue3', $oArguments->get('test3'));
        $this->assertSame('test2', $oArguments->get('test2'));

        // Deux arguments prefixés et troisième required => correct
        $oArguments = new Arguments(
            new Argument('test1', ['longPrefix' => 'test1']),
            new Argument('test2', ['longPrefix' => 'test2']),
            new Argument('test3', ['required' => true])
        );

        // On parse uniquement les prefixés => exception
        try {
            $oArguments->parse('--test1=t', '--test2=a');

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Argument test3 is required', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }


        // On parse un préfixé et un valeur => correct
        $oArguments->parse('--test1=t1', 'testValue3');

        $this->assertSame('t1', $oArguments->get('test1'));
        $this->assertSame('testValue3', $oArguments->get('test3'));

        // 2 arguments required
        $oArguments = new Arguments(
            new Argument('test1', ['required' => true]),
            new Argument('test2', ['required' => true])
        );

        // Avec le setter
        try {
            $oArguments->set(new Argument('test1'));

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Argument test2 required succède d\'un argument non required', $e->getMessage());
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

        // Deuxième argument useless, argument test2 required prend la valeur du premier parsé
        $oArguments->parse('testValeur', 'testValeur2');

        $this->assertSame('testValeur', $oArguments->get('test2'));
        $this->assertNull($oArguments->get('test1'));

        // Deux argument parsés d'un même prefix => le dernier est set
        $oArguments->parse('test', '-t=test1', 'test3', '-t=test2');

        $this->assertSame('test', $oArguments->get('test2'));
        $this->assertSame('test2', $oArguments->get('test1'));
    }

    /**
     * @test Test ->parse() sans argument
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
     * @test Test ->parse() où la collection a un seul argument
     */
    public function testParseOneArgument(): void
    {
        // Un argument non required
        $oArguments = new Arguments(new Argument('test'));

        // Ne throw pas d'exception
        $argumentValue = $oArguments->get('test');
        $this->assertNull($argumentValue);

        // premier argument qui set la valeur, on retrouve la valeur testValue
        $oArguments->parse('testValue');
        $this->assertSame('testValue', $oArguments->get('test'));

        // On re parse un argument déjà set => le premier est set
        $oArguments->parse('premiereValeur');
        $this->assertSame('testValue', $oArguments->get('test'));

        // Un argument required
        $oArguments = new Arguments($oArgument = new Argument('test', ['required' => true]));

        $this->assertTrue($oArgument->isRequired());
        $this->assertFalse($oArgument->hasBeenHandled());
        $this->assertNull($oArguments->get('test'));

        // on parse aucun argument => throw exception d'un argument required
        try {
            $oArguments->parse();

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Argument test is required', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }

        // un argument non required avec une defaultValue différent de null
        $oArguments->set(new Argument('test', ['castTo' => 'int', 'defaultValue' => 25]));
        $oArguments->parse();

        $this->assertSame(25, $oArguments->get('test'));

        // Un argument required avec plusieurs parsés => prend le premier
        $oArguments = new Arguments($oArgument = new Argument('test1', ['required' => true]));

        $oArguments->parse('test', 'test2');
        $this->assertSame('test', $oArguments->get('test1'));

        // Un argument non required avec plusieurs parsés => prend le premier
        $oArguments = new Arguments($oArgument = new Argument('test1'));

        $oArguments->parse('test', 'test2');
        $this->assertSame('test', $oArguments->get('test1'));
    }

    /**
     * @test Test ->parse() où la collection a deux arguments
     */
    public function testParseTwoArguments(): void
    {
        $oArguments = new Arguments(new Argument('test1'), new Argument('test2'));

        // Sans defaultValue
        $oArguments->parse();

        $this->assertNull($oArguments->get('test1'));
        $this->assertNull($oArguments->get('test2'));

        $oArguments->set(new Argument('test1', ['castTo' => 'int', 'defaultValue' => 1]));
        $oArguments->set(new Argument('test2', ['castTo' => 'float', 'defaultValue' => 2.5]));

        // Avec defaultValue
        $oArguments->parse();

        $this->assertSame(1, $oArguments->get('test1'));
        $this->assertSame(2.5, $oArguments->get('test2'));

        // 1 seul required
        $oArguments = new Arguments(new Argument('test1', ['required' => true]), new Argument('test2'));
        $oArguments->parse('test', 'testValue2');

        $this->assertSame('testValue2', $oArguments->get('test2'));

        // 2 arguments parsés => correct
        $oArguments->parse('testValue1', 'testValue2');

        $this->assertSame('testValue1', $oArguments->get('test1'));
        $this->assertSame('testValue2', $oArguments->get('test2'));
    }

    /**
     * @test Test ->parse() avec deux arguments set en premier qui sont prefix et un 3ème et 4ème non requis
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
     * @test ->parse() avec un argument préfixé mais non dans la collection -> handled
     */
    public function testArgPrefixNotInCollection(): void
    {
        $oArguments = new Arguments(new Argument('test'));
        $oArguments->parse('-value');

        $this->assertSame('-value', $oArguments->get('test'));
    }

    /**
     * @test ->parse() avec un argument long préfixé mais non dans la collection
     */
    public function testArgLongPrefixNotInCollection(): void
    {
        $oArguments = new Arguments(new Argument('test'));
        $oArguments->parse('--test1');

        $this->assertSame('--test1', $oArguments->get('test'));
    }

    /**
     * @test ->parse() avec plusieurs arguments préfixés mais non dans la collection
     */
    public function testMultiplePrefixNotInCollection(): void
    {
        $oArguments = new Arguments(new Argument('test'), new Argument('test2'), new Argument('test3'));
        $oArguments->parse('test', '-test', '--test');

        $this->assertSame('test', $oArguments->get('test'));
        $this->assertSame('-test', $oArguments->get('test2'));
        $this->assertSame('--test', $oArguments->get('test3'));
    }

    /**
     * @test ->parse() avec des arguments prefixés et dans la collection sont préfixés -> non handled
     */
    public function testPrefixArgsInCollection(): void
    {
        $oArguments = new Arguments(
            new Argument('test', ['prefix' => 'test']),
            new Argument('test2', ['longPrefix' => 'longtest', 'defaultValue' => 'default'])
        );

        $oArguments->parse('-value1', '-value2');
        $this->assertNull($oArguments->get('test'));
        $this->assertSame('default', $oArguments->get('test2'));
    }

    /**
     * @test Test des arguments parsés qui ont des prefix et longPrefix (- et --, avec valeur ou non)
     */
    public function testParseShortAndLongPrefix(): void
    {
        foreach (['prefix' => '-t', 'longPrefix' => '--t'] as $optionName => $optionValue) {
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));

            //  Aucun argument parsed => defaultValue
            $oArguments->parse();
            $this->assertSame(null, $oArguments->get('test'));

            // On parse des arguments sans prefix => defaultValue
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse('test', 'ae');
            $this->assertSame(null, $oArguments->get('test'));

            // On parse des arguments avec un prefix inverse de l'iterator => defaultValue
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse($optionName === 'prefix' ? '--t' : '-t' . '=value');
            $this->assertSame(null, $oArguments->get('test'));

            // On parse des arguments avec un prefix sans value d'un arg avec value => throw exception
            try {
                $oArguments->parse($optionValue);

                $this->fail('exception expected');
            } catch (\Exception $e) {
                $this->assertSame(
                    sprintf(
                        'Argument avec valeur commençant par %s (t) n\'a pas de signe =',
                        $optionName === 'prefix' ? '-' : '--'
                    ),
                    $e->getMessage()
                );

                $this->assertInstanceOf(ParseException::class, $e);
            }

            // sans ' ni "
            $oArguments->parse($optionValue . '=testValue');
            $this->assertSame('testValue', $oArguments->get('test'));

            // avec ""
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse($optionValue . '="test valeur"');

            $this->assertSame('test valeur', $oArguments->get('test'));

            // avec ''
            $oArguments = new Arguments(new Argument('test', [$optionName => 't']));
            $oArguments->parse($optionValue . "='test valeur 2'");

            $this->assertSame('test valeur 2', $oArguments->get('test'));

            // Argument non parsé avec une defaultValue de set
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

            // Argument noValue avec un prefix inverse de l'iterator => false
            $oArguments->parse($optionName === 'prefix' ? '--t' : '-t');
            $this->assertSame(false, $oArguments->get('test'));

            // Argument noValue renseigné => true
            $oArguments = new Arguments(new Argument('test', [$optionName => 't', 'noValue' => true]));

            $oArguments->parse($optionValue);
            $this->assertSame(true, $oArguments->get('test'));
        }
    }

    /**
     * @test Test des arguments parsés qui doivent être castés en float
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
                'Argument test n\'est pas un nombre ou contient des , (doit caster en float)',
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
                'Argument test2 n\'est pas un nombre ou contient des , (doit caster en float)',
                $e->getMessage()
            );

            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /**
     * @test Test des arguments parsés qui doivent être castés en integer
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
            $this->assertSame('Argument test n\'est pas un nombre (doit caster en int)', $e->getMessage());

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
            $this->assertSame('Argument test2 n\'est pas un nombre (doit caster en int)', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /**
     * @test Test des arguments parsés dont le string est une valeur fausse ('0', '')
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
     * @test ->printArguments() sans argument
     */
    public function testPrintNoArgument(): void
    {
        $oArguments = new Arguments();

        $this->expectOutputString('');

        $oArguments->printArguments();
    }

    /**
     * @test ->printArguments() avec un required argument
     */
    public function testPrintOneRequiredArgument(): void
    {
        $oArguments = new Arguments(new Argument('test', ['required' => true]));

        $this->expectOutputString("Arguments requis:\n\ttest (type: string)");

        $oArguments->printArguments();
    }

    /**
     * @test ->printArguments() un require et un optional
     */
    public function testPrintOneRequiredOneOptionnal(): void
    {
        $oArguments = new Arguments(
            new Argument('test', ['required' => true]),
            new Argument('test2', ['defaultValue' => 12, 'castTo' => 'int'])
        );

        $this->expectOutputString(
            "Arguments requis:\n\ttest (type: string)\n\nArguments optionnels:\n\ttest2 (type: integer) (default: 12)"
        );

        $oArguments->printArguments();
    }

    /**
     * @test ->printArguments() deux required et deux optionnals
     */
    public function testPrintTwoRequiredTwoOptionnal(): void
    {
        $oArguments = new Arguments(
            new Argument('test', ['required' => true]),
            new Argument('test2', ['required' => true, 'castTo' => 'integer']),
            new Argument('test3', ['longPrefix' => 'test3', 'castTo' => 'int']),
            new Argument('test4', ['prefix' => 'test4', 'noValue' => true])
        );

        $this->expectOutputString(
            "Arguments requis:\n\ttest (type: string)\n\ttest2 (type: integer)"
                . "\n\nArguments optionnels:\n\ttest3 --test3=test3 (type: integer)\n\ttest4 -test4"
        );

        $oArguments->printArguments();
    }
}
