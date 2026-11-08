#!/usr/bin/env bash
# Moves files that are NOT part of this project out of the code folders, e.g.
# leftovers from an older app uploaded to the same public_html.
#
#   bash deploy/clean-stray-files.sh           # dry run: lists what would move
#   bash deploy/clean-stray-files.sh --apply   # moves them to ~/stray-files-<time>/
#
# Nothing is deleted. .env, vendor/, node_modules/, storage/ (uploads, logs),
# public/build and public/storage are never touched.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MANIFEST="$ROOT/deploy/manifest.txt"
[ -f "$MANIFEST" ] || { echo "Missing $MANIFEST: upload the deploy/ folder too." >&2; exit 1; }

DEST="$HOME/stray-files-$(date +%Y%m%d-%H%M%S)"
APPLY=0; [ "${1:-}" = "--apply" ] && APPLY=1
cd "$ROOT"

count=0
while IFS= read -r -d '' f; do
  f="${f#./}"
  grep -qxF -- "$f" "$MANIFEST" && continue
  count=$((count + 1))
  if [ "$APPLY" = 1 ]; then
    mkdir -p "$DEST/$(dirname "$f")"
    mv -- "$f" "$DEST/$f"
    echo "moved  $f"
  else
    echo "stray  $f"
  fi
done < <(find ./app ./bootstrap ./config ./database ./lang ./resources ./routes ./tests ./public \
           -type f \
           -not -path './public/build/*' -not -path './public/storage/*' \
           -not -path './bootstrap/cache/.gitignore' -print0 2>/dev/null)

if [ "$count" = 0 ]; then
  echo "No stray files."
elif [ "$APPLY" = 1 ]; then
  find ./app ./config ./database ./routes ./resources ./tests -type d -empty -delete 2>/dev/null || true
  echo "Moved $count file(s) to $DEST"
  echo "Next: composer dump-autoload -o && php artisan optimize:clear"
else
  echo "$count stray file(s). Run again with --apply to move them to $DEST"
fi
