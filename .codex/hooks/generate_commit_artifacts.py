#!/usr/bin/env python3
from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import sys
import tempfile
from datetime import datetime
from pathlib import Path
from typing import Any


DEFAULT_SUBJECT = "chore: update project changes"
MAX_DIFF_CHARS = 120_000


def run(
    cmd: list[str],
    cwd: Path,
    timeout: int = 60,
    env: dict[str, str] | None = None,
    input_data: str | None = None,
) -> subprocess.CompletedProcess[str]:
    return subprocess.run(
        cmd,
        cwd=str(cwd),
        env=env,
        input=input_data,
        text=True,
        capture_output=True,
        timeout=timeout,
        check=False,
    )


def git(repo: Path, args: list[str], timeout: int = 60) -> str:
    result = run(["git", *args], repo, timeout=timeout)
    return (result.stdout or "").strip()


def clean_subject(value: str) -> str:
    subject = " ".join((value or "").strip().split())
    subject = subject.rstrip(".")
    return subject


def conventional_subject_is_valid(repo: Path, subject: str) -> bool:
    validator = Path.home() / ".codex" / "skills" / "conventional-commits" / "scripts" / "check_commit_message.py"
    if not validator.exists():
        return bool(re.match(r"^[a-z]+(?:\([a-z0-9._-]+\))?!?: .+", subject)) and len(subject) <= 72

    result = run(["python3", str(validator), subject], repo, timeout=10)
    return result.returncode == 0


def sanitize_changelog_entry(value: str) -> str:
    entry = " ".join((value or "").strip().split())
    entry = re.sub(r"\[([^\]]+)\]\([^)]+\)", r"\1", entry)
    entry = entry.replace("`", "")
    entry = re.sub(r"\b[\w./\\-]+\.(php|js|ts|css|scss|json|md|toml|yml|yaml|xml|lock)\b", "", entry)
    entry = re.sub(r"\b[A-Z][A-Za-z0-9_]+(::|->)[A-Za-z0-9_]+\b", "", entry)
    entry = re.sub(r"\bApp\\[A-Za-z0-9_\\]+\b", "", entry)
    entry = re.sub(r"https?://\S+", "", entry)
    entry = " ".join(entry.split(" -")).strip(" -")
    return entry.rstrip(".")


def fallback_entries(name_status: str) -> list[str]:
    changed = [line for line in name_status.splitlines() if line.strip()]
    if not changed:
        return ["Updated the project workspace."]
    if any(".codex/" in line or "codex-automation" in line for line in changed):
        return ["Improved the automated project workflow."]
    if any("docs/" in line or "README" in line or "AGENTS" in line for line in changed):
        return ["Improved the project documentation and working guidance."]
    if any("tests/" in line for line in changed):
        return ["Expanded project verification coverage."]
    return ["Updated the application workflow and supporting project files."]


def fallback_subject(name_status: str) -> str:
    changed = [line for line in name_status.splitlines() if line.strip()]
    if any(".codex/" in line or "codex-automation" in line for line in changed):
        return "chore(codex): improve automation hooks"
    if any("docs/" in line or "README" in line or "AGENTS" in line for line in changed):
        return "docs: update project documentation"
    if any("tests/" in line for line in changed):
        return "test: update project coverage"
    return DEFAULT_SUBJECT


def build_prompt(repo: Path) -> str:
    stat = git(repo, ["diff", "--cached", "--stat"], timeout=30)
    name_status = git(repo, ["diff", "--cached", "--name-status"], timeout=30)
    diff = git(repo, ["diff", "--cached", "--", ":(exclude)changelog.md"], timeout=60)
    if len(diff) > MAX_DIFF_CHARS:
        diff = diff[:MAX_DIFF_CHARS] + "\n...[diff truncated]"

    return f"""
You generate automation output for a Laravel Orchid project.

Return only JSON matching the provided schema.

Commit message rules:
- Use Conventional Commits 1.0.0.
- commit_subject must be <= 72 characters.
- Use imperative present tense.
- Do not mention Codex, AI, hooks, internal automation, or generated files unless the change is only automation.
- Use lowercase after the prefix unless a proper noun requires uppercase.
- No trailing period.

Changelog rules:
- changelog_entries must be 1 to 5 user-facing bullet entries.
- Write in plain human English.
- No programming code, code identifiers, filenames, class names, method names, namespaces, raw URLs, markdown links, or backticks.
- Avoid developer jargon. Describe what changed for a project owner or operator.
- If the changes are internal documentation or workflow only, say that plainly.

Changed files:
{name_status}

Diff summary:
{stat}

Diff:
{diff}
""".strip()


def call_codex(repo: Path, prompt: str, timeout: int) -> dict[str, Any] | None:
    schema = {
        "type": "object",
        "additionalProperties": False,
        "properties": {
            "commit_subject": {"type": "string"},
            "commit_body": {"type": "string"},
            "changelog_entries": {
                "type": "array",
                "minItems": 1,
                "maxItems": 5,
                "items": {"type": "string"},
            },
        },
        "required": ["commit_subject", "commit_body", "changelog_entries"],
    }

    with tempfile.TemporaryDirectory(prefix="codex-commit-") as tmp:
        schema_path = Path(tmp) / "schema.json"
        output_path = Path(tmp) / "output.json"
        schema_path.write_text(json.dumps(schema), encoding="utf-8")

        env = os.environ.copy()
        env["CODEX_AUTO_PUSH_DISABLED"] = "1"
        env["CODEX_CHANGELOG_COMMIT_RUNNING"] = "1"

        result = run(
            [
                "codex",
                "exec",
                "--cd",
                str(repo),
                "--sandbox",
                "read-only",
                "--ephemeral",
                "-c",
                "features.hooks=false",
                "-c",
                'model_reasoning_effort="low"',
                "--output-schema",
                str(schema_path),
                "-o",
                str(output_path),
                "-",
            ],
            repo,
            timeout=timeout,
            env=env,
            input_data=prompt,
        )

        if result.returncode != 0 or not output_path.exists():
            sys.stderr.write((result.stderr or result.stdout or "codex exec failed") + "\n")
            return None

        try:
            return json.loads(output_path.read_text(encoding="utf-8"))
        except json.JSONDecodeError as exc:
            sys.stderr.write(f"invalid Codex JSON output: {exc}\n")
            return None


def build_commit_message(repo: Path, data: dict[str, Any] | None) -> str:
    subject = clean_subject(str((data or {}).get("commit_subject") or ""))
    if not conventional_subject_is_valid(repo, subject):
        name_status = git(repo, ["diff", "--cached", "--name-status"], timeout=30)
        subject = fallback_subject(name_status)

    body = str((data or {}).get("commit_body") or "").strip()
    if body:
        return subject + "\n\n" + body + "\n"
    return subject + "\n"


def today_section(text: str, heading: str) -> str:
    start = text.find(heading)
    if start < 0:
        return ""
    next_heading = text.find("\n## ", start + len(heading))
    if next_heading < 0:
        return text[start:]
    return text[start:next_heading]


def update_changelog(repo: Path, data: dict[str, Any] | None) -> None:
    name_status = git(repo, ["diff", "--cached", "--name-status"], timeout=30)
    raw_entries = (data or {}).get("changelog_entries") or []
    entries = [sanitize_changelog_entry(str(entry)) for entry in raw_entries]
    entries = [entry for entry in entries if entry]
    if not entries:
        entries = fallback_entries(name_status)

    changelog = repo / "changelog.md"
    today = datetime.now().strftime("%Y-%m-%d")
    heading = f"## {today}"

    if not changelog.exists():
        block = "\n".join(f"- {entry}" for entry in entries)
        changelog.write_text(f"# Changelog\n\n## {today}\n\n{block}\n", encoding="utf-8")
        return

    text = changelog.read_text(encoding="utf-8")
    section = today_section(text, heading)
    entries = [entry for entry in entries if f"- {entry}" not in section]
    if not entries:
        return

    block = "\n".join(f"- {entry}" for entry in entries)
    if heading in text:
        text = text.replace(heading, f"{heading}\n\n{block}", 1)
    else:
        if text.startswith("# Changelog"):
            text = text.rstrip() + f"\n\n## {today}\n\n{block}\n"
        else:
            text = f"# Changelog\n\n## {today}\n\n{block}\n\n{text.lstrip()}"
    changelog.write_text(text, encoding="utf-8")


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--timeout", type=int, default=int(os.environ.get("CODEX_COMMIT_AI_TIMEOUT", "240")))
    parser.add_argument("--no-ai", action="store_true")
    args = parser.parse_args()

    repo = Path(git(Path.cwd(), ["rev-parse", "--show-toplevel"]) or Path.cwd()).resolve()
    message_path = Path(git(repo, ["rev-parse", "--git-path", "codex-commit-message.txt"])).resolve()

    data: dict[str, Any] | None = None
    if not args.no_ai:
        data = call_codex(repo, build_prompt(repo), timeout=args.timeout)

    update_changelog(repo, data)
    message_path.write_text(build_commit_message(repo, data), encoding="utf-8")
    print(message_path)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
