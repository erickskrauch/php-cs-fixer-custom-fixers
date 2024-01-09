# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fixed
- Bug #8: `ErickSkrauch/align_multiline_parameters` produces new line for promoted properties without any types.

## [1.2.2] - 2024-01-07
### Added
- `ErickSkrauch/align_multiline_parameters` now correctly handle non-latin types and variables names (but you still shouldn't do that).

### Fixed
- Bug #6: `ErickSkrauch/align_multiline_parameters` not working correctly with unions and intersections.
- `ErickSkrauch/align_multiline_parameters` inserted a space between the type and the param name in the wrong position when there were no whitespace between them.

## [1.2.1] - 2023-11-16
### Fixed
- Bug #3: `ErickSkrauch/align_multiline_parameters` not working correctly with nullable type hints.

## [1.2.0] - 2023-07-20
### Changed
- `ErickSkrauch\line_break_after_statements` no longer removes extra blank lines in consecutive closing curly braces. Use [`no_extra_blank_lines`](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/rules/whitespace/no_extra_blank_lines.rst) with `curly_brace_block` tokens configuration for this fix.

## [1.1.0] - 2023-05-29
### Added
- Enh #1: `ErickSkrauch\line_break_after_statements` is now also fixes `try-catch-finally` block.

## [1.0.1] - 2023-05-17
### Fixed
- Decrease priority of the `ErickSkrauch\blank_line_around_class_body` fixer to avoid conflict with `no_extra_blank_lines`.

## 1.0.0 - 2023-05-17
### Added
- Initial implementation (extracted from [`elyby/php-code-style`](https://github.com/elyby/php-code-style/tree/0.5.0)).

[Unreleased]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/compare/1.2.2...HEAD
[1.2.2]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/compare/1.0.1...1.1.0
[1.0.1]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/compare/1.0.0...1.0.1
