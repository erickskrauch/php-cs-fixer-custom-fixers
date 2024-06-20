<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Legacy;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;

if (!method_exists(AbstractFixer::class, 'configure')) {
    trait LegacyConfigurableFixerTrait{
        use ConfigurableFixerTrait;
    }
}
else{
    trait LegacyConfigurableFixerTrait{}
}
