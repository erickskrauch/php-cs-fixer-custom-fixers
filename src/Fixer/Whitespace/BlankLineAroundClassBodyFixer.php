<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Fixer\Whitespace;

use ErickSkrauch\PhpCsFixer\Fixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use SplFileInfo;

/**
 * This fixer conflicts with the CurlyBracesPositionFixer (which is part of the BracesFixer),
 * because CurlyBracesPositionFixer always tries to remove any new lines between class beginning
 * and the first meaningful statement. And then this fixer restores those spaces back.
 *
 * That is the reason, why you always see a "braces, Ely/blank_line_around_class_body" in verbose output.
 *
 * @property array{
 *     blank_lines_count: non-negative-int,
 *     apply_to_anonymous_classes: bool,
 * } $configuration
 */
final class BlankLineAroundClassBodyFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface {
    use ConfigurableFixerTrait;

    /**
     * @internal
     */
    public const C_BLANK_LINES_COUNT = 'blank_lines_count';
    /**
     * @internal
     */
    public const C_APPLY_TO_ANONYMOUS_CLASSES = 'apply_to_anonymous_classes';

    public function getDefinition(): FixerDefinitionInterface {
        return new FixerDefinition(
            'Ensure that class body contains one blank line after class definition and before its end.',
            [
                new CodeSample(
                    '<?php
class Sample
{
    protected function foo()
    {
    }
}
',
                ),
                new CodeSample(
                    '<?php
new class extends Foo {

    protected function foo()
    {
    }

};
',
                    [self::C_APPLY_TO_ANONYMOUS_CLASSES => false],
                ),
                new CodeSample(
                    '<?php
new class extends Foo {
    protected function foo()
    {
    }
};
',
                    [self::C_APPLY_TO_ANONYMOUS_CLASSES => true],
                ),
            ],
        );
    }

    /**
     * Must run after NoExtraBlankLinesFixer (-20), ClassDefinitionFixer (36) and CurlyBracesPositionFixer (0).
     */
    public function getPriority(): int {
        return -21;
    }

    public function isCandidate(Tokens $tokens): bool {
        return $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void {
        $analyzer = new TokensAnalyzer($tokens);
        /** @var Token $token */
        foreach ($tokens as $index => $token) {
            if (!$token->isClassy()) {
                continue;
            }

            $countLines = $this->configuration[self::C_BLANK_LINES_COUNT];
            if (!$this->configuration[self::C_APPLY_TO_ANONYMOUS_CLASSES] && $analyzer->isAnonymousClass($index)) {
                $countLines = 0;
            }

            $startBraceIndex = $tokens->getNextTokenOfKind($index, ['{']);
            $nextAfterBraceToken = $tokens[$startBraceIndex + 1];
            if ($nextAfterBraceToken->isWhitespace()) {
                $nextStatementIndex = $tokens->getNextMeaningfulToken($startBraceIndex);
                $nextStatementToken = $tokens[$nextStatementIndex];
                // Traits should be placed right after a class opening brace
                if ($nextStatementToken->getContent() !== 'use') {
                    $this->ensureBlankLines($tokens, $startBraceIndex + 1, $countLines);
                }
            }

            $endBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $startBraceIndex);
            if ($tokens[$endBraceIndex - 1]->isWhitespace()) {
                $this->ensureBlankLines($tokens, $endBraceIndex - 1, $countLines);
            }
        }
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder(self::C_BLANK_LINES_COUNT, 'Adjusts the number of blank lines.'))
                ->setAllowedTypes(['int'])
                ->setDefault(1)
                ->getOption(),
            (new FixerOptionBuilder(self::C_APPLY_TO_ANONYMOUS_CLASSES, 'Whether this fixer should be applied to anonymous classes.'))
                ->setAllowedTypes(['bool'])
                ->setDefault(true)
                ->getOption(),
        ]);
    }

    private function ensureBlankLines(Tokens $tokens, int $index, int $countLines): void {
        $content = $tokens[$index]->getContent();
        // Apply fix only when the lines count doesn't equal to expected
        // Don't check for \r\n sequence since it's still contains \n part
        if (substr_count($content, "\n") === $countLines + 1) {
            return;
        }

        // Use regexp to extract contents between line breaks
        Preg::matchAll('/[^\n\r]+[\r\n]*/', $content, $matches);
        $lines = $matches[0];
        $eol = $this->whitespacesConfig->getLineEnding();
        $tokens->ensureWhitespaceAtIndex($index, 0, str_repeat($eol, $countLines + 1) . end($lines));
    }

}
