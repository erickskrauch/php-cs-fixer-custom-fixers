<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer;

abstract class AbstractFixer extends \PhpCsFixer\AbstractFixer {

    /**
     * {@inheritdoc}
     */
    public function getName(): string {
        return sprintf('ErickSkrauch/%s', parent::getName());
    }

}
