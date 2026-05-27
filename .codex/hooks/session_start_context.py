#!/usr/bin/env python3
from __future__ import annotations

from memorylib import build_memory_context, emit_context, ensure_memory_files, find_repo_root, read_stdin_json


def main() -> None:
    data = read_stdin_json()
    root = find_repo_root(data.get("cwd"))
    ensure_memory_files(root)
    context = build_memory_context(root)
    if context:
        emit_context("SessionStart", context)


if __name__ == "__main__":
    main()
