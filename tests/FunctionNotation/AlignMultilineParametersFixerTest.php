<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\FunctionNotation;

use ErickSkrauch\PhpCsFixer\FunctionNotation\AlignMultilineParametersFixer;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @covers \ErickSkrauch\PhpCsFixer\FunctionNotation\AlignMultilineParametersFixer
 */
final class AlignMultilineParametersFixerTest extends AbstractFixerTestCase {

    /**
     * @dataProvider provideTrueCases
     */
    public function testBothTrue(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            AlignMultilineParametersFixer::C_VARIABLES => true,
            AlignMultilineParametersFixer::C_DEFAULTS => true,
        ]);
        $this->doTest($expected, $input);
    }

    public function provideTrueCases(): iterable {
        yield 'empty function' => [
            '<?php
            function test(): void {}
            ',
        ];

        yield 'empty multiline function' => [
            '<?php
            function test(
            ): void {}
            ',
        ];

        yield 'single line function' => [
            '<?php
            function test(string $a, int $b): void {}
            ',
        ];

        yield 'single line fn' => [
            '<?php
            fn(string $a, int $b) => $b;
            ',
        ];

        yield 'function, no defaults, no nulls' => [
            '<?php
            function test(
                string $a,
                int    $b
            ): void {}
            ',
            '<?php
            function test(
                string $a,
                int $b
            ): void {}
            ',
        ];

        yield 'function, one has default, no nulls' => [
            '<?php
            function test(
                string $a,
                int    $b = 0
            ): void {}
            ',
            '<?php
            function test(
                string $a,
                int $b = 0
            ): void {}
            ',
        ];

        yield 'function, no defaults, nullable types' => [
            '<?php
            function test(
                string $a,
                ?int   $b = 0
            ): void {}
            ',
            '<?php
            function test(
                string $a,
                ?int $b = 0
            ): void {}
            ',
        ];

        yield 'function, no defaults, nullable types with space' => [
            '<?php
            function test(
                string $a,
                ?  int $b = 0
            ): void {}
            ',
        ];

        yield 'function, one has no type' => [
            '<?php
            function test(
                string $a,
                       $b
            ): void {}
            ',
            '<?php
            function test(
                string $a,
                $b
            ): void {}
            ',
        ];

        yield 'function, one has no type, but has default' => [
            '<?php
            function test(
                string $a,
                       $b = 0
            ): void {}
            ',
            '<?php
            function test(
                string $a,
                $b = 0
            ): void {}
            ',
        ];

        yield 'function, no types at all' => [
            '<?php
            function test(
                $string = "string",
                $int    = 0
            ): void {}
            ',
            '<?php
            function test(
                $string = "string",
                $int = 0
            ): void {}
            ',
        ];

        yield 'function, defaults' => [
            '<?php
            function test(
                string $string = "string",
                int    $int    = 0
            ): void {}
            ',
            '<?php
            function test(
                string $string = "string",
                int $int = 0
            ): void {}
            ',
        ];

        yield 'class method, defaults' => [
            '<?php
            class Test {
                public function foo(
                    string $string = "string",
                    int    $int    = 0
                ): void {}
            }
            ',
            '<?php
            class Test {
                public function foo(
                    string $string = "string",
                    int $int = 0
                ): void {}
            }
            ',
        ];

        yield 'fn, defaults' => [
            '<?php
            fn(
                string $string = "string",
                int    $int    = 0
            ) => $int;
            ',
            '<?php
            fn(
                string $string = "string",
                int $int = 0
            ) => $int;
            ',
        ];

        yield 'class method, union types, defaults' => [
            '<?php
            class Test {
                public function foo(
                    string          $string = "string",
                    int|string|null $int    = 0
                ): void {}
            }
            ',
            '<?php
            class Test {
                public function foo(
                    string $string = "string",
                    int|string|null $int = 0
                ): void {}
            }
            ',
        ];

        yield 'fn, union types, defaults' => [
            '<?php
            fn(
                string          $string = "string",
                int|string|null $int    = 0
            ) => $int;
            ',
            '<?php
            fn(
                string $string = "string",
                int|string|null $int = 0
            ) => $int;
            ',
        ];
    }

    /**
     * @dataProvider provideFalseCases
     */
    public function testBothFalse(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            AlignMultilineParametersFixer::C_VARIABLES => false,
            AlignMultilineParametersFixer::C_DEFAULTS => false,
        ]);
        $this->doTest($expected, $input);
    }

    public function provideFalseCases(): iterable {
        foreach ($this->provideTrueCases() as $key => $case) {
            if (isset($case[1])) {
                yield $key => [$case[1], $case[0]];
            } else {
                yield $key => $case;
            }
        }
    }

    /**
     * @dataProvider provideNullCases
     */
    public function testBothNull(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            AlignMultilineParametersFixer::C_VARIABLES => null,
            AlignMultilineParametersFixer::C_DEFAULTS => null,
        ]);
        $this->doTest($expected, $input);
    }

    public function provideNullCases(): iterable {
        foreach ($this->provideFalseCases() as $key => $case) {
            yield $key => [$case[0]];
        }
    }

    /**
     * @dataProvider provide80TrueCases
     * @requires PHP 8.0
     */
    public function test80BothTrue(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            AlignMultilineParametersFixer::C_VARIABLES => true,
            AlignMultilineParametersFixer::C_DEFAULTS => true,
        ]);
        $this->doTest($expected, $input);
    }

    public function provide80TrueCases(): iterable {
        yield 'constructor promotion, defaults' => [
            '<?php
            class Test {
                public function __construct(
                    public string  $string = "string",
                    protected bool $bool   = true
                ) {}
            }
            ',
            '<?php
            class Test {
                public function __construct(
                    public string $string = "string",
                    protected bool $bool = true
                ) {}
            }
            ',
        ];
    }

    /**
     * @dataProvider provideFalse80Cases
     * @requires PHP 8.0
     */
    public function test80BothFalse(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            AlignMultilineParametersFixer::C_VARIABLES => false,
            AlignMultilineParametersFixer::C_DEFAULTS => false,
        ]);
        $this->doTest($expected, $input);
    }

    public function provideFalse80Cases(): iterable {
        foreach ($this->provide80TrueCases() as $key => $case) {
            if (isset($case[1])) {
                yield $key => [$case[1], $case[0]];
            } else {
                yield $key => $case;
            }
        }
    }

    /**
     * @dataProvider provide81TrueCases
     * @requires PHP 8.1
     */
    public function test81BothTrue(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            AlignMultilineParametersFixer::C_VARIABLES => true,
            AlignMultilineParametersFixer::C_DEFAULTS => true,
        ]);
        $this->doTest($expected, $input);
    }

    public function provide81TrueCases(): iterable {
        yield 'constructor promotion, readonly, defaults' => [
            '<?php
            class Test {
                public function __construct(
                    public readonly string  $string = "string",
                    protected readonly bool $bool   = true
                ) {}
            }
            ',
            '<?php
            class Test {
                public function __construct(
                    public readonly string $string = "string",
                    protected readonly bool $bool = true
                ) {}
            }
            ',
        ];
        yield 'partial constructor promotion, readonly, defaults' => [
            '<?php
            class Test {
                public function __construct(
                    readonly string $string = "string",
                    int             $int    = 0,
                    protected bool  $bool   = true,
                                    $float  = 0.0,
                ) {}
            }
            ',
            '<?php
            class Test {
                public function __construct(
                    readonly string $string = "string",
                    int $int = 0,
                    protected bool $bool = true,
                    $float = 0.0,
                ) {}
            }
            ',
        ];
    }

    /**
     * @dataProvider provideFalse81Cases
     * @requires PHP 8.1
     */
    public function test81BothFalse(string $expected, ?string $input = null): void {
        $this->fixer->configure([
            AlignMultilineParametersFixer::C_VARIABLES => false,
            AlignMultilineParametersFixer::C_DEFAULTS => false,
        ]);
        $this->doTest($expected, $input);
    }

    public function provideFalse81Cases(): iterable {
        foreach ($this->provide81TrueCases() as $key => $case) {
            if (isset($case[1])) {
                yield $key => [$case[1], $case[0]];
            } else {
                yield $key => $case;
            }
        }
    }

    protected function createFixer(): AbstractFixer {
        return new AlignMultilineParametersFixer();
    }

}
