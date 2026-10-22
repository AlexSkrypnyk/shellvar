<p align="center">
  <a href="" rel="noopener">
  <img width=200px height=200px src="https://placehold.jp/000000/ffffff/200x200.png?text=Shellvar&css=%7B%22border-radius%22%3A%22%20100px%22%7D" alt="Shellvar logo"></a>
</p>

<h1 align="center">Utility to work with shell variables</h1>

<div align="center">

[![GitHub Issues](https://img.shields.io/github/issues/drevops/shellvar.svg)](https://github.com/drevops/shellvar/issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/drevops/shellvar.svg)](https://github.com/drevops/shellvar/pulls)
[![Test PHP](https://github.com/drevops/shellvar/actions/workflows/test-php.yml/badge.svg)](https://github.com/drevops/shellvar/actions/workflows/test-php.yml)
[![codecov](https://codecov.io/gh/drevops/shellvar/graph/badge.svg)](https://codecov.io/gh/drevops/shellvar)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/drevops/shellvar)
![LICENSE](https://img.shields.io/github/license/drevops/shellvar)
![Renovate](https://img.shields.io/badge/renovate-enabled-green?logo=renovatebot)

[![Docker Pulls](https://img.shields.io/docker/pulls/drevops/shellvar?logo=docker)](https://hub.docker.com/r/drevops/shellvar)
![amd64](https://img.shields.io/badge/arch-linux%2Famd64-brightgreen)

</div>

---

## Features

- 🔎 Lint variables:
  - Report shell variables that are not in `${VAR}` format.
  - Fix shell variables that are not in `${VAR}` format.
  - Skip lines or blocks marked with `# shellvar-ignore-*` directives.
- 📋 Extract variables:
  - Scan a file or a directory containing shell scripts and extract found variables with comments and assigned values with [advanced interpolation support](tests/phpunit/Fixtures/test-data-real.sh).
  - Filter variables: exclude local, exclude by prefix, exclude from a list in a file.
  - Format output as _CSV_, a _Markdown table_ or _Markdown blocks_ (defined in a template).
  - Reorder, select or exclude columns in tabular output.

## Installation

Download the latest version of `shellvar` from the [releases page](https://github.com/drevops/shellvar/releases) and run it on the host machine:

```bash
./shellvar [command] [options] path/to/script.sh
```

or run it as a Docker container:

```bash
docker run -v $(pwd):/app drevops/shellvar [command] [options] path/to/script.sh
```

## Usage - Lint

- Report shell variables that are not in `${VAR}` format.
- Fix shell variables that are not in `${VAR}` format.

The command exits with a non-zero code when it reports at least 1 variable, so it can gate a CI job.

### Lint file or directory

```bash
./shellvar lint path/to/script.sh

./shellvar lint path/to/dir

./shellvar lint --extension=sh --extension=bash --extension=bats \
  path/to/dir
```

Directories are scanned recursively for files with the given extensions.

### Fix file or directory

```bash
./shellvar lint --fix path/to/script.sh

./shellvar lint --fix path/to/dir

./shellvar lint --fix --extension=sh --extension=bash --extension=bats \
  path/to/dir
```

### Ignore lines

Use comment directives to skip a single line or a block of lines:

```bash
# shellvar-ignore-next-line
var=$VAR1

# shellvar-ignore-start
var=$VAR2
var=$VAR3
# shellvar-ignore-end
```

### Lint options

| Name                          | Description                                                                      | Default value |
|-------------------------------|----------------------------------------------------------------------------------|---------------|
| `path`                        | File or directory to check.                                                      |               |
| `-e`, `--extension=EXTENSION` | File extension to filter by when `path` is a directory. Multiple values allowed. | `sh`, `bash`  |
| `-f`, `--fix`                 | Fixes the variables in the file instead of reporting them.                       |               |

## Usage - Extract

By default, variable names, descriptions (taken from the comments) and their values are printed to STDOUT in the _CSV_ format. You can also change the output format to _Markdown table_ or _Markdown blocks_.

Given the following shell script (see the [extended example](tests/phpunit/Fixtures/test-data.sh) used in tests):

```bash
# Assignment to scalar value.
VAR1=val1
# Assignment to another variable.
VAR2="${VAR1}"
# Parameter expansion.
VAR3=${val3:-abc}
# Parameter expansion with a default value using
# another variable $VAR3.
#
# Continuation of the multi-line comment.
VAR4=${val4:-$VAR3}
```

### CSV (default)

```bash
./shellvar extract path/to/script.sh
```

```csv
Name;"Default value";Description
VAR1;val1;"Assignment to scalar value."
VAR2;${VAR1};"Assignment to another variable."
VAR3;abc;"Parameter expansion."
VAR4;${VAR3};"Parameter expansion with a default value using another variable $VAR3.

Continuation of the multi-line comment."
```

### Markdown table

```bash
./shellvar extract --format=md-table path/to/script.sh
```

<details>
  <summary>Click to see output</summary>

```markdown
| Name   | Default value | Description                                                                                                               |
|--------|---------------|---------------------------------------------------------------------------------------------------------------------------|
| `VAR1` | `val1`        | Assignment to scalar value.                                                                                               |
| `VAR2` | `${VAR1}`     | Assignment to another variable.                                                                                           |
| `VAR3` | `abc`         | Parameter expansion.                                                                                                      |
| `VAR4` | `${VAR3}`     | Parameter expansion with a default value using another variable `$VAR3`.<br/><br/>Continuation of the multi-line comment. |
```

which renders as

| Name   | Default value | Description                                                                                                               |
|--------|---------------|---------------------------------------------------------------------------------------------------------------------------|
| `VAR1` | `val1`        | Assignment to scalar value.                                                                                               |
| `VAR2` | `${VAR1}`     | Assignment to another variable.                                                                                           |
| `VAR3` | `abc`         | Parameter expansion.                                                                                                      |
| `VAR4` | `${VAR3}`     | Parameter expansion with a default value using another variable `$VAR3`.<br/><br/>Continuation of the multi-line comment. |

</details>

### Markdown blocks

```bash
./shellvar extract --format=md-blocks path/to/script.sh
```

<details>
  <summary>Click to see output</summary>

```markdown
### `VAR1`

Assignment to scalar value.

Default value: `val1`

### `VAR2`

Assignment to another variable.

Default value: `${VAR1}`

### `VAR3`

Parameter expansion.

Default value: `abc`

### `VAR4`

Parameter expansion with a default value using<br/>another variable `$VAR3`.

Continuation of the multi-line comment.

Default value: `${VAR3}`
```

which renders as

<table><tr><td>

### `VAR1`

Assignment to scalar value.

Default value: `val1`

### `VAR2`

Assignment to another variable.

Default value: `${VAR1}`

### `VAR3`

Parameter expansion.

Default value: `abc`

### `VAR4`

Parameter expansion with a default value using<br/>another variable `$VAR3`.

Continuation of the multi-line comment.

Default value: `${VAR3}`

</td></tr></table>

</details>

#### Advanced: Markdown blocks with links and custom template

Given a template file `template.md`:

```markdown
### {{ name }}

{{ description }}

Default value: {{ default_value }}

Path: {{ path }}

Paths: {{ paths }}
```

and the preceding script saved as `1.sh` in the current directory:

```bash
./shellvar extract \
  --format=md-blocks \
  --md-link-vars \
  --md-block-template-file=template.md \
  --path-strip-prefix="${PWD}/" \
  1.sh
```

<details>
  <summary>Click to see output</summary>

```markdown
### `VAR1`

Assignment to scalar value.

Default value: `val1`

Path: `1.sh`

Paths: `1.sh`

### `VAR2`

Assignment to another variable.

Default value: `${VAR1}`

Path: `1.sh`

Paths: `1.sh`

### `VAR3`

Parameter expansion.

Default value: `abc`

Path: `1.sh`

Paths: `1.sh`

### `VAR4`

Parameter expansion with a default value using<br/>another variable [`$VAR3`](#VAR3).

Continuation of the multi-line comment.

Default value: `${VAR3}`

Path: `1.sh`

Paths: `1.sh`
```

which renders as

<table><tr><td>

### `VAR1`

Assignment to scalar value.

Default value: `val1`

Path: `1.sh`

Paths: `1.sh`

### `VAR2`

Assignment to another variable.

Default value: `${VAR1}`

Path: `1.sh`

Paths: `1.sh`

### `VAR3`

Parameter expansion.

Default value: `abc`

Path: `1.sh`

Paths: `1.sh`

### `VAR4`

Parameter expansion with a default value using<br/>another variable [`$VAR3`](#VAR3).

Continuation of the multi-line comment.

Default value: `${VAR3}`

Path: `1.sh`

Paths: `1.sh`

</td></tr></table>

</details>

### Column manipulation

Control the order and visibility of columns in tabular output formats (CSV and Markdown table).

#### Reorder columns

```bash
./shellvar extract --format=csv \
  --column-order="Description,Name" \
  path/to/script.sh
```

This outputs columns in the order: Description, Name, Default value (unspecified columns are appended).

#### Show only specific columns

```bash
./shellvar extract --format=md-table \
  --only-columns="Name,Description" \
  path/to/script.sh
```

This outputs only the Name and Description columns.

#### Exclude columns

```bash
./shellvar extract --format=csv \
  --exclude-columns="Default value" \
  path/to/script.sh
```

This outputs all columns except "Default value".

#### Combine column options

```bash
./shellvar extract --format=csv \
  --only-columns="Name,Description,Default value" \
  --column-order="Description,Name" \
  path/to/script.sh
```

This first filters to only the specified columns, then reorders them.

### Options

There are options for different phases: extraction, filtering and formatting.

"Multiple values allowed" means that you can specify the option multiple times like so: `--exclude-prefix=VAR1 --exclude-prefix=VAR2` etc.

#### Extraction

| Name                               | Description                                                                                                                                                          | Default value |
|------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------|
| `paths`                            | File or directory to scan. Multiple paths separated by space. Directories are scanned non-recursively for `.env`, `*.sh` and `*.bash` files, including hidden ones. |               |
| `--skip-text=SKIP`                 | Skip variable extraction if the comment has this specified text.                                                                                                     | `@skip`       |
| `--skip-description-prefix=PREFIX` | Skip description lines that start with the provided prefix. Multiple values allowed.                                                                                 |               |

#### Filtering

| Name                       | Description                                                                                                   | Default value |
|----------------------------|---------------------------------------------------------------------------------------------------------------|---------------|
| `--exclude-local`          | Remove local variables.                                                                                       |               |
| `--exclude-prefix=PREFIX`  | Exclude variables that start with the provided prefix. Multiple values allowed.                               |               |
| `--exclude-from-file=FILE` | A path to a file that contains variables to be excluded from the extraction process. Multiple values allowed. |               |

#### Format

| Name                               | Description                                                                                                                                                                                                             | Default value                                                     |
|------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------|
| `--format=FORMAT`                  | The output format: CSV (`csv`), Markdown table (`md-table`), Markdown blocks (`md-blocks`).                                                                                                                             | `csv`                                                             |
| `--sort`                           | Sort variables in ascending order by name.                                                                                                                                                                              |                                                                   |
| `--unset`                          | A string to represent a value for variables that are defined but have no set value.                                                                                                                                     | `UNSET`                                                           |
| `--fields=FIELDS`                  | Semicolon-separated list of fields. Each field is a key-label pair in the format "key=label". If label is omitted, key is used as label.                                                                                | `name=Name;default_value="Default value";description=Description` |
| `--path-strip-prefix=PREFIX`       | Strip the provided prefix from the path.                                                                                                                                                                                |                                                                   |
| `--csv-separator=SEPARATOR`        | Separator for the CSV output format.                                                                                                                                                                                    | `;`                                                               |
| `--column-order=COLUMNS`           | Comma-separated list of column names to specify output order. Columns not specified are appended in their original order. Only applies to tabular formats (csv, md-table).                                              |                                                                   |
| `--only-columns=COLUMNS`           | Comma-separated list of column names to include. Only these columns will appear in output. Only applies to tabular formats (csv, md-table).                                                                             |                                                                   |
| `--exclude-columns=COLUMNS`        | Comma-separated list of column names to exclude from output. Only applies to tabular formats (csv, md-table).                                                                                                           |                                                                   |
| `--md-link-vars`                   | Link variables within usages to their definitions in the Markdown output format.                                                                                                                                        |                                                                   |
| `--md-link-vars-anchor-case`       | The case of the anchors when linking variables. Can be one of "preserve", "lower" or "upper".                                                                                                                           | `preserve`                                                        |
| `--md-no-inline-code-wrap-vars`    | Do not wrap variables to show them as inline code in the Markdown output format.                                                                                                                                        |                                                                   |
| `--md-no-inline-code-wrap-numbers` | Do not wrap numbers to show them as inline code in the Markdown output format.                                                                                                                                          |                                                                   |
| `--md-inline-code-extra-file=FILE` | A path to a file that contains additional strings to be formatted as inline code in the Markdown output format. Multiple values allowed.                                                                                |                                                                   |
| `--md-block-template-file=FILE`    | A path to a Markdown template file used for Markdown blocks (md-blocks) output format. `{{ name }}`, `{{ description }}`, `{{ default_value }}`, `{{ path }}` and `{{ paths }}` tokens can be used within the template. |                                                                   |

## Contributing

[`CONTRIBUTING.md`](CONTRIBUTING.md) covers local development setup, the lint and test commands and how the Docker image is published.

---
_This repository was created using the [Scaffold](https://getscaffold.dev/) project template_
