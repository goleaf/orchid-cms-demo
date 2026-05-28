#!/usr/bin/env python3
from __future__ import annotations

import json
from collections import Counter

from memorylib import (
    emit_common,
    ensure_memory_files,
    find_repo_root,
    git_branch,
    git_changed_files,
    memory_dir,
    now_iso,
    read_stdin_json,
    safe_append,
    self_learning_disabled,
)


def classify_file(path: str) -> str:
    lower = path.lower()
    if lower.endswith(("test.php", ".spec.ts", ".test.ts", ".spec.js", ".test.js")) or "/tests/" in lower or lower.startswith("tests/"):
        return "tests"
    if "migration" in lower or "/database/" in lower:
        return "database"
    if "/orchid/" in lower or "platformprovider" in lower:
        return "orchid"
    if "/resources/views/" in lower:
        return "views"
    if "/routes/" in lower:
        return "routes"
    if "/app/services/" in lower or "/services/" in lower:
        return "services"
    if "/app/models/" in lower or "/models/" in lower:
        return "models"
    return "code"


def main() -> None:
    if self_learning_disabled():
        emit_common()
        return

    data = read_stdin_json()
    root = find_repo_root(data.get("cwd"))
    ensure_memory_files(root)

    changed = git_changed_files(root)
    if changed:
        counts = Counter(classify_file(path) for path in changed)
        candidate = {
            "ts": now_iso(),
            "event": "Stop",
            "session_id": data.get("session_id"),
            "turn_id": data.get("turn_id"),
            "branch": git_branch(root),
            "changed_file_count": len(changed),
            "changed_file_groups": dict(counts),
            "changed_files_sample": changed[:30],
            "note": "Review this task for stable project conventions before promoting anything to memory.",
        }
        text = "\n## Candidate from task ending at {ts}\n\n```json\n{payload}\n```\n".format(
            ts=candidate["ts"],
            payload=json.dumps(candidate, ensure_ascii=False, indent=2),
        )
        safe_append(memory_dir(root) / "learning_candidates.md", text)

    # Stop hooks must output valid JSON if they output anything.
    emit_common(system_message="Self-learning hook recorded task evidence locally." if changed else None)


if __name__ == "__main__":
    main()
