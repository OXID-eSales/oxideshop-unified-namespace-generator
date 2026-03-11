# Change Log for OXID eSales Unified Namespace Generator

## v5.3.0 - unreleased

### Added
- PHP v8.5 support

### Fixed
- Moved `composer/composer` to `require-dev` [#0007877](https://bugs.oxid-esales.com/view.php?id=7877)

### Removed
- PHP v8.2 support

## v5.2.0 - 2025-04-09

### Added
- PHPUnit v11 support

### Removed
- PHPUnit v10 support

### Deprecated
- Edition-specific class map loaders and related functionality
- Adding exception codes on errors

## v5.1.0 - 2024-10-14

### Added
- PHPUnit v10 support

### Changed
- Class template engine switched from Symfony template engine to Twig

### Removed
- PHPUnit v9 support
- PHP v8.1 support

## v5.0.0 - 2024-03-15

### Added
- PHP version requirement to ^8.1.

### Removed
- Smarty template engine support

## v4.1.0 - 2023-04-19

### Changed
- License updated

## v4.0.0 - 2022-10-06

### Removed
- Composer v1 support

## v3.0.0 - 2021-06-10

### Changed
- Update symfony components to version 5

## v2.2.0 - 2021-04-12

### Added
- Support php 8

## v2.1.0 - 2020-11-04

### Added
- Support for composer v2

## v2.0.1 - 2018-12-03

### Added
- Changelog added

### Changed
- Ignore PhpStorm and Composer working files [PR-3](https://github.com/OXID-eSales/oxideshop-unified-namespace-generator/pull/3)
- Exclude non-essential files from dist package [PR-2](https://github.com/OXID-eSales/oxideshop-unified-namespace-generator/pull/2)
