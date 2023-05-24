# Shell variables extractor

Helps to maintain up-to-date documentation about variables in shell scripts.

[![Tests](https://github.com/AlexSkrypnyk/shell-variables-extractor/actions/workflows/test.yml/badge.svg)](https://github.com/AlexSkrypnyk/shell-variables-extractor/actions/workflows/test.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/AlexSkrypnyk/shell-variables-extractor)
![LICENSE](https://img.shields.io/github/license/AlexSkrypnyk/shell-variables-extractor)

## Features

- Scan a file or a directory containing shell scripts and extract found
  variables with comments and assigned values.
- Filter variables: exclude local, exclude by prefix, exclude from a list in
  file.
- Format output as a CSV, Markdown table or Markdown blocks defined in the
  template.
- Extend filters and formatters with custom classes.

## Installation

    composer require alexskrypnyk/shell-variables-extractor

## Usage

By default, variable names, descriptions (taken from the comments) and their
values are printed to STDOUT in the CSV format. You can also change the output
format to Markdown table or Markdown blocks.

Given the following shell script:

```bash
# Assignment to scalar value.
VAR1=val1
# Assignment to another variable.
VAR2="${VAR1}"
# Parameter expansion.
VAR3=${val3:-abc}
# Parameter expansion with a default value using
# another variable.
#
# Continuation of the multi-line comment.
VAR4=${val4:-$VAR3}
```

### Default CSV output

```bash
./vendor/bin/shell-variables-extractor path/to/script.sh
```

```csv
Name;"Default value";Description
VAR1;val1;"Assignment to scalar value."
VAR2;VAR1;"Assignment to another variable."
VAR3;abc;"Parameter expansion."
VAR4;VAR3;"Parameter expansion with a default value using another variable.
Continuation of the multi-line comment."
```

### Markdown table

```bash
./vendor/bin/shell-variables-extractor --format=md-table path/to/script.sh
```

```markdown
| Name | Default value | Description                                                                                                   |
|------|---------------|---------------------------------------------------------------------------------------------------------------|
| VAR1 | val1          | Assignment to scalar value.                                                                                   |
| VAR2 | VAR1          | Assignment to another variable.                                                                               |
| VAR3 | abc           | Parameter expansion.                                                                                          |
| VAR4 | VAR3          | Parameter expansion with a default value using another variable.<br />Continuation of the multi-line comment. |
```

### Markdown blocks

```bash
./vendor/bin/shell-variables-extractor --format=md-blocks path/to/script.sh
```

```markdown
### `VAR1`

Assignment to scalar value.

Default value: `val1`

### `VAR2`

Assignment to another variable.

Default value: `VAR1`

### `VAR3`

Parameter expansion.

Default value: `abc`

### `VAR4`

Parameter expansion with a default value using another variable.<br />
Continuation of the multi-line comment.

Default value: `VAR3`

```

## Options

There are options for different phases: extraction, filtering and formatting.

"Multiple values allowed" means that you can specify the option multiple times
like so: `--exclude-prefix=VAR1 --exclude-prefix=VAR2` etc.

### Extraction

| Name                               | Default value | Description                                                                          |
|------------------------------------|---------------|--------------------------------------------------------------------------------------|
| `paths`                            |               | File or directory to scan. Multiple files separated by space.                        |
| `--skip-description-prefix=PREFIX` |               | Skip description lines that start with the provided prefix. Multiple values allowed. |

### Filtering

| Name                       | Default value | Description                                                                                                   |
|----------------------------|---------------|---------------------------------------------------------------------------------------------------------------|
| `--exclude-local`          |               | Remove local variables.                                                                                       |
| `--exclude-prefix=PREFIX`  |               | Exclude variables that start with the provided prefix. Multiple values allowed                                |                                                                                                               |
| `--exclude-from-file=FILE` |               | A path to a file that contains variables to be excluded from the extraction process. Multiple values allowed. |

### Format

| Name                               | Default value                                                    | Description                                                                                                                                                                                              |
|------------------------------------|------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `--format=FORMAT`                  | `csv`                                                            | The output format.                                                                                                                                                                                       |
| `--sort`                           |                                                                  | Sort variables in ascending order by name.                                                                                                                                                               |
| `--unset`                          | `UNSET`                                                          | A string to represent a value for variables that are defined but have no set value.                                                                                                                      |
| `--fields=FIELDS`                  | `name=Name;default_value="Default value;description=Description` | Semicolon-separated list of fields. Each field is a key-label pair in the format "key=label". If label is omitted, key is used as label.                                                                 |
| `--path-strip-prefix=PREFIX`       |                                                                  | Strip the provided prefix from the path.                                                                                                                                                                 |
| `--csv-separator=SEPARATOR`        | `;`                                                              | Separator for the CSV output format.                                                                                                                                                                     |
| `--md-link-vars`                   |                                                                  | Link variables within usages to their definitions in the Markdown output format.                                                                                                                         |
| `--md-inline-code-wrap-vars`       |                                                                  | Wrap variables to show them as inline code in the Markdown output format.                                                                                                                                |
| `--md-inline-code-wrap-numbers`    |                                                                  | Wrap numbers to show them as inline code in the Markdown output format.                                                                                                                                  |
| `--md-inline-code-extra-file=FILE` |                                                                  | A path to a file that contains additional strings to be formatted as inline code in the Markdown output format. Multiple values allowed.                                                                 |
| `--md-block-template-file=FILE`    |                                                                  | A path to a Markdown template file used for Markdown blocks (md-blocks) output format. `{{ name }}`, `{{ description }}`, `{{ default_value }}` and `{{ path }}` tokens can be used within the template. |

## Maintenance

    composer install
    composer lint
    composer lint:fix
    composer test
