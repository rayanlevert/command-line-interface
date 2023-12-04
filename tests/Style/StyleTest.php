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

    public function testInlineNoStyle(): void
    {
        $this->expectOutputString('test');

        self::$oStyle->inline('test');
    }

    public function testInlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK);
    }

    public function testInlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m");

        self::$oStyle->inline('test', at: Attribute::OUTLINE);
    }

    public function testInlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::BROWN);
    }

    public function testInlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK, Foreground::PURPLE);
    }

    public function testInlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    public function testInlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m");

        self::$oStyle->inline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    public function testOutlineNoStyle(): void
    {
        $this->expectOutputString("test\n");

        self::$oStyle->outline('test');
    }

    public function testOutlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK);
    }

    public function testOutlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m\n");

        self::$oStyle->outline('test', at: Attribute::OUTLINE);
    }

    public function testOutlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::BROWN);
    }

    public function testOutlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK, Foreground::PURPLE);
    }

    public function testOutlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    public function testOutlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m\n");

        self::$oStyle->outline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    public function testError(): void
    {
        $this->expectOutputString("\e[1;31m  (◍•﹏•) An error occured\e[0m\n");

        self::$oStyle->error('An error occured');
    }

    public function testWarning(): void
    {
        $this->expectOutputString("\e[1;33m  (◍•﹏•) A warning occured\e[0m\n");

        self::$oStyle->warning('A warning occured');
    }

    public function testFlankDefault(): void
    {
        $this->expectOutputString("--- Test Message ---\n");

        self::$oStyle->flank('Test Message');
    }

    public function testFlankCharacter(): void
    {
        $this->expectOutputString('### Test Message ###' . "\n");

        self::$oStyle->flank('Test Message', '#');
    }

    public function testFlankLength(): void
    {
        $this->expectOutputString('- Test Message -' . "\n");

        self::$oStyle->flank('Test Message', length: 1);
    }

    public function testFlankCharacterAndLength(): void
    {
        $this->expectOutputString('// Test Message //' . "\n");

        self::$oStyle->flank('Test Message', '/', 2);
    }

    public function testTitle(): void
    {
        $this->expectOutputString("==============\n｡◕‿◕｡ test ｡◕‿◕｡\n==============\n");

        self::$oStyle->title('test');
    }

    public function testRed(): void
    {
        $this->expectOutputString("\e[1;31mred message\e[0m\n");

        self::$oStyle->red('red message');
    }

    public function testYellow(): void
    {
        $this->expectOutputString("\e[1;33myellow message\e[0m\n");

        self::$oStyle->yellow('yellow message');
    }

    public function testGreen(): void
    {
        $this->expectOutputString("\e[0;32mgreen message\e[0m\n");

        self::$oStyle->green('green message');
    }

    public function testExceptionWithoutTrace(): void
    {
        $e = new \Exception('Exception message test');

        $this->expectOutputString(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°188)\e[0m"
                . "\n\e[1m          Exception message test\e[0m\n"
        );

        self::$oStyle->exception($e, true);
    }

    public function testExceptionWithTrace(): void
    {
        $e = new \Exception('Exception message test');

        self::$oStyle->exception($e);

        $this->assertStringStartsWith(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°200)\e[0m"
                . "\n\e[1m          Exception message test\e[0m\n",
            $this->getActualOutputForAssertion()
        );
    }

    public function testOutlineWithBoolTrueNoPrecede(): void
    {
        $this->expectOutputString("\e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse');
    }

    public function testOutlineWithBoolTrueWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    public function testOutlineWithBoolFalseNoPrecede(): void
    {
        $this->expectOutputString("\e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse');
    }

    public function testOutlineWithBoolFalseWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    public function testTagNoTags(): void
    {
        $this->expectOutputString('text does not contain tags.');

        self::$oStyle->tag('text does not contain tags.');
    }

    public function testTagNotTheSame(): void
    {
        $this->expectOutputString('<fgred>Wrong tag</bgred>');

        self::$oStyle->tag('<fgred>Wrong tag</bgred>');
    }

    public function testTagNotKnown(): void
    {
        $oldError = set_error_handler(function (int $errorCode, string $errorMessage) {
            $this->assertSame('RayanLevert\Cli\Style : tag name \'fgorange\' is incorrect', $errorMessage);
            $this->assertSame(E_USER_NOTICE, $errorCode);
        });

        self::$oStyle->tag('<fgorange>Wrong tag</fgorange>');

        $this->assertSame(2, $this->getCount(), 'A user notice has not been handled');

        set_error_handler($oldError);

        ob_clean();
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
