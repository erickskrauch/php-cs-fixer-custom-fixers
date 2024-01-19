<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer\ClassNotation;

use ErickSkrauch\PhpCsFixer\AbstractFixer;
use ErickSkrauch\PhpCsFixer\Analyzer\ClassElementsAnalyzer;
use ErickSkrauch\PhpCsFixer\Analyzer\ClassNameAnalyzer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use SplStack;

/**
 * @phpstan-import-type AnalyzedClassElement from \ErickSkrauch\PhpCsFixer\Analyzer\ClassElementsAnalyzer
 */
final class OrderedOverridesFixer extends AbstractFixer {

    /**
     * @readonly
     */
    private ClassNameAnalyzer $classNameAnalyzer;

    /**
     * @readonly
     */
    private ClassElementsAnalyzer $classElementsAnalyzer;

    public function __construct() {
        parent::__construct();
        $this->classNameAnalyzer = new ClassNameAnalyzer();
        $this->classElementsAnalyzer = new ClassElementsAnalyzer();
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
     *                                             see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/7760
     */
    public function getPriority(): int {
        return 75;
    }

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
                try {
                    $classReflection = new ReflectionClass($className);
                } catch (ReflectionException $e) { // @phpstan-ignore catch.neverThrown
                    continue;
                }

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

            $unsortedElements = $this->classElementsAnalyzer->getClassElements($tokens, $classBodyStart);
            $sortedElements = $this->sortElements($unsortedElements, $methodsPriority);
            if ($sortedElements === $unsortedElements) {
                continue;
            }

            $startIndex = $unsortedElements[array_key_first($unsortedElements)]['start'];
            $endIndex = $unsortedElements[array_key_last($unsortedElements)]['end'];
            $replaceTokens = [];
            foreach ($sortedElements as $method) {
                for ($k = $method['start']; $k <= $method['end']; ++$k) {
                    $replaceTokens[] = clone $tokens[$k];
                }
            }

            $tokens->overrideRange($startIndex, $endIndex, $replaceTokens);

            $i = $classBodyEnd + 1;
        }
    }

    /**
     * @return list<class-string>
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
     * @phpstan-param list<AnalyzedClassElement> $elements
     * @phpstan-param array<string, non-negative-int> $methodsPriority
     *
     * @phpstan-return list<AnalyzedClassElement>
     */
    private function sortElements(array $elements, array $methodsPriority): array {
        $count = count($elements);
        $targetPriority = $methodsPriority[array_key_last($methodsPriority)];
        for ($i = 0; $i < $count; $i++) {
            $a = $elements[$i];
            if ($a['type'] !== 'method') {
                continue;
            }

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
                    $b = $elements[$j];
                    if ($b['type'] !== 'method') {
                        continue;
                    }

                    if (!isset($methodsPriority[$b['name']])) {
                        continue;
                    }

                    $priorityB = $methodsPriority[$b['name']];
                    if ($priorityB === $targetPriority) {
                        $elements[$i] = $b;
                        $elements[$j] = $a;
                        $targetPriority--;

                        continue 3;
                    }
                }
            } while ($targetPriority > $priorityA && $targetPriority-- >= 0); // @phpstan-ignore greaterOrEqual.alwaysTrue
        }

        // @phpstan-ignore return.type
        return $elements;
    }

}
