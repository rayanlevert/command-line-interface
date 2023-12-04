<?php

namespace RayanLevert\Cli\Tests\Style;

use RayanLevert\Cli\Style\Attribute;
use RayanLevert\Cli\Style\Background;
use RayanLevert\Cli\Style\Foreground;

class StyleTest extends \PHPUnit\Framework\TestCase
{
    protected static \RayanLevert\Cli\Style $oStyle;

    public static function setUpBeforeClass(): void
    {
        self::$oStyle = new \RayanLevert\Cli\Style();
    }

    /** @test */
    public function testInlineNoStyle(): void
    {
        $this->expectOutputString('test');

        self::$oStyle->inline('test');
    }

    /** @test */
    public function testInlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK);
    }

    /** @test */
    public function testInlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m");

        self::$oStyle->inline('test', at: Attribute::OUTLINE);
    }

    /** @test */
    public function testInlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::BROWN);
    }

    /** @test */
    public function testInlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK, Foreground::PURPLE);
    }

    /** @test */
    public function testInlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    /** @test */
    public function testInlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m");

        self::$oStyle->inline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    /** @test */
    public function testOutlineNoStyle(): void
    {
        $this->expectOutputString("test\n");

        self::$oStyle->outline('test');
    }

    /** @test */
    public function testOutlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK);
    }

    /** @test */
    public function testOutlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m\n");

        self::$oStyle->outline('test', at: Attribute::OUTLINE);
    }

    /** @test */
    public function testOutlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::BROWN);
    }

    /** @test */
    public function testOutlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK, Foreground::PURPLE);
    }

    /** @test */
    public function testOutlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    /** @test */
    public function testOutlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m\n");

        self::$oStyle->outline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    /** @test */
    public function testError(): void
    {
        $this->expectOutputString("\e[1;31m  (◍•﹏•) Une erreur est survenue\e[0m\n");

        self::$oStyle->error('Une erreur est survenue');
    }

    /** @test */
    public function testWarning(): void
    {
        $this->expectOutputString("\e[1;33m  (◍•﹏•) Un warning est survenu\e[0m\n");

        self::$oStyle->warning('Un warning est survenu');
    }

    /** @test */
    public function testFlankDefault(): void
    {
        $this->expectOutputString("--- Test Message ---\n");

        self::$oStyle->flank('Test Message');
    }

    /** @test */
    public function testFlankCharacter(): void
    {
        $this->expectOutputString('### Test Message ###' . "\n");

        self::$oStyle->flank('Test Message', '#');
    }

    /** @test */
    public function testFlankLength(): void
    {
        $this->expectOutputString('- Test Message -' . "\n");

        self::$oStyle->flank('Test Message', length: 1);
    }

    /** @test */
    public function testFlankCharacterAndLength(): void
    {
        $this->expectOutputString('// Test Message //' . "\n");

        self::$oStyle->flank('Test Message', '/', 2);
    }

    /** @test */
    public function testTitle(): void
    {
        $this->expectOutputString("==============\n｡◕‿◕｡ test ｡◕‿◕｡\n==============\n");

        self::$oStyle->title('test');
    }

    /** @test */
    public function testTermine(): void
    {
        $this->expectOutputString("\n｡◕‿◕｡ Terminé ｡◕‿◕｡\n");

        self::$oStyle->termine();
    }

    /** @test */
    public function testFlankStyle(): void
    {
        $this->expectOutputString('｡◕‿◕｡ Test Message ｡◕‿◕｡' . "\n");

        self::$oStyle->flankStyle('Test Message');
    }

    /** @test */
    public function testRed(): void
    {
        $this->expectOutputString("\e[1;31mmessage rouge\e[0m\n");

        self::$oStyle->red('message rouge');
    }

    /** @test */
    public function testYellow(): void
    {
        $this->expectOutputString("\e[1;33mmessage jaune\e[0m\n");

        self::$oStyle->yellow('message jaune');
    }

    /** @test */
    public function testGreen(): void
    {
        $this->expectOutputString("\e[0;32mmessage vert\e[0m\n");

        self::$oStyle->green('message vert');
    }

    /** @test */
    public function testExceptionWithoutTrace(): void
    {
        $e = new \Exception('Test message de l\'exception');

        $this->expectOutputString(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°229)\e[0m"
                . "\n\e[1m          Test message de l'exception\e[0m\n"
        );

        self::$oStyle->exception($e, true);
    }

    /** @test */
    public function testExceptionWithTrace(): void
    {
        $e = new \Exception('Test message de l\'exception');

        self::$oStyle->exception($e);

        $output = $this->getActualOutputForAssertion();

        $this->assertStringStartsWith(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°242)\e[0m"
                . "\n\e[1m          Test message de l'exception\e[0m\n",
            $output
        );
    }

    /** @test */
    public function testOutlineWithBoolTrueNoPrecede(): void
    {
        $this->expectOutputString("\e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse');
    }

    /** @test */
    public function testOutlineWithBoolTrueWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    /** @test */
    public function testOutlineWithBoolFalseNoPrecede(): void
    {
        $this->expectOutputString("\e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse');
    }

    /** @test */
    public function testOutlineWithBoolFalseWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    public function testTagNoTags(): void
    {
        $this->expectOutputString('ce texte ne contient pas de tags.');

        self::$oStyle->tag('ce texte ne contient pas de tags.');
    }

    public function testTagNotTheSame(): void
    {
        $this->expectOutputString('<fgred>Je me suis trompé de tag</bgred>');

        self::$oStyle->tag('<fgred>Je me suis trompé de tag</bgred>');
    }

    public function testTagNotKnown(): void
    {
        try {
            self::$oStyle->tag('<fgorange>Je me suis trompé de tag</fgorange>');
        } catch (\PHPUnit\Framework\Error\Notice $e) {
            $this->assertSame('RayanLevert\Cli\Style : nom du tag \'fgorange\' est incorrect', $e->getMessage());

            return;
        }

        $this->fail('Une notice aurait du être levée pour un tag non connu');
    }

    public function testOneAttributeTag(): void
    {
        $this->expectOutputString("\e[1mtexte en gras\e[0m");

        self::$oStyle->tag('<b>texte en gras</b>');
    }

    public function testOneBackgroundTag(): void
    {
        $this->expectOutputString("\e[41mbackground en rouge\e[0m");

        self::$oStyle->tag('<bgred>background en rouge</bgred>');
    }

    public function testOneForegroundTag(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m");

        self::$oStyle->tag('<fglightpurple>texte en light purple</fglightpurple>');
    }

    public function testTagWithAntiSlashN(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m\n");

        self::$oStyle->tag("<fglightpurple>texte en light purple</fglightpurple>\n");
    }

    public function testTagWithTextOuterTagsBeggining(): void
    {
        $this->expectOutputString("texte sans tag. \e[1;35mtexte en light purple\e[0m");

        self::$oStyle->tag("texte sans tag. <fglightpurple>texte en light purple</fglightpurple>");
    }

    public function testTagWithTextOuterTagsEnding(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m. texte sans tag");

        self::$oStyle->tag("<fglightpurple>texte en light purple</fglightpurple>. texte sans tag");
    }

    public function testTagWithTextOuterTagsInBetween(): void
    {
        $this->expectOutputString(
            "\e[1;35mtexte en light purple\e[0m. texte sans tag. \e[0;31mtexte en red\e[0m"
        );

        self::$oStyle->tag(
            "<fglightpurple>texte en light purple</fglightpurple>. texte sans tag. <fgred>texte en red</fgred>"
        );
    }
}
