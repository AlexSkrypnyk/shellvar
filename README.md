# Shell variables extractor

Scan a file or a directory with shell scripts and extract all variables.

[![Tests](https://github.com/AlexSkrypnyk/shell-variables-extractor/actions/workflows/test.yml/badge.svg)](https://github.com/AlexSkrypnyk/shell-variables-extractor/actions/workflows/test.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/AlexSkrypnyk/shell-variables-extractor)
![LICENSE](https://img.shields.io/github/license/AlexSkrypnyk/shell-variables-extractor)

## Features

- Scan a file or a directory with shell scripts and extract found variables with comments and values.
- Filter variables: exclude local, exclude by prefix, exclude from a list in file.
- Format output as CSV, Markdown table or Markdown blocks defined in template.
- Extend filters and formatters with custom classes.

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

With excluded variables specified in the file, custom value `<NOT SET>` for variables without a value, and output as markdown blocks with variables wrapped in inline code:
   
    ./vendor/bin/shell-variables-extractor --exclude-file=./excluded.txt --unset="<NOT SET>" --format=md-blocks --md-inline-code-wrap-vars ../   

## Options

```
      --format=FORMAT                                        The output format. [default: "csv"]
      --skip-description-prefix=SKIP-DESCRIPTION-PREFIX      Skip description lines that start with the provided prefix. (multiple values allowed)
      --exclude-prefix=EXCLUDE-PREFIX                        Exclude variables that start with the provided prefix. (multiple values allowed)
      --exclude-file=EXCLUDE-FILE                            A path to a file that contains variables to be excluded from the extraction process. (multiple values allowed)
      --exclude-local                                        Indicates that the tool should only consider global variables, ignoring local variables.
      --fields=FIELDS                                        Semicolon-separated list of fields. Each field is a key-label pair in the format "key=label". If label is omitted, key is used as label. [default: "name=Name;default_value=\"Default value\";description=Description"]
      --unset=UNSET                                          Specifies a placeholder value for variables that are defined but have no set value. [default: "<UNSET>"]
      --sort                                                 Sort variables by name.
      --path-strip-prefix=PATH-STRIP-PREFIX                  Strip the provided prefix from the path.
      --csv-separator=CSV-SEPARATOR                          Separator for the CSV output format. [default: ";"]
      --md-link-vars                                         Link variables within usages to their definitions in the Markdown output format.
      --md-inline-code-wrap-vars                             Wrap variables to show them as inline code in the Markdown output format.
      --md-inline-code-wrap-numbers                          Wrap numbers to show them as inline code in the Markdown output format.
      --md-inline-code-extra-file=MD-INLINE-CODE-EXTRA-FILE  A path to a file that contains additional strings to be formatted as inline code in the Markdown output format. (multiple values allowed)
      --md-block-template-file=MD-BLOCK-TEMPLATE-FILE        A path to a Markdown template file used for Markdown blocks (md-blocks) output format.
                                                             {{ name }}, {{ description }} and {{ default_value }} tokens can be used within the template.

```

## Maintenance

    composer install
    composer lint
    composer lint:fix
    composer test
