<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Tests\Fixer\ClassNotation\_data;

interface InterfaceAandB extends InterfaceA, InterfaceB {

    public function quux(): void;

}
