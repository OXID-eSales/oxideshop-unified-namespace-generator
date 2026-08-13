# Change Log for OXID eSales Unified Namespace Generator

## v8.0.0-alpha.3 - Unreleased
*Compilation release*

### Removed
- Generation of backwards-compatibility class aliases for legacy class names (e.g. `oxarticle`) in
  generated unified-namespace classes

## v8.0.0-alpha.2 - 2026-02-12
*Compilation release*

## v6.0.0-alpha.1 - 2025-02-03

### Changed
- Exception in Composer Plugin is not caught anymore. Errors and exceptions will be handled by Composer.

### Removed
- Dependency on `oxideshop-facts`
- Deprecated edition-specific class map loaders and related functionality
- Adding exception codes on errors

