#!/usr/bin/env bash
##
# Test data with local variables.
#

# Global variable.
VAR1=val1

# Local variable.
local_var1=localval1

# Mixed-case variable.
Var2=val2

local_var2="${VAR1}-${local_var1}"
