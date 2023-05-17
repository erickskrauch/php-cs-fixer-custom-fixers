<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Whitespace;

use Ely\CS\Fixer\Whitespace\MultilineIfStatementBracesFixer;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @covers \Ely\CS\Fixer\Whitespace\MultilineIfStatementBracesFixer
 */
final class MultilineIfStatementBracesFixerTest extends AbstractFixerTestCase {

    /**
     * @dataProvider provideFixCases
     */
    public function testFixOnNewLine(string $expected, ?string $input = null): void {
        $this->doTest($expected, $input);
    }

    public function provideFixCases(): iterable {
        yield 'simple' => [
            '<?php
if ($condition1
 && $condition2
) {}',
            '<?php
if ($condition1
 && $condition2) {}',
        ];

        yield 'nested' => [
            '<?php
function foo() {
    if ($condition1
     && $condition2
    ) {}
}',
            '<?php
function foo() {
    if ($condition1
     && $condition2) {}
}',
        ];
    }

    /**
     * @dataProvider provideInvertedFixCases
     */
    public function testFixOnSameLine(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            MultilineIfStatementBracesFixer::C_KEEP_ON_OWN_LINE => false,
        ]);
        $this->doTest($expected, $input);
    }

    public function provideInvertedFixCases(): iterable {
        foreach ($this->provideFixCases() as $name => $case) {
            yield $name => [$case[1], $case[0]];
        }
    }

    protected function createFixer(): AbstractFixer {
        return new MultilineIfStatementBracesFixer();
    }

}
