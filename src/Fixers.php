<?php
declare(strict_types=1);

namespace ErickSkrauch\PhpCsFixer;

use IteratorAggregate;
use PhpCsFixer\Finder;
use PhpCsFixer\Fixer\FixerInterface;
use ReflectionClass;
use Traversable;

/**
 * @implements \IteratorAggregate<FixerInterface>
 */
final class Fixers implements IteratorAggregate {

    /**
     * @return \Generator<FixerInterface>
     */
    public function getIterator(): Traversable {
        $finder = new Finder();
        $finder->in(__DIR__ . '/Fixer')->name('*.php');
        $classes = [];
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            // -4 is set to cut ".php" extension
            /** @var class-string<FixerInterface> $class */
            $class = __NAMESPACE__ . str_replace('/', '\\', mb_substr($file->getPathname(), mb_strlen(__DIR__), -4));
            if (!class_exists($class)) {
                continue;
            }

            $rfl = new ReflectionClass($class);
            if (!$rfl->implementsInterface(FixerInterface::class) || $rfl->isAbstract()) {
                continue;
            }

            $classes[] = $class;
        }

        foreach ($classes as $class) {
            yield new $class();
        }
    }

}
