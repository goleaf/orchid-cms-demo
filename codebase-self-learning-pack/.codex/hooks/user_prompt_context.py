#!/usr/bin/env python3
from __future__ import annotations

from memorylib import (
    append_jsonl,
    emit_context,
    ensure_memory_files,
    find_repo_root,
    hash_text,
    memory_dir,
    read_stdin_json,
    relevant_memory,
    sanitize,
)


def main() -> None:
    data = read_stdin_json()
    root = find_repo_root(data.get("cwd"))
    ensure_memory_files(root)

    prompt = str(data.get("prompt") or "")
    # Privacy-friendly prompt index: store only hash and short sanitized preview.
    append_jsonl(memory_dir(root) / "prompt_index.jsonl", {
        "ts": data.get("ts") or None,
        "event": "UserPromptSubmit",
        "session_id": data.get("session_id"),
        "turn_id": data.get("turn_id"),
        "prompt_hash": hash_text(prompt),
        "prompt_preview": sanitize(prompt, max_len=220),
    })

    matches = relevant_memory(root, prompt)
    if matches:
        context = "Relevant self-learning memory for this prompt:\n" + "\n".join(matches)
        emit_context("UserPromptSubmit", context)


if __name__ == "__main__":
    main()
