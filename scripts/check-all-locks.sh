#!/usr/bin/env bash
# Run every active lock check in sequence. Fails fast on the first
# violation. Used by the pre-commit hook to enforce all locks at once.
#
# Usage: bash scripts/check-all-locks.sh

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

bash "${REPO_ROOT}/scripts/check-free-lock.sh"
bash "${REPO_ROOT}/scripts/check-premium-lock.sh"

echo "All locks verified."
