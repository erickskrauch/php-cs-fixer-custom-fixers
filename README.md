# PHP-CS-Fixer custom fixers

A set of custom fixers for [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-build-status]][link-build-status]

## Installation

Run:

```sh
composer require --dev erickskrauch/php-cs-fixer-custom-fixers
```

Then in your configuration file (`.php-cs-fixer.php`) register fixers and use them:

```php
<?php
return (new \PhpCsFixer\Config())
    ->registerCustomFixers(new \ErickSkrauch\PhpCsFixer\Fixers())
    ->setRules([
        'ErickSkrauch/align_multiline_parameters' => true,
        // See the rest of the fixers below
    ]);
```

## Fixers

Table of contents:

* [`ErickSkrauch/align_multiline_parameters`](#erickskrauchalign_multiline_parameters) - Align multiline function params (or remove alignment).
* [`ErickSkrauch/blank_line_around_class_body`](#erickskrauchblank_line_around_class_body) - Add space inside class body.
* [`ErickSkrauch/blank_line_before_return`](#erickskrauchblank_line_before_return) - Add blank line before `return`.
* [`ErickSkrauch/line_break_after_statements`](#erickskrauchline_break_after_statements) - Add blank line after control structures.
* [`ErickSkrauch/multiline_if_statement_braces`](#erickskrauchmultiline_if_statement_braces) - Fix brace position for multiline `if` statements.
* [`ErickSkrauch/ordered_overrides`](#erickskrauchordered_overrides) - Sort overridden methods.
* [`ErickSkrauch/remove_class_name_method_usages`](#erickskrauchremove_class_name_method_usages-yii2) - Replace `::className()` with `:class` (Yii2).

### `ErickSkrauch/align_multiline_parameters`

Forces aligned or not aligned multiline function parameters:

```diff
--- Original
+++ New
@@ @@
  function foo(
      string $string,
-     int $index = 0,
-     $arg = 'no type',
-     ...$variadic,
+     int    $index    = 0,
+            $arg      = 'no type',
+         ...$variadic
  ): void {}
```

**Configuration:**

* `variables` - when set to `true`, forces variables alignment. On `false` forces strictly no alignment.
  You can set it to `null` to disable touching of variables. **Default**: `true`.

* `defaults` - when set to `true`, forces defaults alignment. On `false` forces strictly no alignment.
  You can set it to `null` to disable touching of defaults. **Default**: `false`.

### `ErickSkrauch/blank_line_around_class_body`

Ensure that a class body contains one blank line after its definition and before its end:

```diff
--- Original
+++ New
@@ @@
  <?php
  class Test {
+
      public function func() {
          $obj = new class extends Foo {
+
              public $prop;
+
          }
      }
+
  }
```

**Configuration:**

* `apply_to_anonymous_classes` - should this fixer be applied to anonymous classes? If it is set to `false`, than
  anonymous classes will be fixed to don't have empty lines around body. **Default**: `true`.

* `blank_lines_count` - adjusts an amount of the blank lines. **Default**: `1`.

### `ErickSkrauch/blank_line_before_return`

This is extended version of the original `blank_line_before_statement` fixer. It applies only to `return` statements
and only in cases, when on the current nesting level more than one statements.

```diff
--- Original
+++ New
@@ @@
 <?php
 public function foo() {
     $a = 'this';
     $b = 'is';
+
     return "$a $b awesome";
 }

 public function bar() {
     $this->foo();
     return 'okay';
 }
```

### `ErickSkrauch/line_break_after_statements`

Ensures that there is one blank line above the next statements: `if`, `switch`, `for`, `foreach`, `while`, `do-while` and `try-catch-finally`.

```diff
--- Original
+++ New
@@ @@
 <?php
 $a = 123;
 if ($a === 123) {
     // Do something here
 }
+
 $b = [1, 2, 3];
 foreach ($b as $number) {
     if ($number === 3) {
         echo 'it is three!';
     }
 }
+
 $c = 'next statement';
```

### `ErickSkrauch/multiline_if_statement_braces`

Ensures that multiline if statement body curly brace placed on the right line.

```diff
--- Original
+++ New
@@ @@
 <?php
 if ($condition1 === 123
- && $condition2 = 321) {
+ && $condition2 = 321
+) {
     // Do something here
 }
```

**Configuration:**

* `keep_on_own_line` - should this place closing bracket on its own line? If it's set to `false`, than
  curly bracket will be placed right after the last condition statement. **Default**: `true`.

### `ErickSkrauch/ordered_overrides`

Overridden and implemented methods must be sorted in the same order as they are defined in parent classes.

```diff
--- Original
+++ New
@@ @@
 <?php
 class Foo implements Serializable {

-    public function unserialize($data) {}
+    public function serialize() {}

-    public function serialize() {}
+    public function unserialize($data) {}

 }
```

### `ErickSkrauch/remove_class_name_method_usages` (Yii2)

Replaces Yii2 [`BaseObject::className()`](https://github.com/yiisoft/yii2/blob/e53fc0ded1/framework/base/BaseObject.php#L84)
usages with native `::class` keyword, introduced in PHP 5.5.

```diff
--- Original
+++ New
@@ @@
  <?php
  use common\models\User;
  
- $className = User::className();
+ $className = User::class;
```

[ico-version]: https://img.shields.io/packagist/v/erickskrauch/php-cs-fixer-custom-fixers.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-green.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/erickskrauch/php-cs-fixer-custom-fixers.svg?style=flat-square
[ico-build-status]: https://img.shields.io/github/actions/workflow/status/erickskrauch/php-cs-fixer-custom-fixers/ci.yml?branch=master&style=flat-square

[link-packagist]: https://packagist.org/packages/erickskrauch/php-cs-fixer-custom-fixers
[link-downloads]: https://packagist.org/packages/erickskrauch/php-cs-fixer-custom-fixers/stats
[link-build-status]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/actions
