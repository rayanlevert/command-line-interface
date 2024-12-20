<?php

namespace RayanLevert\Cli\Tests\Style;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Cli\Style;
use RayanLevert\Cli\Style\Attribute;
use RayanLevert\Cli\Style\Background;
use RayanLevert\Cli\Style\Foreground;

#[CoversClass(Style::class)]
class StyleTest extends \PHPUnit\Framework\TestCase
{
    protected static \RayanLevert\Cli\Style $oStyle;

    public static function setUpBeforeClass(): void
    {
        self::$oStyle = new \RayanLevert\Cli\Style();
    }

    #[Test]
    public function inlineNoStyle(): void
    {
        $this->expectOutputString('test');

        self::$oStyle->inline('test');
    }

    #[Test]
    public function inlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK);
    }

    #[Test]
    public function inlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m");

        self::$oStyle->inline('test', at: Attribute::OUTLINE);
    }

    #[Test]
    public function inlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::BROWN);
    }

    #[Test]
    public function inlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m");

        self::$oStyle->inline('test', Background::BLACK, Foreground::PURPLE);
    }

    #[Test]
    public function inlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m");

        self::$oStyle->inline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    #[Test]
    public function inlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m");

        self::$oStyle->inline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    #[Test]
    public function outlineNoStyle(): void
    {
        $this->expectOutputString("test\n");

        self::$oStyle->outline('test');
    }

    #[Test]
    public function outlineOnlyBackground(): void
    {
        $this->expectOutputString("\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK);
    }

    #[Test]
    public function outlineOnlyAttribute(): void
    {
        $this->expectOutputString("\e[6mtest\e[0m\n");

        self::$oStyle->outline('test', at: Attribute::OUTLINE);
    }

    #[Test]
    public function outlineOnlyForeground(): void
    {
        $this->expectOutputString("\e[0;33mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::BROWN);
    }

    #[Test]
    public function outlineBackgroundAndForeground(): void
    {
        $this->expectOutputString("\e[0;35m\e[40mtest\e[0m\n");

        self::$oStyle->outline('test', Background::BLACK, Foreground::PURPLE);
    }

    #[Test]
    public function outlineForegroundAndAttribute(): void
    {
        $this->expectOutputString("\e[0;35m\e[4mtest\e[0m\n");

        self::$oStyle->outline('test', fg: Foreground::PURPLE, at: Attribute::UNDERLINE);
    }

    #[Test]
    public function outlineBackgroundAndAttribute(): void
    {
        $this->expectOutputString("\e[46m\e[3mtest\e[0m\n");

        self::$oStyle->outline('test', Background::CYAN, at: Attribute::ITALIC);
    }

    #[Test]
    public function error(): void
    {
        $this->expectOutputString("\e[1;31m  (◍•﹏•) An error occured\e[0m\n");

        self::$oStyle->error('An error occured');
    }

    #[Test]
    public function warning(): void
    {
        $this->expectOutputString("\e[1;33m  (◍•﹏•) A warning occured\e[0m\n");

        self::$oStyle->warning('A warning occured');
    }

    #[Test]
    public function flankDefault(): void
    {
        $this->expectOutputString("--- Test Message ---\n");

        self::$oStyle->flank('Test Message');
    }

    #[Test]
    public function flankCharacter(): void
    {
        $this->expectOutputString('### Test Message ###' . "\n");

        self::$oStyle->flank('Test Message', '#');
    }

    #[Test]
    public function flankLength(): void
    {
        $this->expectOutputString('- Test Message -' . "\n");

        self::$oStyle->flank('Test Message', length: 1);
    }

    #[Test]
    public function flankCharacterAndLength(): void
    {
        $this->expectOutputString('// Test Message //' . "\n");

        self::$oStyle->flank('Test Message', '/', 2);
    }

    #[Test]
    public function title(): void
    {
        $this->expectOutputString("==============\n｡◕‿◕｡ test ｡◕‿◕｡\n==============\n");

        self::$oStyle->title('test');
    }

    #[Test]
    public function red(): void
    {
        $this->expectOutputString("\e[1;31mred message\e[0m\n");

        self::$oStyle->red('red message');
    }

    #[Test]
    public function yellow(): void
    {
        $this->expectOutputString("\e[1;33myellow message\e[0m\n");

        self::$oStyle->yellow('yellow message');
    }

    #[Test]
    public function green(): void
    {
        $this->expectOutputString("\e[0;32mgreen message\e[0m\n");

        self::$oStyle->green('green message');
    }

    #[Test]
    public function exceptionWithoutTrace(): void
    {
        $e = new \Exception('Exception message test');

        $this->expectOutputString(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°217)\e[0m"
                . "\n\e[1m          Exception message test\e[0m\n"
        );

        self::$oStyle->exception($e, true);
    }

    #[Test]
    public function exceptionWithTrace(): void
    {
        $e = new \Exception('Exception message test');

        self::$oStyle->exception($e);

        $this->assertStringStartsWith(
            "\n\e[1;31m  (◍•﹏•) Exception thrown in file " . $e->getFile() . " (line n°230)\e[0m"
                . "\n\e[1m          Exception message test\e[0m\n",
            $this->getActualOutputForAssertion()
        );
    }

    #[Test]
    public function outlineWithBoolTrueNoPrecede(): void
    {
        $this->expectOutputString("\e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse');
    }

    #[Test]
    public function outlineWithBoolTrueWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[0;32mifTrue\e[0m\n");

        self::$oStyle->outlineWithBool(true, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    #[Test]
    public function outlineWithBoolFalseNoPrecede(): void
    {
        $this->expectOutputString("\e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse');
    }

    #[Test]
    public function outlineWithBoolFalseWithPrecede(): void
    {
        $this->expectOutputString("To Precede \e[1;31mifFalse\e[0m\n");

        self::$oStyle->outlineWithBool(false, 'ifTrue', 'ifFalse', 'To Precede ');
    }

    #[Test]
    public function tagNoTags(): void
    {
        $this->expectOutputString('text does not contain tags.');

        self::$oStyle->tag('text does not contain tags.');
    }

    #[Test]
    public function tagNotTheSame(): void
    {
        $this->expectOutputString('<fgred>Wrong tag</bgred>');

        self::$oStyle->tag('<fgred>Wrong tag</bgred>');
    }

    #[Test]
    public function tagNotKnown(): void
    {
        set_error_handler(function (int $errorCode, string $errorMessage) {
            $this->assertSame('RayanLevert\Cli\Style : tag name \'fgorange\' is incorrect', $errorMessage);
            $this->assertSame(E_USER_NOTICE, $errorCode);
        });

        self::$oStyle->tag('<fgorange>Wrong tag</fgorange>');

        $this->assertSame(2, $this->getCount(), 'A user notice has not been handled');

        restore_error_handler();

        ob_clean();
    }

    #[Test]
    public function oneAttributeTag(): void
    {
        $this->expectOutputString("\e[1mtexte en gras\e[0m");

        self::$oStyle->tag('<b>texte en gras</b>');
    }

    #[Test]
    public function oneBackgroundTag(): void
    {
        $this->expectOutputString("\e[41mbackground en rouge\e[0m");

        self::$oStyle->tag('<bgred>background en rouge</bgred>');
    }

    #[Test]
    public function oneForegroundTag(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m");

        self::$oStyle->tag('<fglightpurple>texte en light purple</fglightpurple>');
    }

    #[Test]
    public function tagWithAntiSlashN(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m\n");

        self::$oStyle->tag("<fglightpurple>texte en light purple</fglightpurple>\n");
    }

    #[Test]
    public function tagWithTextOuterTagsBeggining(): void
    {
        $this->expectOutputString("texte sans tag. \e[1;35mtexte en light purple\e[0m");

        self::$oStyle->tag("texte sans tag. <fglightpurple>texte en light purple</fglightpurple>");
    }

    #[Test]
    public function tagWithTextOuterTagsEnding(): void
    {
        $this->expectOutputString("\e[1;35mtexte en light purple\e[0m. texte sans tag");

        self::$oStyle->tag("<fglightpurple>texte en light purple</fglightpurple>. texte sans tag");
    }

    #[Test]
    public function tagWithTextOuterTagsInBetween(): void
    {
        $this->expectOutputString(
            "\e[1;35mtexte en light purple\e[0m. texte sans tag. \e[0;31mtexte en red\e[0m"
        );

        self::$oStyle->tag(
            "<fglightpurple>texte en light purple</fglightpurple>. texte sans tag. <fgred>texte en red</fgred>"
        );
    }
}
