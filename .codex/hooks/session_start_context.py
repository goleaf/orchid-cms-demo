#!/usr/bin/env python3
from __future__ import annotations

from memorylib import (
    build_memory_context,
    emit_context,
    ensure_memory_files,
    find_repo_root,
    read_stdin_json,
    self_learning_disabled,
    skill_inventory_context,
)


def main() -> None:
    if self_learning_disabled():
        return

    data = read_stdin_json()
    root = find_repo_root(data.get("cwd"))
    ensure_memory_files(root)
    context_parts = [build_memory_context(root), skill_inventory_context(root)]
    context = "\n\n".join(part for part in context_parts if part)
    if context:
        emit_context("SessionStart", context)


if __name__ == "__main__":
    main()
