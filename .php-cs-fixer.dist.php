<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('tests/Fixer/ClassNotation/_data')
;

return Ely\CS\Config::create([
    // Disable "parameters" and "match" to keep compatibility with PHP 7.4
    'trailing_comma_in_multiline' => [
        'elements' => ['arrays', 'arguments'],
    ],
])->setFinder($finder);
