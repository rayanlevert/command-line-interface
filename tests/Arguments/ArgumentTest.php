<?php

namespace RayanLevert\Cli\Tests\Arguments;

use RayanLevert\Cli\Arguments\Argument;
use RayanLevert\Cli\Arguments\Exception;
use RayanLevert\Cli\Arguments\ParseException;

class ArgumentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test Test le constructor et les throw d'exception des options incompatibles
     */
    public function testConstructIncompatiblesOptions(): void
    {
        try {
            new Argument('testArgument', [
                'required'      => true,
                'defaultValue'  => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertEquals('Un argument noValue|required ne peut avoir une valeur par défaut', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            new Argument('testArgument', [
                'noValue'      => true,
                'defaultValue' => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertEquals('Un argument noValue|required ne peut avoir une valeur par défaut', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            new Argument('testArgument', [
                'required' => true,
                'prefix'   => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertEquals('Un argument avec un prefix (option) ne peut être required', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            new Argument('testArgument', [
                'required'   => true,
                'longPrefix' => 'test'
            ]);

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertEquals('Un argument avec un prefix (option) ne peut être required', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /**
     * @test Test chaque option du constructor et des getters
     */
    public function testConstructGetters(): void
    {
        // sans option
        $oArgument = new Argument('test');

        $this->assertEquals('test', $oArgument->getName());
        $this->assertEquals('', $oArgument->getDescription());
        $this->assertNull($oArgument->getDefaultValue());
        $this->assertFalse($oArgument->isRequired());
        $this->assertFalse($oArgument->hasNoValue());
        $this->assertEquals('string', $oArgument->getCastTo());
        $this->assertEquals('', $oArgument->getPrefix());
        $this->assertEquals('', $oArgument->getLongPrefix());
        $this->assertFalse($oArgument->hasBeenHandled());

        $oArgument = new Argument('test', ['description' => 'testDescription']);
        $this->assertEquals('testDescription', $oArgument->getDescription());

        $oArgument = new Argument('test', ['required' => true]);
        $this->assertTrue($oArgument->isRequired());

        $oArgument = new Argument('test', ['noValue' => true]);
        $this->assertTrue($oArgument->hasNoValue());

        $oArgument = new Argument('test', ['prefix' => 't']);
        $this->assertEquals('t', $oArgument->getPrefix());

        $oArgument = new Argument('test', ['longPrefix' => 'test']);
        $this->assertEquals('test', $oArgument->getLongPrefix());

        // castTo to integer
        $oArgument = new Argument('test', ['castTo' => 'int']);
        $this->assertEquals('integer', $oArgument->getCastTo());

        $oArgument = new Argument('test', ['castTo' => 'integer']);
        $this->assertEquals('integer', $oArgument->getCastTo());

        // castTo to double
        $oArgument = new Argument('test', ['castTo' => 'float']);
        $this->assertEquals('double', $oArgument->getCastTo());

        $oArgument = new Argument('test', ['castTo' => 'double']);
        $this->assertEquals('double', $oArgument->getCastTo());

        // castTo to string
        $oArgument = new Argument('test', ['castTo' => 'string']);
        $this->assertEquals('string', $oArgument->getCastTo());

        // castTo to boolean => throw d'exception
        try {
            $oArgument = new Argument('test', ['castTo' => 'bool']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('castTo ne peut être bool, utiliser l\'option noValue', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            $oArgument = new Argument('test', ['castTo' => 'boolean']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('castTo ne peut être bool, utiliser l\'option noValue', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /**
     * @test Test l'option defaultValue
     */
    public function testDefaultValue(): void
    {
        // integer
        $oArgument = new Argument('test', ['castTo' => 'int', 'defaultValue' => 10]);
        $this->assertIsInt($oArgument->getDefaultValue());
        $this->assertEquals(10, $oArgument->getDefaultValue());

        // float
        $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 5.5]);
        $this->assertIsFloat($oArgument->getDefaultValue());
        $this->assertEquals(5.5, $oArgument->getDefaultValue());

        // string
        $oArgument = new Argument('test', ['defaultValue' => 'defaultValue']);
        $this->assertIsString($oArgument->getDefaultValue());
        $this->assertEquals('defaultValue', $oArgument->getDefaultValue());

        // pas de valeur set = NULL
        $oArgument = new Argument('test');
        $this->assertNull($oArgument->getDefaultValue());

        // on récupère tous les types PHP incorrects = throw une exception
        foreach ([true, false, [], new \stdClass(), fopen(__FILE__, 'r')] as $incorrectValue) {
            try {
                $oArgument = new Argument('test', ['defaultValue' => $incorrectValue]);

                $this->fail('expected exception pour la valeur ' . var_export($incorrectValue, true));
            } catch (\Exception $e) {
                $this->assertEquals('La valeur par défaut doit être un float, int ou string', $e->getMessage());
                $this->assertInstanceOf(Exception::class, $e);
            }
        }

        // castTo string et defaultValue string OK
        $oArgument = new Argument('test', ['defaultValue' => 'test']);

        try {
            // castTo string et defaultValue integer => exception
            $oArgument = new Argument('test', ['defaultValue' => 12]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('La valeur par défaut n\'est pas du même type que castTo (string)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            // castTo string et defaultValue float => exception
            $oArgument = new Argument('test', ['defaultValue' => 45.5]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('La valeur par défaut n\'est pas du même type que castTo (string)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // castTo integer et defaultValue integer => OK
        $oArgument = new Argument('test', ['castTo' => 'integer', 'defaultValue' => 12]);

        try {
            // castTo integer et defaultValue string => exception
            $oArgument = new Argument('test', ['castTo' => 'integer', 'defaultValue' => '12']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('La valeur par défaut n\'est pas du même type que castTo (integer)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            // castTo integer et defaultValue float => exception
            $oArgument = new Argument('test', ['castTo' => 'integer', 'defaultValue' => 45.5]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('La valeur par défaut n\'est pas du même type que castTo (integer)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        // castTo float et defaultValue float => OK
        $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 12.4]);

        try {
            // castTo float et defaultValue string => exception
            $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => '12']);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('La valeur par défaut n\'est pas du même type que castTo (double)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }

        try {
            // castTo integer et defaultValue integer => exception
            $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 45]);

            $this->fail('exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('La valeur par défaut n\'est pas du même type que castTo (double)', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /**
     * @test Test le construct avec un castTo incorrect
     */
    public function testCastToNotCorrectType(): void
    {
        $this->expectExceptionObject(new Exception('incorrect-type n\'est pas un type de cast correct'));

        new Argument('test', ['castTo' => 'incorrect-type']);
    }

    /**
     * @test Test la method ->setValueParsed() d'un argument string
     */
    public function testParseArgumentString(): void
    {
        // parse d'un argument string sans castTo => string
        $oArgument = new Argument('test');
        $oArgument->setValueParsed('value');

        $this->assertEquals('value', $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());
    }

    /**
     * @test Test la method ->setValueParsed() d'un argument integer
     */
    public function testParseArgumentInteger(): void
    {
        // parse d'un argument string numerique avec castTo integer => value int
        $oArgument = new Argument('test', ['castTo' => 'integer']);
        $oArgument->setValueParsed('12');

        $this->assertIsInt($oArgument->getValue());
        $this->assertEquals(12, $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());

        // parse d'un argument string non numérique avec castTo integer => throw
        $oArgument = new Argument('test', ['castTo' => 'integer']);

        try {
            $oArgument->setValueParsed('stringsansnombre');

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertEquals('Argument test n\'est pas un nombre (doit caster en int)', $e->getMessage());
            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /**
     * @test Test la method ->setValueParsed() d'un argument float
     */
    public function testParseArgumentFloat(): void
    {
        // parse d'un argument string numerique avec castTo float entier => value float
        $oArgument = new Argument('test', ['castTo' => 'float']);
        $oArgument->setValueParsed('12');

        $this->assertIsFloat($oArgument->getValue());
        $this->assertEquals(12.0, $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());

        // parse d'un argument string numerique avec castTo float => value float
        $oArgument = new Argument('test', ['castTo' => 'float']);
        $oArgument->setValueParsed('12.8');

        $this->assertIsFloat($oArgument->getValue());
        $this->assertEquals(12.8, $oArgument->getValue());
        $this->assertTrue($oArgument->hasBeenHandled());

        // parse d'un argument string numerique avec castTo float avec virgule => throw exception
        $oArgument = new Argument('test', ['castTo' => 'float']);

        try {
            $oArgument->setValueParsed('12,8');

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertEquals(
                'Argument test n\'est pas un nombre ou contient des , (doit caster en float)',
                $e->getMessage()
            );

            $this->assertInstanceOf(ParseException::class, $e);
        }

        // parse d'un argument string non numérique avec castTo integer => throw
        $oArgument = new Argument('test', ['castTo' => 'float']);

        try {
            $oArgument->setValueParsed('stringsansnombre');

            $this->fail('expected exception');
        } catch (\Exception $e) {
            $this->assertEquals(
                'Argument test n\'est pas un nombre ou contient des , (doit caster en float)',
                $e->getMessage()
            );

            $this->assertInstanceOf(ParseException::class, $e);
        }
    }

    /**
     * @test ->getInfos() sans options
     */
    public function testGetInfosOptionsEmpty(): void
    {
        $this->assertEquals('test (type: string)', (new Argument('test'))->getInfos());
    }

    /**
     * @test ->getInfos() avec des arguments required
     */
    public function testGetInfosRequired(): void
    {
        $this->assertEquals('test (type: string)', (new Argument('test'))->getInfos());

        $this->assertEquals('test (type: double)', (new Argument('test', ['castTo' => 'float']))->getInfos());
    }

    /**
     * @test ->getInfos() qu'avec des prefixes
     */
    public function testGetInfosWithPrefixes(): void
    {
        // Que le prefix
        $oArgument = new Argument('test', ['prefix' => 't']);
        $this->assertEquals('test -t=test (type: string)', $oArgument->getInfos());

        // Que le longPrefix
        $oArgument = new Argument('test', ['longPrefix' => 'longtest']);
        $this->assertEquals('test --longtest=test (type: string)', $oArgument->getInfos());

        // longPrefix et prefix
        $oArgument = new Argument('test', ['longPrefix' => 'longtest', 'prefix' => 't']);
        $this->assertEquals('test -t=test, --longtest=test (type: string)', $oArgument->getInfos());

        // Que le prefix sans valeur
        $oArgument = new Argument('test', ['prefix' => 't', 'noValue' => true]);
        $this->assertEquals('test -t', $oArgument->getInfos());

        // Que le longPrefix sans valeur
        $oArgument = new Argument('test', ['longPrefix' => 'longtest', 'noValue' => true]);
        $this->assertEquals('test --longtest', $oArgument->getInfos());

        // longPrefix et prefix sans valeur
        $oArgument = new Argument('test', ['longPrefix' => 'longtest', 'prefix' => 't', 'noValue' => true]);
        $this->assertEquals('test -t, --longtest', $oArgument->getInfos());
    }

    /**
     * @test ->getInfos() avec une description
     */
    public function testGetInfosDescription(): void
    {
        $oArgument = new Argument('test', ['description' => 'Test description']);

        $this->assertEquals("test (type: string)\n\t  Test description", $oArgument->getInfos());
    }

    /**
     * @test ->getInfos() avec une valeur par défaut
     */
    public function testGetInfosDefaultValue(): void
    {
        $oArgument = new Argument('test', ['castTo' => 'float', 'defaultValue' => 56.56]);

        $this->assertEquals('test (type: double) (default: 56.56)', $oArgument->getInfos());
    }

    /**
     * @test ->getInfos() avec un type de cast
     */
    public function testGetInfosCastTo(): void
    {
        $oArgument = new Argument('test', ['castTo' => 'float']);

        $this->assertEquals('test (type: double)', $oArgument->getInfos());
    }

    /**
     * @test ->getInfos() avec toutes les options mélangées
     */
    public function testGetInfosAllOptions(): void
    {
        // Avec prefix noValue
        $oArgument = new Argument('test', [
            'description'  => 'Test description',
            'castTo'       => 'int',
            'prefix'       => 't',
            'noValue'      => true,
            'longPrefix'   => 'longtest'
        ]);

        $this->assertEquals(
            "test -t, --longtest\n\t  Test description",
            $oArgument->getInfos()
        );

        // Avec prefix
        $oArgument = new Argument('test', [
            'description'  => 'Test description',
            'defaultValue' => 12,
            'castTo'       => 'int',
            'prefix'       => 't',
            'longPrefix'   => 'longtest'
        ]);

        $this->assertEquals(
            "test -t=test, --longtest=test (type: integer) (default: 12)\n\t  Test description",
            $oArgument->getInfos()
        );

        // Sans prefix
        $oArgument = new Argument('test', [
            'description'  => 'Test description',
            'defaultValue' => 'test',
            'castTo'       => 'string',
        ]);

        $this->assertEquals(
            "test (type: string) (default: test)\n\t  Test description",
            $oArgument->getInfos()
        );
    }
}
