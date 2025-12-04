# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Shellvar is a PHP CLI utility for working with shell script variables. It provides two main commands:
- **lint**: Report/fix shell variables not in `${VAR}` format
- **extract**: Extract variables with comments and values, output as CSV or Markdown

## Commands

```bash
# Install dependencies
composer install

# Run linting (phpcs, phpstan, rector)
composer lint

# Fix linting issues
composer lint-fix

# Run tests (no coverage)
composer test

# Run tests with coverage
composer test-coverage

# Run a single test
./vendor/bin/phpunit --filter=TestClassName
./vendor/bin/phpunit --filter=testMethodName
./vendor/bin/phpunit tests/phpunit/Unit/VariableTest.php

# Build PHAR
composer build
```

## Architecture

The codebase uses a plugin-style architecture with autodiscovery:

### Core Pattern: Manager + AutodiscoveryFactory

Each major subsystem (Extractor, Filter, Formatter) follows the same pattern:
- **Manager** (singleton): Orchestrates operations, collects console arguments/options from plugins
- **AutodiscoveryFactory**: Scans directory for classes implementing `FactoryDiscoverableInterface`
- **Plugins**: Self-registering classes that provide `getName()` and console argument/option definitions

### Subsystems

**Extractors** (`src/Extractor/`): Parse shell scripts to find variables
- `ShellExtractor`: Main extractor using `VariableParser` for complex shell syntax

**Filters** (`src/Filter/`): Remove unwanted variables from results
- `ExcludeLocalFilter`: Filter local variables
- `ExcludePrefixFilter`: Filter by variable prefix
- `ExcludeFromFileFilter`: Filter using exclusion file

**Formatters** (`src/Formatter/`): Output extracted variables
- `CsvFormatter`: CSV output
- `MarkdownTableFormatter`: Markdown table
- `MarkdownBlocksFormatter`: Markdown blocks with template support

### Key Interfaces

- `FactoryDiscoverableInterface`: Required for autodiscovery, provides `getName()`
- `ConsoleAwareInterface`: Provides `getConsoleArguments()` and `getConsoleOptions()`
- `SingletonInterface`/`SingletonTrait`: Used by managers

### Data Flow (Extract Command)

1. `ExtractCommand` creates `Config` and initializes managers
2. `ExtractorManager::extract()` parses files → `Variable[]`
3. `FilterManager::filter()` removes unwanted variables
4. `FormatterManager::format()` produces output string

## Testing

- **Unit tests**: `tests/phpunit/Unit/` - Test individual classes
- **Functional tests**: `tests/phpunit/Functional/` - Test full command execution
- **Fixtures**: `tests/phpunit/Fixtures/` - Shell scripts and expected outputs
- **Traits**: `AssertTrait`, `FixtureTrait`, `MockTrait`, `ReflectionTrait` for test helpers

## Adding New Plugins

To add a new Filter/Formatter/Extractor:
1. Create class in appropriate `src/{Type}/` directory
2. Implement `FactoryDiscoverableInterface` and extend the abstract base class
3. Implement `getName()` returning a unique identifier
4. Add console options via `getConsoleOptions()` if needed
5. The autodiscovery will automatically register the class
