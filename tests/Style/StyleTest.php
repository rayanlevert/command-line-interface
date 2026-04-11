<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RayanLevert\Cli\Style;
use RayanLevert\Cli\Style\Attribute;
use RayanLevert\Cli\Style\Background;
use RayanLevert\Cli\Style\Foreground;

use const E_USER_NOTICE;

#[CoversClass(Style::class)]
class StyleTest extends \PHPUnit\Framework\TestCase
{
    protected static Style $oStyle;

    public static function setUpBeforeClass(): void
    {
        self::$oStyle = new Style();
    }

    #[Test]
    #[TestDox('Outputs string with no style applied (inline)')]
    public function inlineNoStyle(): void
    {
        $this->expectOutputString('test');

        self::$oStyle->inline('test');
    }

    #[Test]
    #[TestDox('Outputs string with only background color applied (inline)')]
    public function inlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK);
    }

    #[Test]
    #[TestDox('Outputs string with only attribute applied (inline)')]
    public function inlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m");

        self::$oStyle->inline('test', at: Attribute::OUTLINE);
    }

    #[Test]
    #[TestDox('Outputs string with only foreground color applied (inline)')]
    public function inlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::BROWN);
    }

    #[Test]
    #[TestDox('Outputs string with background and foreground colors (inline)')]
    public function inlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK, Foreground::PURPLE);
    }

    #[Test]
    #[TestDox('Outputs string with foreground color and attribute (inline)')]
    public function inlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    #[Test]
    #[TestDox('Outputs string with background color and attribute (inline)')]
    public function inlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m");

        self::$oStyle->inline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    #[Test]
    #[TestDox('Outputs string with no style applied (outline)')]
    public function outlineNoStyle(): void
    {
        $this->expectOutputString("test\n");

        self::$oStyle->outline('test');
    }

    #[Test]
    #[TestDox('Outputs string with only background color applied (outline)')]
    public function outlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK);
    }

    #[Test]
    #[TestDox('Outputs string with only attribute applied (outline)')]
    public function outlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m\n");

        self::$oStyle->outline('test', at: Attribute::OUTLINE);
    }

    #[Test]
    #[TestDox('Outputs string with only foreground color applied (outline)')]
    public function outlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::BROWN);
    }

    #[Test]
    #[TestDox('Outputs string with background and foreground colors (outline)')]
    public function outlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK, Foreground::PURPLE);
    }

    #[Test]
    #[TestDox('Outputs string with foreground color and attribute (outline)')]
    public function outlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    #[Test]
    #[TestDox('Outputs string with background color and attribute (outline)')]
    public function outlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m\n");

        self::$oStyle->outline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    #[Test]
    #[TestDox('Outputs styled error message')]
    public function error(): void
    {
        $this->expectOutputString("\e[1;31m  (◍•﹏•) An error occured\e[0m\n");

        self::$oStyle->error('An error occured');
    }

    #[Test]
    #[TestDox('Outputs styled warning message')]
    public function warning(): void
    {
        $this->expectOutputString("\e[1;33m  (◍•﹏•) A warning occured\e[0m\n");

        self::$oStyle->warning('A warning occured');
    }

    #[Test]
    #[TestDox('Outputs flank line with default character')]
    public function flankDefault(): void
    {
        $this->expectOutputString("--- Test Message ---\n");

        self::$oStyle->flank('Test Message');
    }

    #[Test]
    #[TestDox('Outputs flank line with custom character')]
    public function flankCharacter(): void
    {
        $this->expectOutputString('### Test Message ###' . "\n");

        self::$oStyle->flank('Test Message', '#');
    }

    #[Test]
    #[TestDox('Outputs flank line with length 1')]
    public function flankLength(): void
    {
        $this->expectOutputString('- Test Message -' . "\n");

        self::$oStyle->flank('Test Message', length: 1);
    }

    #[Test]
    #[TestDox('Outputs flank line with custom character and length')]
    public function flankCharacterAndLength(): void
    {
        $this->expectOutputString('// Test Message //' . "\n");

        self::$oStyle->flank('Test Message', '/', 2);
    }

    #[Test]
    #[TestDox('Outputs a title')]
    public function title(): void
    {
        $this->expectOutputString("==============\n｡◕‿◕｡ test ｡◕‿◕｡\n==============\n");

        self::$oStyle->title('test');
    }

    #[Test]
    #[TestDox('Outputs a red message')]
    public function red(): void
    {
        $this->expectOutputString("\e[1;31mred message\e[0m\n");

        self::$oStyle->red('red message');
    }

    #[Test]
    #[TestDox('Outputs a yellow message')]
    public function yellow(): void
    {
        $this->expectOutputString("\e[1;33myellow message\e[0m\n");

        self::$oStyle->yellow('yellow message');
    }

    #[Test]
    #[TestDox('Outputs a green message')]
    public function green(): void
    {
        $this->expectOutputString("\e[0;32mgreen message\e[0m\n");

        self::$oStyle->green('green message');
    }

    #[Test]
    #[TestDox('Outputs formatted exception without trace')]
    public function exceptionWithoutTrace(): void
    {
        $e = new \Exception('Exception message test');

        $this->expectOutputString(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°245)\e[0m"
                . "\n\e[1m          Exception message test\e[0m\n"
        );

        self::$oStyle->exception($e, true);
    }

    #[Test]
    #[TestDox('Outputs formatted exception with trace')]
    public function exceptionWithTrace(): void
    {
        $e = new \Exception('Exception message test');

        self::$oStyle->exception($e);

        $this->assertStringStartsWith(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°259)\e[0m"
                . "\n\e[1m          Exception message test\e[0m\n",
            $this->getActualOutputForAssertion()
        );
    }

    #[Test]
    #[TestDox('Outputs with bool true, no precede')]
    public function outlineWithBoolTrueNoPrecede(): void
    {
        $this->expectOutputString("\e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse');
    }

    #[Test]
    #[TestDox('Outputs with bool true, with precede')]
    public function outlineWithBoolTrueWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    #[Test]
    #[TestDox('Outputs with bool false, no precede')]
    public function outlineWithBoolFalseNoPrecede(): void
    {
        $this->expectOutputString("\e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse');
    }

    #[Test]
    #[TestDox('Outputs with bool false, with precede')]
    public function outlineWithBoolFalseWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    #[Test]
    #[TestDox('Outputs text with no tags')]
    public function tagNoTags(): void
    {
        $this->expectOutputString('text does not contain tags.');

        self::$oStyle->tag('text does not contain tags.');
    }

    #[Test]
    #[TestDox('Outputs mismatched tags')]
    public function tagNotTheSame(): void
    {
        $this->expectOutputString('<fgred>Wrong tag</bgred>');

        self::$oStyle->tag('<fgred>Wrong tag</bgred>');
    }

    #[Test]
    #[TestDox('Outputs when unknown tag is used')]
    public function tagNotKnown(): void
    {
        $oldError = set_error_handler(function (int $errorCode, string $errorMessage): void {
            $this->assertSame('RayanLevert\Cli\Style : tag name \'fgorange\' is incorrect', $errorMessage);
            $this->assertSame(E_USER_NOTICE, $errorCode);
        });

        self::$oStyle->tag('<fgorange>Wrong tag</fgorange>');

        $this->assertSame(2, $this->getCount(), 'A user notice has not been handled');

        set_error_handler($oldError);

        ob_clean();
    }

    #[Test]
    #[TestDox('Outputs one attribute tag (bold)')]
    public function oneAttributeTag(): void
    {
        $this->expectOutputString("\e[1mtexte en gras\e[0m");

        self::$oStyle->tag('<b>texte en gras</b>');
    }

    #[Test]
    #[TestDox('Outputs one background color tag')]
    public function oneBackgroundTag(): void
    {
        $this->expectOutputString("\e[41mbackground en rouge\e[0m");

        self::$oStyle->tag('<bgred>background en rouge</bgred>');
    }

    #[Test]
    #[TestDox('Outputs one foreground color tag')]
    public function oneForegroundTag(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m");

        self::$oStyle->tag('<fglightpurple>texte en light purple</fglightpurple>');
    }

    #[Test]
    #[TestDox('Outputs tag with anti-slash n')]
    public function tagWithAntiSlashN(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m\n");

        self::$oStyle->tag("<fglightpurple>texte en light purple</fglightpurple>\n");
    }

    #[Test]
    #[TestDox('Tag with text outside at the beginning')]
    public function tagWithTextOuterTagsBeggining(): void
    {
        $this->expectOutputString("texte sans tag. \e[1;35mtexte en light purple\e[0m");

        self::$oStyle->tag('texte sans tag. <fglightpurple>texte en light purple</fglightpurple>');
    }

    #[Test]
    #[TestDox('Tag with text outside at the ending')]
    public function tagWithTextOuterTagsEnding(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m. texte sans tag");

        self::$oStyle->tag('<fglightpurple>texte en light purple</fglightpurple>. texte sans tag');
    }

    #[Test]
    #[TestDox('Tag with text outside in between tags')]
    public function tagWithTextOuterTagsInBetween(): void
    {
        $this->expectOutputString(
            "\e[1;35mtexte en light purple\e[0m. texte sans tag. \e[0;31mtexte en red\e[0m"
        );

        self::$oStyle->tag(
            '<fglightpurple>texte en light purple</fglightpurple>. texte sans tag. <fgred>texte en red</fgred>'
        );
    }
}
