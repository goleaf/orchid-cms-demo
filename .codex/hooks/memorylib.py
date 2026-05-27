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

SKILL_ROOTS = [
    ".agents/skills",
    "skills",
    ".codex/skills",
]

PLUGIN_CONTAINERS = [
    "plugins",
    ".codex/plugins",
]

PLUGIN_MANIFESTS = [
    "plugin.json",
    ".codex-plugin/plugin.json",
]

SKILL_IGNORED_DIRS = {
    ".git",
    "__pycache__",
    ".pytest_cache",
    ".phpunit.cache",
    "build",
    "cache",
    "dist",
    "node_modules",
    "storage",
    "target",
    "vendor",
}

SKILL_PROMPT_TERMS = [
    "skill",
    "skills",
    "skill.md",
    "repository skill",
    "repo skill",
    "hook",
    "self-learning",
    "memory",
    "discovery",
]


def now_iso() -> str:
    return datetime.now(timezone.utc).isoformat(timespec="seconds")


def now_iso_z() -> str:
    return datetime.now(timezone.utc).isoformat(timespec="seconds").replace("+00:00", "Z")


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


def safe_write_text(path: Path, text: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(sanitize(text, max_len=max(len(text) + 100, 8000)), encoding="utf-8")


def safe_read_text(path: Path, max_bytes: int = 256_000) -> tuple[str, list[str]]:
    warnings: list[str] = []
    try:
        size = path.stat().st_size
    except OSError:
        return "", ["could not stat file"]

    if size > max_bytes:
        warnings.append(f"file larger than {max_bytes} bytes; read truncated")

    try:
        with path.open("r", encoding="utf-8", errors="ignore") as fh:
            return fh.read(max_bytes), warnings
    except OSError:
        return "", [*warnings, "could not read file"]


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


def path_relative_to(root: Path, path: Path) -> str:
    try:
        return path.resolve().relative_to(root.resolve()).as_posix()
    except ValueError:
        return path.resolve().as_posix()


def is_kebab_case(value: str) -> bool:
    return re.fullmatch(r"[a-z0-9]+(?:-[a-z0-9]+)*", value) is not None


def file_mtime(path: Path) -> str | None:
    try:
        return datetime.fromtimestamp(path.stat().st_mtime, timezone.utc).isoformat(timespec="seconds").replace("+00:00", "Z")
    except OSError:
        return None


def directory_has_files(path: Path) -> bool:
    if not path.is_dir():
        return False
    try:
        for item in path.rglob("*"):
            if any(part in SKILL_IGNORED_DIRS for part in item.parts):
                continue
            if item.is_file():
                return True
    except OSError:
        return False
    return False


def strip_metadata_value(value: str) -> str:
    value = value.strip()
    if len(value) >= 2 and value[0] == value[-1] and value[0] in {"'", '"'}:
        return value[1:-1].strip()
    return value


def first_useful_skill_line(body: str) -> str:
    for line in body.splitlines():
        stripped = line.strip()
        if not stripped or stripped in {"---", "```"}:
            continue
        if stripped.startswith("```"):
            continue
        stripped = stripped.lstrip("#").strip()
        stripped = stripped.lstrip("-*").strip()
        if stripped:
            return sanitize(stripped, max_len=240)
    return ""


def parse_skill_metadata(skill_md_path: Path) -> dict[str, Any]:
    text, warnings = safe_read_text(skill_md_path, max_bytes=128_000)
    metadata: dict[str, str] = {}
    body = text
    has_frontmatter = False

    if not text.strip():
        return {
            "metadata": metadata,
            "body_description": "",
            "warnings": [*warnings, "empty SKILL.md"],
        }

    lines = text.splitlines()
    if lines and lines[0].strip() == "---":
        has_frontmatter = True
        closing_index: int | None = None
        for index, line in enumerate(lines[1:], start=1):
            if line.strip() == "---":
                closing_index = index
                break

        if closing_index is None:
            warnings.append("invalid metadata format: frontmatter is not closed")
        else:
            for line in lines[1:closing_index]:
                stripped = line.strip()
                if not stripped or stripped.startswith("#"):
                    continue
                if ":" not in stripped:
                    warnings.append("invalid metadata format: expected key: value")
                    continue
                key, value = stripped.split(":", 1)
                key = key.strip().lower()
                if key in {"name", "description"}:
                    metadata[key] = strip_metadata_value(value)
            body = "\n".join(lines[closing_index + 1:])

    if has_frontmatter and not metadata:
        warnings.append("invalid metadata format: no supported metadata fields")

    return {
        "metadata": metadata,
        "body_description": first_useful_skill_line(body),
        "warnings": warnings,
    }


def has_plugin_manifest(path: Path) -> bool:
    return any((path / manifest).is_file() for manifest in PLUGIN_MANIFESTS)


def repository_skill_roots(root: Path) -> list[Path]:
    roots = [root / rel for rel in SKILL_ROOTS]

    if has_plugin_manifest(root):
        roots.extend([
            root / ".codex-plugin" / "skills",
            root / ".agents" / "plugins" / "skills",
        ])

    for container_rel in PLUGIN_CONTAINERS:
        container = root / container_rel
        if not container.is_dir():
            continue
        try:
            children = [child for child in container.iterdir() if child.is_dir()]
        except OSError:
            continue
        for child in children:
            if not has_plugin_manifest(child):
                continue
            roots.extend([
                child / "skills",
                child / ".agents" / "skills",
                child / ".codex" / "skills",
                child / ".codex-plugin" / "skills",
            ])

    unique: list[Path] = []
    seen: set[str] = set()
    for skill_root in roots:
        resolved = str(skill_root.resolve())
        if resolved not in seen:
            unique.append(skill_root)
            seen.add(resolved)
    return unique


def iter_skill_dirs(skill_root: Path, max_depth: int = 5) -> Iterable[Path]:
    if not skill_root.is_dir():
        return

    base_depth = len(skill_root.resolve().parts)
    for dirpath, dirnames, filenames in os.walk(skill_root):
        current = Path(dirpath)
        dirnames[:] = [
            dirname for dirname in dirnames
            if dirname not in SKILL_IGNORED_DIRS and not dirname.startswith(".cache")
        ]
        if len(current.resolve().parts) - base_depth >= max_depth:
            dirnames[:] = []
        if "SKILL.md" in filenames:
            yield current


def validate_skill_record(root: Path, expected_roots: list[Path], skill_dir: Path) -> dict[str, Any]:
    skill_md = skill_dir / "SKILL.md"
    parsed = parse_skill_metadata(skill_md)
    metadata = parsed["metadata"]
    warnings = list(parsed["warnings"])

    name = metadata.get("name") or skill_dir.name
    description = metadata.get("description") or parsed["body_description"]

    if "name" not in metadata:
        warnings.append("missing name metadata; inferred from directory name")
    if not name:
        warnings.append("missing name")
    elif not is_kebab_case(name):
        warnings.append("non-kebab-case skill name")

    if "description" not in metadata:
        warnings.append("missing description metadata")
    if not description:
        warnings.append("missing description")

    try:
        size = skill_md.stat().st_size
    except OSError:
        size = 0
    if size > 64_000:
        warnings.append("suspiciously large SKILL.md")

    scripts_dir = skill_dir / "scripts"
    references_dir = skill_dir / "references"
    assets_dir = skill_dir / "assets"

    if scripts_dir.is_dir() and not directory_has_files(scripts_dir):
        warnings.append("scripts directory exists but contains no scripts")
    if references_dir.is_dir() and not directory_has_files(references_dir):
        warnings.append("references directory exists but contains no references")

    inside_expected_root = False
    for expected_root in expected_roots:
        try:
            skill_dir.resolve().relative_to(expected_root.resolve())
            inside_expected_root = True
            break
        except ValueError:
            continue
    if not inside_expected_root:
        warnings.append("skill outside expected repository-local roots")

    valid = skill_md.is_file() and bool(name) and bool(description) and "empty SKILL.md" not in warnings

    return {
        "name": name,
        "description": description,
        "absolute_path": skill_dir.resolve().as_posix(),
        "path": path_relative_to(root, skill_dir),
        "absolute_skill_md": skill_md.resolve().as_posix(),
        "skill_md": path_relative_to(root, skill_md),
        "scripts_path": path_relative_to(root, scripts_dir) if scripts_dir.is_dir() else None,
        "references_path": path_relative_to(root, references_dir) if references_dir.is_dir() else None,
        "assets_path": path_relative_to(root, assets_dir) if assets_dir.is_dir() else None,
        "has_scripts": scripts_dir.is_dir(),
        "has_references": references_dir.is_dir(),
        "has_assets": assets_dir.is_dir(),
        "valid": valid,
        "warnings": sorted(set(warnings)),
        "mtime": file_mtime(skill_md),
    }


def discover_repository_skills(repo_root: str | Path | None = None) -> dict[str, Any]:
    root = Path(repo_root).resolve() if repo_root is not None else find_repo_root()
    skill_roots = repository_skill_roots(root)
    skills: list[dict[str, Any]] = []
    inventory_warnings: list[str] = []
    seen_dirs: set[str] = set()

    for skill_root in skill_roots:
        if not skill_root.exists():
            continue
        if not skill_root.is_dir():
            inventory_warnings.append(f"skill root is not a directory: {path_relative_to(root, skill_root)}")
            continue
        for skill_dir in iter_skill_dirs(skill_root):
            resolved = skill_dir.resolve().as_posix()
            if resolved in seen_dirs:
                continue
            seen_dirs.add(resolved)
            skills.append(validate_skill_record(root, skill_roots, skill_dir))

    names: dict[str, list[dict[str, Any]]] = {}
    for skill in skills:
        names.setdefault(str(skill.get("name") or ""), []).append(skill)

    for name, matching_skills in names.items():
        if not name or len(matching_skills) < 2:
            continue
        paths = [str(skill["path"]) for skill in matching_skills]
        warning = f"duplicate skill name: {name}"
        inventory_warnings.append(f"{warning} at {', '.join(paths)}")
        for skill in matching_skills:
            skill["warnings"] = sorted(set([*skill.get("warnings", []), warning]))

    skills.sort(key=lambda item: (str(item.get("name") or ""), str(item.get("path") or "")))

    return {
        "generated_at": now_iso_z(),
        "repository_root": root.as_posix(),
        "skill_roots_scanned": [path_relative_to(root, skill_root) for skill_root in skill_roots],
        "skills": skills,
        "warnings": sorted(set(inventory_warnings)),
    }


def skill_inventory_event(inventory: dict[str, Any], inventory_path: Path) -> dict[str, Any]:
    skills = inventory.get("skills") if isinstance(inventory.get("skills"), list) else []
    warning_count = len(inventory.get("warnings") or []) + sum(len(skill.get("warnings") or []) for skill in skills)
    return {
        "ts": now_iso_z(),
        "event": "repository_skill_discovery",
        "skills_found": len(skills),
        "valid_skills": sum(1 for skill in skills if skill.get("valid")),
        "warning_count": warning_count,
        "skill_names": [skill.get("name") for skill in skills if skill.get("name")],
        "inventory_path": str(inventory_path),
    }


def update_tool_notes_skill_summary(root: Path, inventory: dict[str, Any]) -> None:
    ensure_memory_files(root)
    path = memory_dir(root) / "tool_notes.md"
    existing = path.read_text(encoding="utf-8", errors="ignore") if path.exists() else "# Tool Notes\n\n"
    skills = inventory.get("skills") if isinstance(inventory.get("skills"), list) else []
    invalid = [skill for skill in skills if not skill.get("valid") or skill.get("warnings")]

    lines = [
        "## Repository skill discovery",
        "",
        f"- Last scan: {inventory.get('generated_at', '')}",
        f"- Skills found: {len(skills)}; valid: {sum(1 for skill in skills if skill.get('valid'))}.",
    ]
    if skills:
        lines.append("- Discovered skills: " + ", ".join(
            f"`{skill.get('name')}` ({skill.get('path')})" for skill in skills[:10]
        ) + ("." if len(skills) <= 10 else f", plus {len(skills) - 10} more."))
    else:
        lines.append("- Discovered skills: none.")

    if invalid:
        lines.append("- Recommended fixes: " + "; ".join(
            f"{skill.get('path')}: {', '.join((skill.get('warnings') or [])[:3])}" for skill in invalid[:6]
        ) + ("." if len(invalid) <= 6 else f"; plus {len(invalid) - 6} more."))
    elif inventory.get("warnings"):
        lines.append("- Recommended fixes: " + "; ".join(str(w) for w in inventory.get("warnings", [])[:6]) + ".")
    else:
        lines.append("- Recommended fixes: none.")

    lines.extend([
        "- Manual command: `python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json`.",
        "",
    ])
    section = "\n".join(lines)
    pattern = re.compile(r"\n?## Repository skill discovery\n.*?(?=\n## |\Z)", re.DOTALL)
    if pattern.search(existing):
        updated = pattern.sub("\n" + section.rstrip() + "\n", existing).rstrip() + "\n"
    else:
        updated = existing.rstrip() + "\n\n" + section
    safe_write_text(path, updated)


def write_skill_inventory(root: Path, inventory: dict[str, Any], update_notes: bool = True, append_event: bool = True) -> Path:
    mdir = memory_dir(root)
    mdir.mkdir(parents=True, exist_ok=True)
    inventory_path = mdir / "skill_inventory.json"
    inventory_path.write_text(
        json.dumps(inventory, ensure_ascii=False, separators=(",", ":"), default=str) + "\n",
        encoding="utf-8",
    )
    if append_event:
        try:
            append_jsonl(mdir / "events.jsonl", skill_inventory_event(inventory, inventory_path))
        except Exception:
            pass
    if update_notes:
        try:
            update_tool_notes_skill_summary(root, inventory)
        except Exception:
            pass
    return inventory_path


def load_skill_inventory(root: Path) -> dict[str, Any] | None:
    path = memory_dir(root) / "skill_inventory.json"
    if not path.exists():
        return None
    try:
        payload = json.loads(path.read_text(encoding="utf-8"))
        return payload if isinstance(payload, dict) else None
    except Exception:
        return None


def format_skill_inventory_for_context(inventory: dict[str, Any] | None, max_items: int = 10) -> str:
    if not inventory:
        return ""
    skills = inventory.get("skills") if isinstance(inventory.get("skills"), list) else []
    if not skills:
        return ""

    lines = ["Repository skill inventory:"]
    for skill in skills[:max_items]:
        description = str(skill.get("description") or "").strip()
        if len(description) > 180:
            description = description[:180] + "..."
        line = f"- {skill.get('name')}: {description} ({skill.get('path')})"
        warnings = [str(w) for w in (skill.get("warnings") or [])[:2]]
        if warnings:
            line += " [warnings: " + "; ".join(warnings) + "]"
        lines.append(line)
    if len(skills) > max_items:
        lines.append(f"- ... {len(skills) - max_items} more skills omitted")
    inventory_warnings = [str(w) for w in (inventory.get("warnings") or [])[:3]]
    if inventory_warnings:
        lines.append("Inventory warnings: " + "; ".join(inventory_warnings))
    return "\n".join(lines)


def skill_inventory_prompt_relevant(prompt: str) -> bool:
    prompt_l = prompt.lower()
    return any(term in prompt_l for term in SKILL_PROMPT_TERMS)


def skill_inventory_context(root: Path, refresh_if_missing: bool = True) -> str:
    inventory = load_skill_inventory(root)
    if inventory is None and refresh_if_missing:
        try:
            inventory = discover_repository_skills(root)
        except Exception as exc:
            return f"Repository skill inventory could not be loaded: {sanitize(str(exc), max_len=180)}"
    return format_skill_inventory_for_context(inventory)


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
