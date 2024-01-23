<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Fixer\Whitespace;

use ErickSkrauch\PhpCsFixer\Fixer\Whitespace\BlankLineAroundClassBodyFixer;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * @property \ErickSkrauch\PhpCsFixer\Fixer\Whitespace\BlankLineAroundClassBodyFixer $fixer
 * @covers \ErickSkrauch\PhpCsFixer\Fixer\Whitespace\BlankLineAroundClassBodyFixer
 */
final class BlankLineAroundClassBodyFixerTest extends AbstractFixerTestCase {

    /**
     * @var array<string, mixed>
     */
    private static $configurationDoNotApplyForAnonymousClasses = ['apply_to_anonymous_classes' => false];

    /**
     * @var array<string, mixed>
     */
    private static $configurationTwoEmptyLines = ['blank_lines_count' => 2];

    /**
     * @dataProvider provideFixCases
     * @phpstan-param array<string, mixed> $configuration
     */
    public function testFix(string $expected, ?string $input = null, array $configuration = null): void {
        if ($configuration !== null) {
            $this->fixer->configure($configuration);
        }

        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<array{0: string, 1?: string, 2?: array<string, mixed>}>
     */
    public function provideFixCases(): iterable {
        yield [
            '<?php
class Good
{

    public function firstMethod()
    {
        //code here
    }

}',
            '<?php
class Good
{
    public function firstMethod()
    {
        //code here
    }
}',
        ];

        yield [
            '<?php
class Good
{

    /**
     * Also blank line before DocBlock
     */
    public function firstMethod()
    {
        //code here
    }

}',
            '<?php
class Good
{
    /**
     * Also blank line before DocBlock
     */
    public function firstMethod()
    {
        //code here
    }
}',
        ];

        yield [
            '<?php
class Good
{

    /**
     * Too many whitespaces
     */
    public function firstMethod()
    {
        //code here
    }

}',
            '<?php
class Good
{


    /**
     * Too many whitespaces
     */
    public function firstMethod()
    {
        //code here
    }



}',
        ];

        yield [
            '<?php
interface Good
{

    /**
     * Also blank line before DocBlock
     */
    public function firstMethod();

}',
            '<?php
interface Good
{
    /**
     * Also blank line before DocBlock
     */
    public function firstMethod();
}',
        ];

        yield [
            '<?php
trait Good
{

    /**
     * Also no blank line before DocBlock
     */
    public function firstMethod() {}

}',
            '<?php
trait Good
{
    /**
     * Also no blank line before DocBlock
     */
    public function firstMethod() {}
}',
        ];

        yield [
            '<?php
class Good
{
    use Foo\bar;

    public function firstMethod()
    {
        //code here
    }

}',
            '<?php
class Good
{
    use Foo\bar;

    public function firstMethod()
    {
        //code here
    }
}',
        ];

        yield [
            '<?php
class Good
{
    use Foo\bar;
    use Foo\baz;

    public function firstMethod()
    {
        //code here
    }

}',
            '<?php
class Good
{
    use Foo\bar;
    use Foo\baz;

    public function firstMethod()
    {
        //code here
    }
}',
        ];

        yield [
            '<?php
class Good
{
    use Foo, Bar {
        Bar::smallTalk insteadof A;
        Foo::bigTalk insteadof B;
    }

    public function firstMethod()
    {
        //code here
    }

}',
            '<?php
class Good
{
    use Foo, Bar {
        Bar::smallTalk insteadof A;
        Foo::bigTalk insteadof B;
    }

    public function firstMethod()
    {
        //code here
    }
}',
        ];

        yield [
            '<?php
class Good
{


    public function firstMethod()
    {
        //code here
    }


}',
            '<?php
class Good
{
    public function firstMethod()
    {
        //code here
    }
}',
            self::$configurationTwoEmptyLines,
        ];

        // check if some fancy whitespaces aren't modified
        yield [
            '<?php
class Good
{public



    function firstMethod()
    {
        //code here
    }

}',
        ];

        yield [
            '<?php
$class = new class extends \Foo {

    public $field;

    public function firstMethod() {}

};',
            '<?php
$class = new class extends \Foo {
    public $field;

    public function firstMethod() {}
};',
        ];

        yield [
            '<?php
$class = new class extends \Foo {
    public $field;

    public function firstMethod() {}
};',
            '<?php
$class = new class extends \Foo {

    public $field;

    public function firstMethod() {}

};',
            self::$configurationDoNotApplyForAnonymousClasses,
        ];
    }

    /**
     * @dataProvider provideMessyWhitespacesCases
     */
    public function testMessyWhitespaces(string $expected, ?string $input = null): void {
        $fixer = $this->fixer;
        $fixer->setWhitespacesConfig(new WhitespacesFixerConfig("\t", "\r\n"));

        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<array{string, string}>
     */
    public function provideMessyWhitespacesCases(): iterable {
        yield [
            "<?php\nclass Foo\n{\r\n\r\n    public function bar() {}\r\n\r\n}",
            "<?php\nclass Foo\n{\n    public function bar() {}\n}",
        ];

        yield [
            "<?php\nclass Foo\n{\r\n\r\n    public function bar() {}\r\n\r\n}",
            "<?php\nclass Foo\n{\r\n\r\n\n\n    public function bar() {}\n\n\n\n}",
        ];
    }

    protected function createFixer(): AbstractFixer {
        return new BlankLineAroundClassBodyFixer();
    }

}
