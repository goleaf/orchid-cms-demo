#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

mkdir -p "$ROOT/.agents" "$ROOT/.codex"
cp -R "$SCRIPT_DIR/.agents/"* "$ROOT/.agents/"
cp -R "$SCRIPT_DIR/.codex/"* "$ROOT/.codex/"

chmod +x "$ROOT/.codex/hooks/"*.py || true
chmod +x "$ROOT/.agents/skills/codebase-self-learning/scripts/"*.py || true

cat <<MSG
Installed codebase self-learning skill + hooks into:
$ROOT

Next steps:
1. Start Codex inside this repository.
2. Run /hooks in Codex.
3. Review and trust the hooks.
4. Optionally paste AGENTS.self-learning.snippet.md into your AGENTS.md.
MSG
