# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Enh #1: `ErickSkrauch\line_break_after_statements` is now also fixes `try-catch-finally` block.

## [1.0.1] - 2023-05-17
### Fixed
- Decrease priority of the `ErickSkrauch\blank_line_around_class_body` fixer to avoid conflict with `no_extra_blank_lines`.

## 1.0.0 - 2023-05-17
### Added
- Initial implementation (extracted from [`elyby/php-code-style`](https://github.com/elyby/php-code-style/tree/0.5.0)).

[Unreleased]: https://github.com/erickskrauch/php-cs-fixer-custom-fixers/compare/1.0.1...HEAD
[1.0.1]: https://github.com/elyby/php-code-style/compare/1.0.0...1.0.1
