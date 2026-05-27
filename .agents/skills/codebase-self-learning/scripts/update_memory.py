#!/usr/bin/env python3
"""Add reviewed project memory or learning candidates.

Usage examples:

python3 .agents/skills/codebase-self-learning/scripts/update_memory.py \
  --category rules \
  --fact "Visible labels must use tkey()." \
  --evidence "User requirement + existing localization helper."

python3 .agents/skills/codebase-self-learning/scripts/update_memory.py \
  --candidate \
  --category learned_patterns \
  --fact "Possible convention: service classes own business logic." \
  --evidence "Observed in App/Services." 
"""
from __future__ import annotations

import argparse
import re
import subprocess
from datetime import datetime, timezone
from pathlib import Path

CATEGORY_FILES = {
    "rules": "rules.md",
    "learned_patterns": "learned_patterns.md",
    "decisions": "decisions.md",
    "recurring_mistakes": "recurring_mistakes.md",
    "tool_notes": "tool_notes.md",
}

SECRET_PATTERNS = [
    re.compile(r"(?i)(api[_-]?key|secret|token|password|passwd|pwd|bearer)\s*[:=]\s*['\"]?[^'\"\s]+"),
    re.compile(r"(?i)authorization:\s*bearer\s+[a-z0-9._~+/=-]+"),
    re.compile(r"\b[A-Za-z0-9+/]{40,}={0,2}\b"),
    re.compile(r"\b[0-9a-fA-F]{40,}\b"),
]


def run(cmd: list[str], cwd: Path | None = None) -> str:
    try:
        return subprocess.run(cmd, cwd=cwd, capture_output=True, text=True, check=False).stdout.strip()
    except Exception:
        return ""


def repo_root() -> Path:
    root = run(["git", "rev-parse", "--show-toplevel"])
    return Path(root).resolve() if root else Path.cwd().resolve()


def sanitize(text: str) -> str:
    value = text.strip()
    for pattern in SECRET_PATTERNS:
        value = pattern.sub("[REDACTED_SECRET]", value)
    value = re.sub(r"[\w.+-]+@[\w.-]+\.[a-zA-Z]{2,}", "[REDACTED_EMAIL]", value)
    return value


def ensure_file(path: Path, title: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    if not path.exists():
        path.write_text(f"# {title}\n\n", encoding="utf-8")


def already_exists(path: Path, fact: str) -> bool:
    if not path.exists():
        return False
    existing = path.read_text(encoding="utf-8", errors="ignore").lower()
    normalized = fact.lower().strip(" .")
    return normalized in existing


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--category", required=True, choices=sorted(CATEGORY_FILES.keys()))
    parser.add_argument("--fact", required=True)
    parser.add_argument("--evidence", required=True)
    parser.add_argument("--candidate", action="store_true")
    args = parser.parse_args()

    root = repo_root()
    memory_dir = root / ".codex" / "memory"
    memory_dir.mkdir(parents=True, exist_ok=True)

    fact = sanitize(args.fact)
    evidence = sanitize(args.evidence)
    ts = datetime.now(timezone.utc).isoformat(timespec="seconds")

    if args.candidate:
        path = memory_dir / "learning_candidates.md"
        ensure_file(path, "Learning Candidates")
        entry = f"\n- [{ts}] Candidate `{args.category}`: {fact}\n  Evidence: {evidence}\n"
    else:
        path = memory_dir / CATEGORY_FILES[args.category]
        ensure_file(path, args.category.replace("_", " ").title())
        if already_exists(path, fact):
            print(f"Memory already exists in {path}")
            return
        entry = f"\n- {fact}\n  Evidence: {evidence}\n  Added: {ts}\n"

    with path.open("a", encoding="utf-8") as fh:
        fh.write(entry)
    print(f"Updated {path}")


if __name__ == "__main__":
    main()
