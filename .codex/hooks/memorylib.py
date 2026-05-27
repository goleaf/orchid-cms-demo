#!/usr/bin/env python3
"""Shared helpers for Codex self-learning hooks.

This module is intentionally dependency-free. It avoids network access, avoids
storing secrets, and keeps all memory local to the repository.
"""
from __future__ import annotations

import hashlib
import json
import os
import re
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Iterable

MEMORY_FILES = {
    "project_brief": "project_brief.md",
    "rules": "rules.md",
    "learned_patterns": "learned_patterns.md",
    "decisions": "decisions.md",
    "recurring_mistakes": "recurring_mistakes.md",
    "tool_notes": "tool_notes.md",
    "learning_candidates": "learning_candidates.md",
}

SECRET_PATTERNS = [
    re.compile(r"(?i)(api[_-]?key|secret|token|password|passwd|pwd|bearer)\s*[:=]\s*['\"]?[^'\"\s]+"),
    re.compile(r"(?i)authorization:\s*bearer\s+[a-z0-9._~+/=-]+"),
    re.compile(r"\b[A-Za-z0-9+/]{40,}={0,2}\b"),
    re.compile(r"\b[0-9a-fA-F]{40,}\b"),
]

PII_PATTERNS = [
    re.compile(r"[\w.+-]+@[\w.-]+\.[a-zA-Z]{2,}"),
]


def now_iso() -> str:
    return datetime.now(timezone.utc).isoformat(timespec="seconds")


def read_stdin_json() -> dict[str, Any]:
    raw = sys.stdin.read()
    if not raw.strip():
        return {}
    try:
        data = json.loads(raw)
        return data if isinstance(data, dict) else {}
    except json.JSONDecodeError:
        return {}


def emit_context(event_name: str, text: str) -> None:
    if not text.strip():
        return
    print(json.dumps({
        "hookSpecificOutput": {
            "hookEventName": event_name,
            "additionalContext": text.strip(),
        }
    }))


def emit_common(system_message: str | None = None, continue_: bool = True) -> None:
    payload: dict[str, Any] = {"continue": continue_}
    if system_message:
        payload["systemMessage"] = system_message
    print(json.dumps(payload))


def run(cmd: list[str], cwd: Path | None = None, timeout: int = 5) -> str:
    try:
        result = subprocess.run(
            cmd,
            cwd=str(cwd) if cwd else None,
            capture_output=True,
            text=True,
            timeout=timeout,
            check=False,
        )
        return (result.stdout or "").strip()
    except Exception:
        return ""


def find_repo_root(cwd: str | None = None) -> Path:
    start = Path(cwd or os.getcwd()).resolve()
    git_root = run(["git", "rev-parse", "--show-toplevel"], cwd=start)
    if git_root:
        return Path(git_root).resolve()
    return start


def memory_dir(root: Path) -> Path:
    return root / ".codex" / "memory"


def ensure_memory_files(root: Path) -> None:
    mdir = memory_dir(root)
    mdir.mkdir(parents=True, exist_ok=True)
    defaults = {
        "project_brief.md": "# Project Brief\n\nAdd stable high-level project context here.\n",
        "rules.md": "# Rules\n\n- Do not store secrets or private personal data in memory.\n",
        "learned_patterns.md": "# Learned Patterns\n\n",
        "decisions.md": "# Decisions\n\n",
        "recurring_mistakes.md": "# Recurring Mistakes\n\n",
        "tool_notes.md": "# Tool Notes\n\n",
        "learning_candidates.md": "# Learning Candidates\n\nCandidates are unreviewed. Promote only after evidence.\n",
        "events.jsonl": "",
        "prompt_index.jsonl": "",
    }
    for filename, content in defaults.items():
        path = mdir / filename
        if not path.exists():
            path.write_text(content, encoding="utf-8")


def sanitize(text: Any, max_len: int = 3000) -> str:
    value = json.dumps(text, ensure_ascii=False, default=str) if not isinstance(text, str) else text
    for pattern in SECRET_PATTERNS:
        value = pattern.sub("[REDACTED_SECRET]", value)
    for pattern in PII_PATTERNS:
        value = pattern.sub("[REDACTED_EMAIL]", value)
    value = value.replace("\x00", "")
    if len(value) > max_len:
        value = value[:max_len] + "…[truncated]"
    return value


def append_jsonl(path: Path, payload: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    safe_payload = json.loads(sanitize(payload, max_len=10000)) if isinstance(payload, dict) else payload
    with path.open("a", encoding="utf-8") as fh:
        fh.write(json.dumps(safe_payload, ensure_ascii=False, default=str) + "\n")


def safe_append(path: Path, text: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("a", encoding="utf-8") as fh:
        fh.write(sanitize(text, max_len=8000))


def git_changed_files(root: Path) -> list[str]:
    output = run(["git", "status", "--porcelain"], cwd=root)
    files: list[str] = []
    for line in output.splitlines():
        if not line.strip():
            continue
        # Porcelain format: XY path OR XY old -> new
        path = line[3:].strip()
        if " -> " in path:
            path = path.split(" -> ")[-1]
        if path:
            files.append(path)
    return sorted(set(files))


def git_branch(root: Path) -> str | None:
    branch = run(["git", "branch", "--show-current"], cwd=root)
    return branch or None


def hash_text(text: str) -> str:
    return hashlib.sha256(text.encode("utf-8", errors="ignore")).hexdigest()[:16]


def read_memory_file(root: Path, filename: str, limit: int = 4000) -> str:
    path = memory_dir(root) / filename
    if not path.exists():
        return ""
    try:
        text = path.read_text(encoding="utf-8")
    except Exception:
        return ""
    return text[:limit]


def build_memory_context(root: Path, max_chars: int = 9000) -> str:
    ensure_memory_files(root)
    parts: list[str] = []
    ordered = [
        ("Project brief", "project_brief.md", 1800),
        ("Rules", "rules.md", 2200),
        ("Decisions", "decisions.md", 1800),
        ("Learned patterns", "learned_patterns.md", 2200),
        ("Recurring mistakes", "recurring_mistakes.md", 1400),
        ("Tool notes", "tool_notes.md", 1000),
    ]
    for title, filename, limit in ordered:
        body = read_memory_file(root, filename, limit=limit).strip()
        if body:
            parts.append(f"## {title}\n{body}")
    context = "\n\n".join(parts).strip()
    if len(context) > max_chars:
        context = context[:max_chars] + "\n…[memory truncated]"
    if not context:
        return ""
    return (
        "Repository self-learning memory is available. Apply it only when it matches current code evidence.\n\n"
        + context
    )


def iter_memory_lines(root: Path) -> Iterable[tuple[str, str]]:
    for filename in [
        "rules.md",
        "decisions.md",
        "learned_patterns.md",
        "recurring_mistakes.md",
        "tool_notes.md",
    ]:
        path = memory_dir(root) / filename
        if not path.exists():
            continue
        try:
            for line in path.read_text(encoding="utf-8").splitlines():
                stripped = line.strip()
                if stripped.startswith("-") or stripped.startswith("*"):
                    yield filename, stripped
        except Exception:
            continue


def relevant_memory(root: Path, prompt: str, max_items: int = 12) -> list[str]:
    prompt_l = prompt.lower()
    words = {w for w in re.findall(r"[a-zA-Zа-яА-ЯёЁ0-9_]{4,}", prompt_l)}
    scored: list[tuple[int, str, str]] = []
    for filename, line in iter_memory_lines(root):
        line_l = line.lower()
        score = sum(1 for w in words if w in line_l)
        # Always surface hard project constraints.
        if any(term in line_l for term in ["not saas", "не saas", "no saas", "translation", "tkey", "orchid"]):
            score += 2
        if score > 0:
            scored.append((score, filename, line))
    scored.sort(reverse=True, key=lambda item: item[0])
    return [f"{line} ({filename})" for _, filename, line in scored[:max_items]]


def command_summary(tool_input: Any) -> str:
    if isinstance(tool_input, dict):
        command = tool_input.get("command") or tool_input.get("cmd") or ""
        if isinstance(command, list):
            command = " ".join(str(x) for x in command)
        return sanitize(str(command), max_len=400)
    return sanitize(tool_input, max_len=400)


def response_summary(tool_response: Any) -> dict[str, Any]:
    text = sanitize(tool_response, max_len=1200)
    lower = text.lower()
    return {
        "hash": hash_text(text),
        "mentions_failure": any(term in lower for term in ["failed", "failure", "error", "exception", "fatal", "ошибка"]),
        "mentions_success": any(term in lower for term in ["passed", "success", "ok", "green", "успешно"]),
        "sample": text[:500],
    }


def looks_like_test_command(command: str) -> bool:
    lower = command.lower()
    markers = [
        "php artisan test",
        "pest",
        "phpunit",
        "npm test",
        "pnpm test",
        "yarn test",
        "pytest",
        "go test",
        "cargo test",
        "mvn test",
        "gradle test",
    ]
    return any(marker in lower for marker in markers)
