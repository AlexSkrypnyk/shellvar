# Contributing

This guide covers working on Shellvar from a clone: local setup, the lint and test commands, building the PHAR and how releases are published.

## Local setup

You need PHP 8.2 or later and Composer. Install the dependencies:

```bash
composer install
```

Run the tool from the clone:

```bash
./shellvar extract path/to/script.sh
```

## Lint and test

The project uses PHP_CodeSniffer, PHPStan and Rector for linting and PHPUnit for tests:

```bash
composer lint
composer lint-fix
composer test
composer test-coverage
```

`composer test` runs without coverage. `composer test-coverage` collects coverage and needs a coverage driver such as Xdebug or PCOV.

To run a single test class or method, pass a filter:

```bash
./vendor/bin/phpunit --filter=VariableTest
```

## Build the PHAR

Build a standalone PHAR with [Box](https://github.com/box-project/box):

```bash
composer build
```

The PHAR is written to `.build/shellvar`. The build patches Box for PHP 8.5 with `box-php85.patch`, so it needs the `patch` utility.

## Add a filter or formatter

Extractors, filters and formatters are discovered automatically from their directories under `src/`. To add a filter or formatter:

1. Create a class in `src/Filter/` or `src/Formatter/` that extends `AbstractFilter` or `AbstractFormatter`.
2. Return a unique name from `getName()`. For a formatter, this name is the value of `--format`.
3. Return any console options from `getConsoleOptions()`. The `extract` command picks them up.

## Releases

Pushing a tag runs the `Release PHP` workflow. It builds the PHAR and attaches it to the GitHub release for that tag.

The [Docker image](https://hub.docker.com/r/drevops/shellvar) is built for `linux/amd64` and `linux/arm64` and pushed to Docker Hub with the following tags:

- `XX.YY.ZZ` and `latest` - when a release tag is pushed, from the `Release Docker` workflow.
- `canary` - on every push to the `main` branch, from the `Test Docker` workflow after its tests pass on both architectures.

Both workflows log in to Docker Hub with the `DOCKER_USER` and `DOCKER_PASS` repository secrets. To push to a different image, set the `DOCKER_IMAGE` repository variable.
