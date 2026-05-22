#!/usr/bin/env bash
set -euo pipefail

DEPENDENCIES="${1:-highest}"

case "${DEPENDENCIES}" in
  lowest)
    # Ensure a true "lowest" resolution (i.e., do not reuse a previously-generated lockfile).
    rm -f composer.lock
    composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction --no-progress
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
