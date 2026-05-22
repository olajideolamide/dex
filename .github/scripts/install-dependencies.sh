#!/usr/bin/env bash
set -euo pipefail

DEPENDENCIES="${1:-highest}"

case "${DEPENDENCIES}" in
  lowest)
    composer update --prefer-lowest --prefer-dist --no-interaction --no-progress
    ;;
  highest)
    composer update --prefer-dist --no-interaction --no-progress
    ;;
  locked)
    composer install --prefer-dist --no-interaction --no-progress
    ;;
  *)
    echo "Unknown dependency mode: ${DEPENDENCIES}" >&2
    echo "Expected one of: lowest, highest, locked" >&2
    exit 1
    ;;
esac
