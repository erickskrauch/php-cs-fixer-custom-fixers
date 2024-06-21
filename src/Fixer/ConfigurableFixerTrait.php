<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Fixer;

use PhpCsFixer\AbstractFixer;

// PHP-CS-Fixer 3.59.3 has changed implementation of the AbstractFixer by removing method `configure` from it
// and introducing a separate trait \PhpCsFixer\Fixer\ConfigurableFixerTrait with this method inside.
// To mitigate these changes and maintain compatibility with older versions of PHP-CS-Fixer, this solution was created.
//
// Commit: https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/commit/064efa1f#diff-e1fb45756cd1d53b6d67072d8a026692c07af55617018229b0bf4ab6c22e3e53L105
// See https://github.com/erickskrauch/php-cs-fixer-custom-fixers/issues/12
if (method_exists(AbstractFixer::class, 'configure')) {
    trait ConfigurableFixerTrait {

    }
} else {
    trait ConfigurableFixerTrait {
        use \PhpCsFixer\Fixer\ConfigurableFixerTrait;

    }
}
