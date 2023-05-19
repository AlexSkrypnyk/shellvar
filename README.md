# Shell variables extractor

Scan a file or a directory with shell scripts and extract all variables.

[![Tests](https://github.com/drevops/shell-variables-extractor/actions/workflows/tests.yml/badge.svg)](https://github.com/drevops/shell-variables-extractor/actions/workflows/tests.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/drevops/shell-variables-extractor)
![LICENSE](https://img.shields.io/github/license/drevops/shell-variables-extractor)

## Features

- Scan a file or a directory with shell scripts and extract found variables.
- Extract variables comments.
- Use exclude list to skip specified variables.

## Installation

    composer require drevops/shell-variables-extractor

## Usage

Variables can have descriptions and default values that will be printed out
to the STDOUT in the CSV format as `name, default_value, description`.

This is helpful to maintain a table of variables and their descriptions in
documentation.

    ./shell-variable-extractor.php path/to/file1 path/to/file2
    ./shell-variable-extractor.php path/to/dir

With excluded file:

    ./shell-variable-extractor.php -e ../excluded.txt path/to/file

Full:
    
    ./shell-variable-extractor.php  -t -m -e ./excluded.txt -u "<NOT SET>" ../

## Maintenance

    composer install
    composer lint
    composer lint:fix
    composer test
