<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Analyzer;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Taken from the \PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer and simplified
 *
 * @phpstan-type AnalyzedClassElementType 'use_trait'|'case'|'constant'|'property'|'method'
 * @phpstan-type AnalyzedClassElement array{
 *     start: int,
 *     visibility: string,
 *     abstract: bool,
 *     static: bool,
 *     readonly: bool,
 *     type: AnalyzedClassElementType,
 *     name: string,
 *     end: int,
 * }
 */
final class ClassElementsAnalyzer {

    /**
     * @return list<AnalyzedClassElement>
     */
    public function getClassElements(Tokens $tokens, int $classOpenBraceIndex): array {
        static $elementTokenKinds = [CT::T_USE_TRAIT, T_CASE, T_CONST, T_VARIABLE, T_FUNCTION];

        $startIndex = $classOpenBraceIndex + 1;
        $elements = [];

        while (true) {
            $element = [
                'start' => $startIndex,
                'visibility' => 'public',
                'abstract' => false,
                'static' => false,
                'readonly' => false,
            ];

            for ($i = $startIndex; ; ++$i) {
                $token = $tokens[$i];

                // class end
                if ($token->equals('}')) {
                    return $elements; // @phpstan-ignore return.type
                }

                if ($token->isGivenKind(T_ABSTRACT)) {
                    $element['abstract'] = true;

                    continue;
                }

                if ($token->isGivenKind(T_STATIC)) {
                    $element['static'] = true;

                    continue;
                }

                if (defined('T_READONLY') && $token->isGivenKind(T_READONLY)) {
                    $element['readonly'] = true;
                }

                if ($token->isGivenKind([T_PROTECTED, T_PRIVATE])) {
                    $element['visibility'] = strtolower($token->getContent());

                    continue;
                }

                if (!$token->isGivenKind($elementTokenKinds)) {
                    continue;
                }

                $element['type'] = $this->detectElementType($tokens, $i);
                if ($element['type'] === 'property') {
                    $element['name'] = $tokens[$i]->getContent();
                } elseif (in_array($element['type'], ['use_trait', 'case', 'constant', 'method', 'magic', 'construct', 'destruct'], true)) {
                    $element['name'] = $tokens[$tokens->getNextMeaningfulToken($i)]->getContent();
                }

                $element['end'] = $this->findElementEnd($tokens, $i);

                break;
            }

            $elements[] = $element;
            $startIndex = $element['end'] + 1; // @phpstan-ignore offsetAccess.notFound
        }
    }

    /**
     * @phpstan-return AnalyzedClassElementType
     */
    private function detectElementType(Tokens $tokens, int $index): string {
        $token = $tokens[$index];
        if ($token->isGivenKind(CT::T_USE_TRAIT)) {
            return 'use_trait';
        }

        if ($token->isGivenKind(T_CASE)) {
            return 'case';
        }

        if ($token->isGivenKind(T_CONST)) {
            return 'constant';
        }

        if ($token->isGivenKind(T_VARIABLE)) {
            return 'property';
        }

        return 'method';
    }

    private function findElementEnd(Tokens $tokens, int $index): int {
        $endIndex = $tokens->getNextTokenOfKind($index, ['{', ';']);
        if ($tokens[$endIndex]->equals('{')) {
            $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $endIndex);
        }

        for (++$endIndex; $tokens[$endIndex]->isWhitespace(" \t") || $tokens[$endIndex]->isComment(); ++$endIndex);

        --$endIndex;

        return $tokens[$endIndex]->isWhitespace() ? $endIndex - 1 : $endIndex;
    }

}
