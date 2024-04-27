#!/usr/bin/env bash
##
# Test data file for variables extraction script.
#
# shellcheck disable=SC2034,SC2154

CONTENT="## This is an automated message ##

Site ${DREVOPS_NOTIFY_EMAIL_PROJECT} \"${DREVOPS_NOTIFY_EMAIL_REF}\" branch has been deployed at ${timestamp} and is available at ${DREVOPS_NOTIFY_EMAIL_ENVIRONMENT_URL}.

Login at: ${DREVOPS_NOTIFY_EMAIL_ENVIRONMENT_URL}/user/login"

DREVOPS_DRUPAL_PRIVATE_FILES="${DREVOPS_DRUPAL_PRIVATE_FILES:-./${DREVOPS_WEBROOT}/sites/default/files/private}"

DREVOPS_TASK_LAGOON_PROJECT="${DREVOPS_TASK_LAGOON_PROJECT:-${LAGOON_PROJECT:-}}"

DREVOPS_SSH_FILE="${DREVOPS_SSH_FILE:-${HOME}/.ssh/id_rsa}"

DREVOPS_DOCTOR_CHECK_CONTAINERS="${DREVOPS_DOCTOR_CHECK_CONTAINERS:-0}"

OTHER="${OTHER:-1}"
