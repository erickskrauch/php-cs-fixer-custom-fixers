<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Whitespace;

use Ely\CS\Fixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use SplFileInfo;

final class MultilineIfStatementBracesFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface {

    /**
     * @internal
     */
    public const C_KEEP_ON_OWN_LINE = 'keep_on_own_line';

    public function getDefinition(): FixerDefinitionInterface {
        return new FixerDefinition(
            'Ensures that multiline if statement body curly brace placed on the right line.',
            [
                new CodeSample(
                    '<?php
if ($condition1 == true
 && $condition2 === false) {}
',
                ),
                new CodeSample(
                    '<?php
if ($condition1 == true
 && $condition2 === false
) {}
',
                    [self::C_KEEP_ON_OWN_LINE => false],
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool {
        return $tokens->isTokenKindFound(T_IF);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder(self::C_KEEP_ON_OWN_LINE, 'adjusts the position of condition closing brace.'))
                ->setAllowedTypes(['bool'])
                ->setDefault(true)
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void {
        $keepOnOwnLine = $this->configuration[self::C_KEEP_ON_OWN_LINE];
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $eol = $this->whitespacesConfig->getLineEnding();
        foreach ($tokens as $i => $token) {
            if (!$token->isGivenKind(T_IF)) {
                continue;
            }

            $openBraceIndex = $tokens->getNextTokenOfKind($i, ['(']);
            if (!$tokensAnalyzer->isBlockMultiline($tokens, $openBraceIndex)) {
                continue;
            }

            $closingBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openBraceIndex);
            /** @var \PhpCsFixer\Tokenizer\Token $statementBeforeClosingBrace */
            $statementBeforeClosingBrace = $tokens[$closingBraceIndex - 1];
            if ($keepOnOwnLine) {
                if (!$statementBeforeClosingBrace->isWhitespace()
                 || !\str_contains($statementBeforeClosingBrace->getContent(), $eol)
                ) {
                    $indent = WhitespacesAnalyzer::detectIndent($tokens, $i);
                    $tokens->ensureWhitespaceAtIndex($closingBraceIndex, 0, $eol . $indent);
                }
            } else {
                $tokens->removeLeadingWhitespace($closingBraceIndex);
            }
        }
    }

}
