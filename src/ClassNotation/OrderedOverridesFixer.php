<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\ClassNotation;

use ErickSkrauch\PhpCsFixer\AbstractFixer;
use ErickSkrauch\PhpCsFixer\Analyzer\ClassNameAnalyzer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use ReflectionClass;
use SplFileInfo;
use SplStack;

/**
 * @phpstan-type MethodData array{
 *     name: string,
 *     start: int,
 *     end: int,
 * }
 */
final class OrderedOverridesFixer extends AbstractFixer {

    /**
     * @readonly
     */
    private ClassNameAnalyzer $classNameAnalyzer;

    public function __construct() {
        parent::__construct();
        $this->classNameAnalyzer = new ClassNameAnalyzer();
    }

    public function isCandidate(Tokens $tokens): bool {
        return $tokens->isAnyTokenKindsFound([T_CLASS, T_INTERFACE]);
    }

    public function getDefinition(): FixerDefinitionInterface {
        return new FixerDefinition(
            'Overridden and implemented methods must be sorted in the same order as they are defined in parent classes.',
            [
                new CodeSample('<?php
class Foo implements Serializable {

    public function unserialize($data) {}

    public function serialize() {}

}
'),
            ],
        );
    }

    /**
     * Must run before OrderedClassElementsFixer
     * Must run after OrderedInterfacesFixer TODO: it's invariant right now: x < 0, but x > 65
     */
    public function getPriority(): int {
        return 75;
    }

    /**
     * @throws \ReflectionException
     */
    protected function applyFix(SplFileInfo $file, Tokens $tokens): void {
        for ($i = 1, $count = $tokens->count(); $i < $count; ++$i) {
            $classToken = $tokens[$i];
            if (!$classToken->isGivenKind([T_CLASS, T_INTERFACE])) {
                continue;
            }

            $methodsOrder = [];

            $extends = $this->getClassExtensions($tokens, $i, T_EXTENDS);
            $interfaces = $this->getClassExtensions($tokens, $i, T_IMPLEMENTS);
            $extensions = array_merge($extends, $interfaces);
            if (count($extensions) === 0) {
                continue;
            }

            foreach ($extensions as $className) {
                $classReflection = new ReflectionClass($className);
                $parents = $this->getClassParents($classReflection, new SplStack());
                foreach ($parents as $parent) {
                    foreach ($parent->getMethods() as $method) {
                        if (!in_array($method->getShortName(), $methodsOrder, true)) {
                            $methodsOrder[] = $method->getShortName();
                        }
                    }
                }
            }

            if (count($methodsOrder) === 0) {
                continue;
            }

            /** @var array<string, non-negative-int> $methodsPriority */
            $methodsPriority = array_flip(array_reverse($methodsOrder));

            $classBodyStart = $tokens->getNextTokenOfKind($i, ['{']);
            $classBodyEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classBodyStart);

            /** @var list<MethodData> $unsortedMethods */
            $unsortedMethods = [];
            // TODO: actually there still might be properties and traits in between methods declarations
            for ($j = $classBodyStart; $j < $classBodyEnd; $j++) {
                $functionToken = $tokens[$j];
                if (!$functionToken->isGivenKind(T_FUNCTION)) {
                    continue;
                }

                $methodNameToken = $tokens[$tokens->getNextMeaningfulToken($j)];
                // Ensure it's not an anonymous function
                if ($methodNameToken->equals('(')) {
                    continue;
                }

                $methodName = $methodNameToken->getContent();

                // Take the closest whitespace to the beginning of the method
                $methodStart = $tokens->getPrevTokenOfKind($j, ['}', ';', '{']) + 1;
                $methodEnd = $this->findElementEnd($tokens, $j);

                $unsortedMethods[] = [
                    'name' => $methodName,
                    'start' => $methodStart,
                    'end' => $methodEnd,
                ];
            }

            $sortedMethods = $this->sortMethods($unsortedMethods, $methodsPriority);
            if ($sortedMethods === $unsortedMethods) {
                continue;
            }

            $startIndex = $unsortedMethods[array_key_first($unsortedMethods)]['start'];
            $endIndex = $unsortedMethods[array_key_last($unsortedMethods)]['end'];
            $replaceTokens = [];
            foreach ($sortedMethods as $method) {
                for ($k = $method['start']; $k <= $method['end']; ++$k) {
                    $replaceTokens[] = clone $tokens[$k];
                }
            }

            $tokens->overrideRange($startIndex, $endIndex, $replaceTokens);

            $i = $endIndex;
        }
    }

    /**
     * @return array<int, class-string>
     */
    private function getClassExtensions(Tokens $tokens, int $classTokenIndex, int $extensionType): array {
        $extensionTokenIndex = $tokens->getNextTokenOfKind($classTokenIndex, [[$extensionType], '{']);
        if (!$tokens[$extensionTokenIndex]->isGivenKind($extensionType)) {
            return [];
        }

        $classNames = [];
        $classStartIndex = $tokens->getNextMeaningfulToken($extensionTokenIndex);
        do {
            $nextDelimiterIndex = $tokens->getNextTokenOfKind($classStartIndex, [',', '{']);
            $classNames[] = $this->classNameAnalyzer->getFqn($tokens, $classStartIndex);
            $classStartIndex = $tokens->getNextMeaningfulToken($nextDelimiterIndex);
        } while ($tokens[$nextDelimiterIndex]->getContent() === ',');

        return $classNames;
    }

    /**
     * @param ReflectionClass<object> $class
     * @param SplStack<ReflectionClass<object>> $stack
     *
     * @return SplStack<ReflectionClass<object>>
     */
    private function getClassParents(ReflectionClass $class, SplStack $stack): SplStack {
        $stack->push($class);
        $parent = $class->getParentClass();
        if ($parent !== false) {
            $stack = $this->getClassParents($parent, $stack);
        }

        $interfaces = $class->getInterfaces();
        if (count($interfaces) > 0) {
            foreach (array_reverse($interfaces) as $interface) {
                $stack = $this->getClassParents($interface, $stack);
            }
        }

        return $stack;
    }

    /**
     * Taken from the OrderedClassElementsFixer
     */
    private function findElementEnd(Tokens $tokens, int $index): int {
        $blockStart = $tokens->getNextTokenOfKind($index, ['{', ';']);
        if ($tokens[$blockStart]->equals('{')) {
            $blockEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $blockStart);
        } else {
            $blockEnd = $blockStart;
        }

        for (++$blockEnd; $tokens[$blockEnd]->isWhitespace(" \t") || $tokens[$blockEnd]->isComment(); ++$blockEnd);

        --$blockEnd;

        return $tokens[$blockEnd]->isWhitespace() ? $blockEnd - 1 : $blockEnd;
    }

    /**
     * @phpstan-param list<MethodData> $methods
     * @phpstan-param array<string, non-negative-int> $methodsPriority
     *
     * @phpstan-return list<MethodData>
     */
    private function sortMethods(array $methods, array $methodsPriority): array {
        $count = count($methods);
        $targetPriority = $methodsPriority[array_key_last($methodsPriority)];
        for ($i = 0; $i < $count; $i++) {
            $a = $methods[$i];
            if (!isset($methodsPriority[$a['name']])) {
                continue;
            }

            $priorityA = $methodsPriority[$a['name']];
            if ($priorityA === $targetPriority) {
                $targetPriority--;
                continue;
            }

            do {
                for ($j = $i + 1; $j < $count; $j++) {
                    $b = $methods[$j];
                    if (!isset($methodsPriority[$b['name']])) {
                        continue;
                    }

                    $priorityB = $methodsPriority[$b['name']];
                    if ($priorityB === $targetPriority) {
                        $methods[$i] = $b;
                        $methods[$j] = $a;
                        $targetPriority--;

                        continue 3;
                    }
                }
            } while ($targetPriority > $priorityA && $targetPriority-- >= 0); // @phpstan-ignore greaterOrEqual.alwaysTrue
        }

        // @phpstan-ignore return.type
        return $methods;
    }

}
