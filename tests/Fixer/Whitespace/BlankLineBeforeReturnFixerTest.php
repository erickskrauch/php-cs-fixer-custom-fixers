<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Fixer\Whitespace;

use ErickSkrauch\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeReturnFixer;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * Original file copied from:
 * @url https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/5c5de791ab/tests/Fixer/Whitespace/BlankLineBeforeStatementFixerTest.php
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Andreas Möller <am@localheinz.com>
 * @author SpacePossum
 *
 * @internal
 *
 * @property BlankLineBeforeReturnFixer $fixer
 *
 * @covers \ErickSkrauch\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeReturnFixer
 */
final class BlankLineBeforeReturnFixerTest extends AbstractFixerTestCase {

    /**
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null): void {
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<array{0: string, 1?: string}>
     */
    public function provideFixCases(): iterable {
        yield [
            '$a = $a;
return $a;
',
        ];
        yield [
            '<?php
$a = $a;

return $a;',
            '<?php
$a = $a; return $a;',
        ];
        yield [
            '<?php
$b = $b;

return $b;',
            '<?php
$b = $b;return $b;',
        ];
        yield [
            '<?php
$c = $c;

return $c;',
            '<?php
$c = $c;
return $c;',
        ];
        yield [
            '<?php
    $d = $d;

    return $d;',
            '<?php
    $d = $d;
    return $d;',
        ];
        yield [
            '<?php
    if (true) {
        return 1;
    }',
        ];
        yield [
            '<?php
    if (true)
        return 1;
    ',
        ];
        yield [
            '<?php
    if (true) {
        return 1;
    } else {
        return 2;
    }',
        ];
        yield [
            '<?php
    if (true)
        return 1;
    else
        return 2;
    ',
        ];
        yield [
            '<?php
    if (true) {
        return 1;
    } elseif (false) {
        return 2;
    }',
        ];
        yield [
            '<?php
    if (true)
        return 1;
    elseif (false)
        return 2;
    ',
        ];
        yield [
            '<?php
    throw new Exception("return true;");',
        ];
        yield [
            '<?php
    function foo()
    {
        // comment
        return "foo";
    }',
        ];
        yield [
            '<?php
    function foo()
    {
        // comment

        return "bar";
    }',
        ];
        yield [
            '<?php
    function foo()
    {
        // comment
        return "bar";
    }',
        ];
        yield [
            '<?php
    function foo() {
        $a = "a";
        $b = "b";

        return $a . $b;
    }',
            '<?php
    function foo() {
        $a = "a";
        $b = "b";
        return $a . $b;
    }',
        ];
        yield [
            '<?php
    function foo() {
        $b = "b";
        return $a . $b;
    }',
        ];
        yield [
            '<?php
    function foo() {
        $a = "a";

        return $a . "hello";
    }

    function bar() {
        $b = "b";
        return $b . "hello";
    }
    ',
        ];
        yield [
            '<?php
            if ($condition) {
                $a = "Interpolation {$var}.";
                return true;
            }',
        ];
        yield [
            '<?php
            if ($condition) {
                $a = "Deprecated interpolation ${var}.";
                return true;
            }',
        ];
    }

    /**
     * @dataProvider provideMessyWhitespacesCases
     */
    public function testMessyWhitespaces(string $expected, ?string $input = null): void {
        $this->fixer->setWhitespacesConfig(new WhitespacesFixerConfig("\t", "\r\n"));

        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<array{string, string}>
     */
    public function provideMessyWhitespacesCases(): iterable {
        yield [
            "<?php\r\n\$a = \$a;\r\n\r\nreturn \$a;",
            "<?php\r\n\$a = \$a; return \$a;",
        ];
        yield [
            "<?php\r\n\$b = \$b;\r\n\r\nreturn \$b;",
            "<?php\r\n\$b = \$b;return \$b;",
        ];
        yield [
            "<?php\r\n\$c = \$c;\r\n\r\nreturn \$c;",
            "<?php\r\n\$c = \$c;\r\nreturn \$c;",
        ];
    }

    protected function createFixer(): AbstractFixer {
        return new BlankLineBeforeReturnFixer();
    }

}
