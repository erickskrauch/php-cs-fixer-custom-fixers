<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation;

use ErickSkrauch\PhpCsFixer\Fixer\ClassNotation\OrderedOverridesFixer;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @covers \ErickSkrauch\PhpCsFixer\Fixer\ClassNotation\OrderedOverridesFixer
 */
final class OrderedOverridesFixerTest extends AbstractFixerTestCase {

    /**
     * @dataProvider provideTestCases
     */
    public function test(string $expected, ?string $input = null): void {
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<array{0: string, 1?: string}>
     */
    public function provideTestCases(): iterable {
        yield 'no extends, no implements' => [
            '<?php
            class A {
                public function foo(): void {}
                public function bar(): void {}
            }
            ',
        ];

        yield 'implements empty interface' => [
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\EmptyInterface {
                public function foo(): void {}
            }
            ',
        ];

        yield 'single interface implementation' => [
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function foo(): void {}
                public function bar(): void {}
            }
            ',
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function bar(): void {}
                public function foo(): void {}
            }
            ',
        ];

        yield 'multiple interfaces implementation' => [
            '<?php
            use ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

            class A implements _data\InterfaceA, _data\InterfaceB {
                public function foo(): void {}
                public function bar(): void {}
                public function baz(): void {}
                public function qux(): void {}
            }
            ',
            '<?php
            use ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

            class A implements _data\InterfaceA, _data\InterfaceB {
                public function baz(): void {}
                public function bar(): void {}
                public function qux(): void {}
                public function foo(): void {}
            }
            ',
        ];

        yield 'abstract class' => [
            '<?php
            use ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

            abstract class A implements _data\InterfaceA, _data\InterfaceB {
                public function bar(): void {}
                abstract function ownMethod(): void;
                public function baz(): void {}
            }
            ',
            '<?php
            use ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

            abstract class A implements _data\InterfaceA, _data\InterfaceB {
                public function baz(): void {}
                abstract function ownMethod(): void;
                public function bar(): void {}
            }
            ',
        ];

        yield 'interface extension' => [
            '<?php
            interface ExtendedA extends \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function baz(): void;
            }
            ',
        ];

        yield 'non-interface methods' => [
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function foo(): void {}
                public function nonInterface(): void {}
                public function bar(): void {}
            }
            ',
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function bar(): void {}
                public function nonInterface(): void {}
                public function foo(): void {}
            }
            ',
        ];

        yield 'extend abstract class' => [
            '<?php
            class A extends \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\AbstractA {
                public function foo(): void {}
                public function nonInterface(): void {}
                public function bar(): void {}
            }
            ',
            '<?php
            class A extends \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\AbstractA {
                public function bar(): void {}
                public function nonInterface(): void {}
                public function foo(): void {}
            }
            ',
        ];

        yield 'interface with multiple extends' => [
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceAandB {
                public function foo(): void {}
                public function nonInterface(): void {}
                public function bar(): void {}
                public function baz() : void{}
                public function qux() : void{}
                public function quux(): void{}
            }
            ',
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceAandB {
                public function bar(): void {}
                public function nonInterface(): void {}
                public function qux() : void{}
                public function foo(): void {}
                public function quux(): void{}
                public function baz() : void{}
            }
            ',
        ];

        yield 'mix of extends and interface' => [
            '<?php
            use ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

            class A extends _data\AbstractA implements _data\InterfaceB {
                public function foo(): void {}
                public function nonInterface(): void {}
                public function baz() : void{}
                public function qux() : void{}
            }
            ',
            '<?php
            use ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

            class A extends _data\AbstractA implements _data\InterfaceB {
                public function qux() : void{}
                public function nonInterface(): void {}
                public function foo(): void {}
                public function baz() : void{}
            }
            ',
        ];

        yield 'function in implementation' => [
            '<?php
            class A extends \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\AbstractA {
                public function bar(): void {
                    $a = function() {
                        // body
                    };
                }
            }
            ',
        ];

        yield 'anonymous class' => [
            '<?php
            $a = new class implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function foo(): void {}
                public function bar(): void {}
            };
            ',
            '<?php
            $a = new class implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function bar(): void {}
                public function foo(): void {}
            };
            ',
        ];

        yield 'extends unknown class' => [
            '<?php
            class A extends UnknownClass {
                public function baz() : void{}
                public function foo(): void {}
            }
            ',
        ];

        yield 'mix of other class elements' => [
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function foo(): void {}
                private string $a;
                public function nonInterface(): void {}
                public const B = 321;
                public function bar(): void {}
                use SomeTrait;
                public function qux(): void {}
            }
            ',
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceA {
                public function bar(): void {}
                private string $a;
                public function nonInterface(): void {}
                public const B = 321;
                public function foo(): void {}
                use SomeTrait;
                public function qux(): void {}
            }
            ',
        ];

        yield 'use the order of the deepest parent ' => [
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceAReverseOrder {
                public function foo(): void {}
                public function bar(): void {}
            }
            ',
            '<?php
            class A implements \ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data\InterfaceAReverseOrder {
                public function bar(): void {}
                public function foo(): void {}
            }
            ',
        ];
    }

    protected function createFixer(): AbstractFixer {
        return new OrderedOverridesFixer();
    }

}
