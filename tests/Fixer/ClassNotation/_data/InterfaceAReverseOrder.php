<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

interface InterfaceAReverseOrder extends InterfaceA {

    public function bar(): void;

    public function foo(): void;

}
