<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Fixer\LanguageConstruct;

use ErickSkrauch\PhpCsFixer\Fixer\LanguageConstruct\RemoveClassNameMethodUsagesFixer;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @covers \ErickSkrauch\PhpCsFixer\Fixer\LanguageConstruct\RemoveClassNameMethodUsagesFixer
 */
final class RemoveClassNameMethodUsagesFixerTest extends AbstractFixerTestCase {

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
            '<?php echo className();',
        ];

        yield [
            '<?php
use Foo\Bar\Baz;

$exceptionString = Baz::classname();
',
        ];

        yield [
            '<?php
use Foo\Bar\Baz;

$className = Baz::class;
',
            '<?php
use Foo\Bar\Baz;

$className = Baz::className();
',
        ];

        yield [
            '<?php
use Foo\Bar\Baz;

$exceptionString = "The class should be instance of " . Baz::class . " and nothing else";
',
            '<?php
use Foo\Bar\Baz;

$exceptionString = "The class should be instance of " . Baz::className() . " and nothing else";
',
        ];
    }

    protected function createFixer(): AbstractFixer {
        return new RemoveClassNameMethodUsagesFixer();
    }

}
