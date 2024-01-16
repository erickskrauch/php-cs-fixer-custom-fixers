<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\Analyzer;

use LogicException;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;

// TODO: better naming
// TODO: cover with tests
final class ClassNameAnalyzer {

    private NamespacesAnalyzer $namespacesAnalyzer;

    private NamespaceUsesAnalyzer $namespacesUsesAnalyzer;

    public function __construct() {
        $this->namespacesAnalyzer = new NamespacesAnalyzer();
        $this->namespacesUsesAnalyzer = new NamespaceUsesAnalyzer();
    }

    /**
     * @see https://www.php.net/manual/en/language.namespaces.rules.php
     *
     * @phpstan-return class-string
     */
    public function getFqn(Tokens $tokens, int $classNameIndex): string {
        $firstPart = $tokens[$classNameIndex];
        if (!$firstPart->isGivenKind([T_STRING, T_NS_SEPARATOR])) {
            throw new LogicException(sprintf('No T_STRING or T_NS_SEPARATOR at given index %d, got "%s".', $classNameIndex, $firstPart->getName()));
        }

        $relativeClassName = $this->readClassName($tokens, $classNameIndex);
        if (str_starts_with($relativeClassName, '\\')) {
            return $relativeClassName; // @phpstan-ignore return.type
        }

        $namespace = $this->namespacesAnalyzer->getNamespaceAt($tokens, $classNameIndex);
        $uses = $this->namespacesUsesAnalyzer->getDeclarationsInNamespace($tokens, $namespace);
        $parts = explode('\\', $relativeClassName, 2);
        /** @var \PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis $use */
        foreach ($uses as $use) {
            if ($use->getShortName() !== $parts[0]) {
                continue;
            }

            // @phpstan-ignore return.type
            return '\\' . $use->getFullName() . (isset($parts[1]) ? ('\\' . $parts[1]) : '');
        }

        // @phpstan-ignore return.type
        return ($namespace->getFullName() !== '' ? '\\' : '') . $namespace->getFullName() . '\\' . $relativeClassName;
    }

    private function readClassName(Tokens $tokens, int $classNameStart): string {
        $className = '';
        $index = $classNameStart;
        do {
            $token = $tokens[$index];
            if ($token->isWhitespace()) {
                continue;
            }

            $className .= $token->getContent();
        } while ($tokens[++$index]->isGivenKind([T_STRING, T_NS_SEPARATOR, T_WHITESPACE]));

        return $className;
    }

}
