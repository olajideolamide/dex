#!/usr/bin/env bash
set -euo pipefail

DEPENDENCIES="${1:-highest}"

case "${DEPENDENCIES}" in
  lowest)
    # Test against the minimum supported CI4 version, not just a generally low dependency set.
    rm -f composer.lock
    composer update codeigniter4/framework:4.3.5 --with-all-dependencies --prefer-lowest --prefer-stable --prefer-dist --no-interaction --no-progress
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
