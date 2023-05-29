<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Whitespace;

use ErickSkrauch\PhpCsFixer\Whitespace\LineBreakAfterStatementsFixer;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @covers \ErickSkrauch\PhpCsFixer\Whitespace\LineBreakAfterStatementsFixer
 */
final class LineBreakAfterStatementsFixerTest extends AbstractFixerTestCase {

    /**
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null): void {
        $this->doTest($expected, $input);
    }

    public function provideFixCases(): iterable {
        // Simple cases
        yield [
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b") {
            // code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b") {
            // code
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b") {
            // code
        } else {
            // another code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b") {
            // code
        } else {
            // another code
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        for ($i = 0; $i < 3; $i++) {
            // code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        for ($i = 0; $i < 3; $i++) {
            // code
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        foreach (["foo", "bar"] as $str) {
            // code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        foreach (["foo", "bar"] as $str) {
            // code
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        while ($i < 10) {
            // code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        while ($i < 10) {
            // code
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        do {
            // code
        } while ($i < 10);

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        do {
            // code
        } while ($i < 10);
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        switch ("str") {
            case "a":
                break;
            case "b":
                break;
            default:
                // code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        switch ("str") {
            case "a":
                break;
            case "b":
                break;
            default:
                // code
        }
        $a = "next statement";
    }
}',
        ];

        // Extended cases
        yield [
            '<?php
class Foo
{
    public function bar()
    {
        if ("a" === "b") {
            // code
        } else if ("a" === "c") {
            // code
        } else if ("a" === "d") {
            // code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function bar()
    {
        if ("a" === "b") {
            // code
        } else if ("a" === "c") {
            // code
        } else if ("a" === "d") {
            // code
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function bar()
    {
        if ("a" === "b") {
            // code
        } elseif ("a" === "c") {
            // code
        } elseif ("a" === "d") {
            // code
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function bar()
    {
        if ("a" === "b") {
            // code
        } elseif ("a" === "c") {
            // code
        } elseif ("a" === "d") {
            // code
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function bar()
    {
        foreach (["foo", "bar"] as $str) {
            if ($str === "foo") {
                // code
            }
        }
    }
}',
            '<?php
class Foo
{
    public function bar()
    {
        foreach (["foo", "bar"] as $str) {
            if ($str === "foo") {
                // code
            }

        }
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        switch ("str") {
            case "a": {
                break;
            }
            case "b": {
                break;
            }
            default: {
                // code
            }
        }

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        switch ("str") {
            case "a": {
                break;
            }
            case "b": {
                break;
            }
            default: {
                // code
            }
        }
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
$a = "prev statement";
foreach ($coordinates as $coordinate) {
    $points = explode(",", $coordinate);
}
',
        ];

        // Issue 5
        yield [
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b")
            $this->bar();

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b")
            $this->bar();
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b")
            $this->bar();
        else
            $this->baz();

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        if ("a" === "b")
            $this->bar();
        else
            $this->baz();
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        for ($i = 0; $i < 3; $i++)
            $this->bar();

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        for ($i = 0; $i < 3; $i++)
            $this->bar();
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        foreach (["foo", "bar"] as $str)
            $this->bar();

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        foreach (["foo", "bar"] as $str)
            $this->bar();
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        while ($i < 10)
            $this->bar();

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        while ($i < 10)
            $this->bar();
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function foo()
    {
        do
            $this->bar();
        while ($i < 10);

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function foo()
    {
        do
            $this->bar();
        while ($i < 10);
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function bar()
    {
        if ("a" === "b")
            $this->foo();
        else if ("a" === "c")
            $this->bar();
        else if ("a" === "d")
            $this->baz();

        $a = "next statement";
    }
}',
            '<?php
class Foo
{
    public function bar()
    {
        if ("a" === "b")
            $this->foo();
        else if ("a" === "c")
            $this->bar();
        else if ("a" === "d")
            $this->baz();
        $a = "next statement";
    }
}',
        ];

        yield [
            '<?php
class Foo
{
    public function bar()
    {
        foreach (["foo", "bar"] as $str)
            if ($str === "foo")
                $this->bar();

        return 3;
    }
}',
            '<?php
class Foo
{
    public function bar()
    {
        foreach (["foo", "bar"] as $str)
            if ($str === "foo")
                $this->bar();
        return 3;
    }
}',
        ];

        yield [
            '<?php
            do {
                $a = 123;
            } while ($value > 10); // comment here
            ',
        ];

        yield [
            '<?php
            try {
                $a = 123;
            } catch (Throwable $e) {
                // Do nothing
            }

            $a = 321;
            ',
            '<?php
            try {
                $a = 123;
            } catch (Throwable $e) {
                // Do nothing
            }
            $a = 321;
            ',
        ];

        yield [
            '<?php
            try {
                $a = 123;
            } catch (Exception $e) {
                // Do nothing
            } catch (Throwable $e) {
                // More general nothing
            }

            $a = 321;
            ',
            '<?php
            try {
                $a = 123;
            } catch (Exception $e) {
                // Do nothing
            } catch (Throwable $e) {
                // More general nothing
            }
            $a = 321;
            ',
        ];

        yield [
            '<?php
            try {
                $a = 123;
            } catch (Throwable $e) {
                // Do nothing
            } finally {
                // Also do something
            }

            $a = 321;
            ',
            '<?php
            try {
                $a = 123;
            } catch (Throwable $e) {
                // Do nothing
            } finally {
                // Also do something
            }
            $a = 321;
            ',
        ];
    }

    protected function createFixer(): AbstractFixer {
        return new LineBreakAfterStatementsFixer();
    }

}
