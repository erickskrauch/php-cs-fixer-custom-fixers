<?php
declare(strict_types=1);

namespace Analyzer;

use ErickSkrauch\PhpCsFixer\Analyzer\ClassNameAnalyzer;
use LogicException;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ErickSkrauch\PhpCsFixer\Analyzer\ClassNameAnalyzer
 */
final class ClassNameAnalyzerTest extends TestCase {

    private ClassNameAnalyzer $analyzer;

    protected function setUp(): void {
        parent::setUp();
        $this->analyzer = new ClassNameAnalyzer();
    }

    /**
     * @dataProvider provideValidCases
     */
    public function testValid(string $code, int $index, string $expectedClassName): void {
        $this->assertSame($expectedClassName, $this->analyzer->getFqn(Tokens::fromCode($code), $index));
    }

    /**
     * @return iterable<array{0: string, 1: positive-int, 2: string}>
     */
    public function provideValidCases(): iterable {
        yield 'of new simple' => ['<?php $a = new DateTime();', 7, '\DateTime'];
        yield 'of new with fqn' => ['<?php $a = new \A\B\ClassName();', 7, '\A\B\ClassName'];
        yield 'of new in namespace' => [
            '<?php
            namespace A\B;
            $a = new ClassName();',
            15,
            '\A\B\ClassName',
        ];
        yield 'of new in partial import' => [
            '<?php
            namespace A;
            use A\B;

            $a = new B\C\ClassName();',
            20,
            '\A\B\C\ClassName',
        ];
        yield 'of extends simple' => ['<?php class A extends DateTime {}', 7, '\DateTime'];
        yield 'of extends with fqn' => ['<?php class A extends \A\B\ClassName {}', 7, '\A\B\ClassName'];
        yield 'of multiple implements' => ['<?php class A implements \A\B\ClassName, \B\A\OtherClassName {}', 7, '\A\B\ClassName'];

        $multipleNamespacesCase = '<?php
        namespace A;
        
        $a = new DateTime();

        namespace B {
            $b = new DateTime();
        }

        namespace C {
            $c = new DateTime();
        }
        ';

        yield 'multiple namespaces A' => [$multipleNamespacesCase, 13, '\A\DateTime'];
        yield 'multiple namespaces B' => [$multipleNamespacesCase, 30, '\B\DateTime'];
        yield 'multiple namespaces C' => [$multipleNamespacesCase, 49, '\C\DateTime'];
    }

    public function testInvalid(): void {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No T_STRING or T_NS_SEPARATOR at given index 5, got "T_LNUMBER".');

        $this->analyzer->getFqn(Tokens::fromCode('<?php $a = 123;'), 5);
    }

}
