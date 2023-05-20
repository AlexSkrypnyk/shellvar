# Shell variables extractor

Scan a file or a directory with shell scripts and extract all variables.

[![Tests](https://github.com/AlexSkrypnyk/shell-variables-extractor/actions/workflows/test.yml/badge.svg)](https://github.com/AlexSkrypnyk/shell-variables-extractor/actions/workflows/test.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/AlexSkrypnyk/shell-variables-extractor)
![LICENSE](https://img.shields.io/github/license/AlexSkrypnyk/shell-variables-extractor)

## Features

- Scan a file or a directory with shell scripts and extract found variables.
- Extract variables comments.
- Use exclude list to skip specified variables.

## Installation

    composer require alexskrypnyk/shell-variables-extractor

## Usage

Variables can have descriptions and default values that will be printed out
to the STDOUT in the CSV format as `name, default_value, description`.

This is helpful to maintain a table of variables and their descriptions in
documentation.

    ./vendor/bin/shell-variables-extractor path/to/file1 path/to/file2

With excluded variables specified in the file:

    ./vendor/bin/shell-variables-extractor --exclude-file=../excluded.txt path/to/file

With excluded variables specified in the file, custom value for variables without a value, and output as markdown with variables wrapped in ticks:
   
    ./vendor/bin/shell-variables-extractor --ticks --markdown --exclude-file=./excluded.txt --unset="<NOT SET>" ../   

## Maintenance

    composer install
    composer lint
    composer lint:fix
    composer test
