#!/usr/bin/env bash
##
# Test ignore directive edge cases.
#

# Edge case 1: ignore-next-line followed by ignore block.
# The next-line flag should be cleared by the block start.
# shellvar-ignore-next-line
# shellvar-ignore-start
var=$VAR1
# shellvar-ignore-end
var=$VAR2

# Edge case 2: ignore-next-line before ignore block end.
# shellvar-ignore-start
var=$VAR3
# shellvar-ignore-next-line
# shellvar-ignore-end
var=$VAR4

# Edge case 3: consecutive ignore-next-line directives.
# Only the last one should apply (to the next non-directive line).
# shellvar-ignore-next-line
# shellvar-ignore-next-line
var=$VAR5
var=$VAR6
