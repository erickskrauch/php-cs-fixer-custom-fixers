<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\LanguageConstruct;

use ErickSkrauch\PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

/**
 * Replaces Yii2 BaseObject::className() usages with native ::class keyword, introduced in PHP 5.5.
 *
 * @author ErickSkrauch <erickskrauch@ely.by>
 */
final class RemoveClassNameMethodUsagesFixer extends AbstractFixer {

    public function getDefinition(): FixerDefinitionInterface {
        return new FixerDefinition(
            'Converts Yii2 `BaseObject::className()` method usage into `::class` keyword.',
            [
                new CodeSample(
                    '<?php

use Foo\Bar\Baz;

$className = Baz::className();
',
                ),
            ],
            null,
            'Risky when the method `className()` is overridden.',
        );
    }

    public function isCandidate(Tokens $tokens): bool {
        return $tokens->isTokenKindFound(T_STRING);
    }

    public function isRisky(): bool {
        return true;
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void {
        for ($index = $tokens->count() - 4; $index > 0; --$index) {
            $candidate = $this->getReplaceCandidate($tokens, $index);
            if ($candidate === null) {
                continue;
            }

            $this->fixClassNameMethodUsage(
                $tokens,
                $index,
                $candidate[0], // brace open
                $candidate[1],  // brace close
            );
        }
    }

    /**
     * @return array{int, int}|null
     */
    private function getReplaceCandidate(Tokens $tokens, int $index): ?array {
        if (!$tokens[$index]->isGivenKind(T_STRING)) {
            return null;
        }

        $braceOpenIndex = $tokens->getNextMeaningfulToken($index);
        if (!$tokens[$braceOpenIndex]->equals('(')) {
            return null;
        }

        $braceCloseIndex = $tokens->getNextMeaningfulToken($braceOpenIndex);
        if (!$tokens[$braceCloseIndex]->equals(')')) {
            return null;
        }

        $doubleColon = $tokens->getPrevMeaningfulToken($index);
        if (!$tokens[$doubleColon]->isGivenKind([T_DOUBLE_COLON])) {
            return null;
        }

        $methodName = $tokens[$index]->getContent();
        if ($methodName !== 'className') {
            return null;
        }

        return [
            $braceOpenIndex,
            $braceCloseIndex,
        ];
    }

    private function fixClassNameMethodUsage(
        Tokens $tokens,
        int $index,
        int $braceOpenIndex,
        int $braceCloseIndex
    ): void {
        $tokens->clearTokenAndMergeSurroundingWhitespace($braceCloseIndex);
        $tokens->clearTokenAndMergeSurroundingWhitespace($braceOpenIndex);
        $tokens->clearAt($index);
        $tokens->insertAt($index, new Token([CT::T_CLASS_CONSTANT, 'class']));
    }

}
