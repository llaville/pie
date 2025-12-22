#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")/../../"

E2E_PATH="$(dirname "$0")"
DOCKERFILE="$E2E_PATH/Dockerfile"

if [[ ! -f "$DOCKERFILE" ]]; then
  echo "Dockerfile not found: $DOCKERFILE" >&2
  exit 1
fi

# Make a list of the build targets starting with test_
mapfile -t TARGETS < <(awk '
  tolower($1) == "from" {
    for (i = 1; i <= NF; i++) {
      if (tolower($i) == "as" && i < NF) {
        t = $(i+1)
        if (substr(t,1,5) == "test_") print t
      }
    }
  }
' "$DOCKERFILE")

if [[ ${#TARGETS[@]} -eq 0 ]]; then
  echo "No test_ targets found in $DOCKERFILE" >&2
  exit 1
fi

# If a specific target is provided as an argument, run only that target
if [[ $# -gt 0 ]]; then
  REQUESTED_TARGET="$1"
  # Verify the requested target exists among discovered test_ targets
  found=false
  for t in "${TARGETS[@]}"; do
    if [[ "$t" == "$REQUESTED_TARGET" ]]; then
      found=true
      break
    fi
  done
  if [[ $found == false ]]; then
    echo "Requested target '$REQUESTED_TARGET' not found in $DOCKERFILE" >&2
    echo "Available test_ targets:" >&2
    for t in "${TARGETS[@]}"; do
      echo "  - $t" >&2
    done
    exit 1
  fi
  TARGETS=("$REQUESTED_TARGET")
fi

PASSED=()
FAILED=()

for TARGET in "${TARGETS[@]}"; do
  echo "🧪 Running $TARGET"
  LOGFILE="$E2E_PATH/$TARGET.out"
  # Stream to console and to the logfile
  if docker buildx build --target="$TARGET" --file "$DOCKERFILE" . |& tee "$LOGFILE"; then
    PASSED+=("$TARGET")
    echo "✅ Passed $TARGET"
    rm "$LOGFILE"
  else
    FAILED+=("$TARGET")
    echo "❌ Failed $TARGET" >&2
  fi
  echo
done

echo "================ Summary ================"
echo "Total: ${#TARGETS[@]}  |  Passed: ${#PASSED[@]}  |  Failed: ${#FAILED[@]}"
if [[ ${#FAILED[@]} -gt 0 ]]; then
  echo "Failed targets:" >&2
  for f in "${FAILED[@]}"; do
    echo "  - $f" >&2
  done
  exit 1
fi

echo "All test targets passed."
