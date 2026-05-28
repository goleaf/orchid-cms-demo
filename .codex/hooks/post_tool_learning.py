#!/usr/bin/env python3
from __future__ import annotations

from memorylib import (
    append_jsonl,
    command_summary,
    ensure_memory_files,
    find_repo_root,
    git_branch,
    git_changed_files,
    looks_like_test_command,
    memory_dir,
    read_stdin_json,
    response_summary,
    self_learning_disabled,
)


def main() -> None:
    if self_learning_disabled():
        return

    data = read_stdin_json()
    root = find_repo_root(data.get("cwd"))
    ensure_memory_files(root)

    tool_name = str(data.get("tool_name") or "")
    command = command_summary(data.get("tool_input"))
    summary = response_summary(data.get("tool_response"))

    event = {
        "ts": data.get("ts") or None,
        "event": "PostToolUse",
        "session_id": data.get("session_id"),
        "turn_id": data.get("turn_id"),
        "tool_name": tool_name,
        "command": command,
        "is_test_command": looks_like_test_command(command),
        "response": summary,
        "branch": git_branch(root),
        "changed_files": git_changed_files(root)[:80],
    }
    append_jsonl(memory_dir(root) / "events.jsonl", event)

    # Do not emit context by default. This hook records evidence only.


if __name__ == "__main__":
    main()
