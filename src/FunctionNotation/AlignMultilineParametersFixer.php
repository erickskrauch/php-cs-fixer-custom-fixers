<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\FunctionNotation;

use ErickSkrauch\PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use SplFileInfo;

final class AlignMultilineParametersFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface {

    /**
     * @internal
     */
    public const C_VARIABLES = 'variables';
    /**
     * @internal
     */
    public const C_DEFAULTS = 'defaults';

    private array $parameterModifiers;

    public function __construct() {
        parent::__construct();
        $this->parameterModifiers = [
            CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC,
            CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED,
            CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE,
        ];
        if (defined('T_READONLY')) {
            $this->parameterModifiers[] = T_READONLY;
        }
    }

    public function getDefinition(): FixerDefinitionInterface {
        return new FixerDefinition(
            'Aligns parameters in multiline function declaration.',
            [
                new CodeSample(
                    '<?php
function test(
    string $a,
    int $b = 0
): void {};
',
                ),
                new CodeSample(
                    '<?php
function test(
    string $string,
    int    $int    = 0
): void {};
',
                    [self::C_VARIABLES => false, self::C_DEFAULTS => false],
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool {
        return $tokens->isAnyTokenKindsFound([T_FUNCTION, T_FN]);
    }

    /**
     * Must run after StatementIndentationFixer, MethodArgumentSpaceFixer, CompactNullableTypehintFixer
     */
    public function getPriority(): int {
        return -10;
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder(self::C_VARIABLES, 'On null no value alignment, on bool forces alignment.'))
                ->setAllowedTypes(['bool', 'null'])
                ->setDefault(true)
                ->getOption(),
            (new FixerOptionBuilder(self::C_DEFAULTS, 'On null no value alignment, on bool forces alignment.'))
                ->setAllowedTypes(['bool', 'null'])
                ->setDefault(null)
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void {
        // There is nothing to do
        if ($this->configuration[self::C_VARIABLES] === null && $this->configuration[self::C_DEFAULTS] === null) {
            return;
        }

        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $functionsAnalyzer = new FunctionsAnalyzer();
        /** @var \PhpCsFixer\Tokenizer\Token $functionToken */
        foreach ($tokens as $i => $functionToken) {
            if (!$functionToken->isGivenKind([T_FUNCTION, T_FN])) {
                continue;
            }

            $openBraceIndex = $tokens->getNextTokenOfKind($i, ['(']);
            $isMultiline = $tokensAnalyzer->isBlockMultiline($tokens, $openBraceIndex);
            if (!$isMultiline) {
                continue;
            }

            /** @var \PhpCsFixer\Tokenizer\Analyzer\Analysis\ArgumentAnalysis[] $arguments */
            $arguments = $functionsAnalyzer->getFunctionArguments($tokens, $i);
            if (empty($arguments)) {
                continue;
            }

            $longestType = 0;
            $longestVariableName = 0;
            $hasAtLeastOneTypedArgument = false;
            foreach ($arguments as $argument) {
                $typeAnalysis = $argument->getTypeAnalysis();
                if ($typeAnalysis) {
                    $hasAtLeastOneTypedArgument = true;
                    $typeLength = $this->getFullTypeLength($tokens, $typeAnalysis->getStartIndex());
                    if ($typeLength > $longestType) {
                        $longestType = $typeLength;
                    }
                }

                $variableNameLength = strlen($argument->getName());
                if ($variableNameLength > $longestVariableName) {
                    $longestVariableName = $variableNameLength;
                }
            }

            $argsIndent = WhitespacesAnalyzer::detectIndent($tokens, $i) . $this->whitespacesConfig->getIndent();
            foreach ($arguments as $argument) {
                if ($this->configuration[self::C_VARIABLES] !== null) {
                    $whitespaceIndex = $argument->getNameIndex() - 1;
                    if ($this->configuration[self::C_VARIABLES] === true) {
                        $typeLen = 0;
                        if ($argument->getTypeAnalysis() !== null) {
                            $typeLen = $this->getFullTypeLength($tokens, $argument->getTypeAnalysis()->getStartIndex());
                        }

                        $appendix = str_repeat(' ', $longestType - $typeLen + (int)$hasAtLeastOneTypedArgument);
                        if ($argument->hasTypeAnalysis()) {
                            $whitespaceToken = $appendix;
                        } else {
                            $whitespaceToken = $this->whitespacesConfig->getLineEnding() . $argsIndent . $appendix;
                        }
                    } else {
                        if ($argument->hasTypeAnalysis()) {
                            $whitespaceToken = ' ';
                        } else {
                            $whitespaceToken = $this->whitespacesConfig->getLineEnding() . $argsIndent;
                        }
                    }

                    $tokens->ensureWhitespaceAtIndex($whitespaceIndex, 0, $whitespaceToken);
                }

                if ($this->configuration[self::C_DEFAULTS] !== null) {
                    // Can't use $argument->hasDefault() because it's null when it's default for a type (e.g. 0 for int)
                    /** @var \PhpCsFixer\Tokenizer\Token $equalToken */
                    $equalToken = $tokens[$tokens->getNextMeaningfulToken($argument->getNameIndex())];
                    if ($equalToken->getContent() === '=') {
                        $nameLen = strlen($argument->getName());
                        $whitespaceIndex = $argument->getNameIndex() + 1;
                        if ($this->configuration[self::C_DEFAULTS] === true) {
                            $tokens->ensureWhitespaceAtIndex($whitespaceIndex, 0, str_repeat(' ', $longestVariableName - $nameLen + 1));
                        } else {
                            $tokens->ensureWhitespaceAtIndex($whitespaceIndex, 0, ' ');
                        }
                    }
                }
            }
        }
    }

    /**
     * TODO: The declaration might be split across multiple lines.
     *       In such case we need to find the longest line and return it as the full type length
     *
     * @param int $typeIndex points to the beginning of the type
     */
    private function getFullTypeLength(Tokens $tokens, int $typeIndex): int {
        $typeLength = 0;
        $varNameTokenIndex = $tokens->getNextTokenOfKind($typeIndex, [[T_VARIABLE]]);
        for ($i = $typeIndex; $i < $varNameTokenIndex - 1; $i++) { // -1 to avoid whitespace between param name and type
            $typeLength += strlen($tokens[$i]->getContent());
        }

        $possiblyReadonlyToken = $tokens[$typeIndex - 2];
        if ($possiblyReadonlyToken->isGivenKind($this->parameterModifiers)) {
            $whitespaceToken = $tokens[$typeIndex - 1];
            $typeLength += strlen($possiblyReadonlyToken->getContent() . $whitespaceToken->getContent());
        }

        $possiblyPromotionToken = $tokens[$typeIndex - 4];
        if ($possiblyPromotionToken->isGivenKind($this->parameterModifiers)) {
            $whitespaceToken = $tokens[$typeIndex - 3];
            $typeLength += strlen($possiblyPromotionToken->getContent() . $whitespaceToken->getContent());
        }

        return $typeLength;
    }

}
