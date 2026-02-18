#!/usr/bin/env bash
##
# Test ignore directives.
#

VAR1=1
var=${VAR1}
# shellvar-ignore-next-line
var=$VAR2
var=${VAR3}
# shellvar-ignore-start
var=$VAR4
var=$VAR5
# shellvar-ignore-end
var=${VAR6}
