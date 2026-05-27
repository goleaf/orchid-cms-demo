# Codebase Self-Learning Skill + Codex Hooks

This package adds a local self-learning loop for Codex.

It does **not** retrain the model. It creates a repository memory layer that Codex can read and update while working on code.

## What is included

```text
.agents/skills/codebase-self-learning/SKILL.md
.agents/skills/codebase-self-learning/scripts/discover_skills.py
.agents/skills/codebase-self-learning/scripts/update_memory.py
.agents/skills/codebase-self-learning/references/learning-loop.md
.codex/hooks.json
.codex/hooks/memorylib.py
.codex/hooks/session_start_context.py
.codex/hooks/user_prompt_context.py
.codex/hooks/post_tool_learning.py
.codex/hooks/stop_learning.py
.codex/memory/*.md
.codex/memory/skill_inventory.json
```

## How it works

1. `SessionStart` loads stable memory from `.codex/memory/` into Codex context.
2. `UserPromptSubmit` selects memory entries relevant to the current prompt.
3. `PostToolUse` records sanitized evidence from shell commands and code-edit tools.
4. `Stop` writes a learning candidate summary when files changed.
5. The `codebase-self-learning` skill tells Codex how to promote useful candidates into stable memory.
6. Repository skill discovery keeps a compact inventory of local Codex skills available to context hooks.

## Install

Copy the `.agents` and `.codex` folders into the root of your repository.

Then start Codex from inside that repository.

In the Codex CLI, run:

```text
/hooks
```

Review and trust the hooks. Codex requires review/trust for non-managed command hooks.

## Usage

Ask Codex to use the skill explicitly:

```text
Use the codebase-self-learning skill. Implement the next CRM block and update project memory with stable conventions only.
```

Or let Codex activate it implicitly when doing code work.

## Repository skill discovery

Repository skill discovery scans bounded local roots only:

- `.agents/skills/`
- `skills/`
- `.codex/skills/`
- plugin skill roots when this repository already contains a plugin manifest or plugin directory

A skill is a directory with `SKILL.md`. Simple frontmatter is supported:

```markdown
---
name: codebase-self-learning
description: Learn stable project rules from code evidence.
---
```

Run discovery manually:

```bash
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```

Useful options:

```bash
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --no-write --json
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --root /path/to/repo --verbose
```

Discovery writes:

- `.codex/memory/skill_inventory.json`
- `.codex/memory/events.jsonl`
- `.codex/memory/tool_notes.md`

Session-start context may include skill name, description, relative path, and key warnings. User-prompt context includes skills only when the prompt is about skills, hooks, self-learning, memory, or discovery. Full `SKILL.md` contents, references, scripts, and assets are not injected.

Recommended fixes for invalid skills:

- Add missing `name` and `description` frontmatter.
- Keep skill names kebab-case.
- Remove duplicate skill names.
- Remove empty optional folders or add the intended files.
- Keep `SKILL.md` concise; move large material into `references/`.

Limitations:

- Metadata parsing is intentionally minimal and standard-library only.
- Discovery does not modify skills.
- Missing or malformed metadata becomes warnings, not hard failures.

## Add a stable memory manually

```bash
python3 .agents/skills/codebase-self-learning/scripts/update_memory.py \
  --category rules \
  --fact "All visible UI labels must use translation keys, not hardcoded text." \
  --evidence "User requirement for Superadmin-managed translations."
```

## Add a candidate memory

```bash
python3 .agents/skills/codebase-self-learning/scripts/update_memory.py \
  --candidate \
  --category learned_patterns \
  --fact "Possible convention: CRM business logic belongs in App\\Services\\Crm." \
  --evidence "Observed in current CRM implementation."
```

## Privacy and safety

The hooks try to sanitize secrets and emails, but do not treat this as a security boundary. Review `.codex/memory/events.jsonl` and `.codex/memory/learning_candidates.md` before committing them.

Recommended `.gitignore` choices:

```gitignore
# Keep stable memory if your team wants shared project learning
# .codex/memory/*.md

# Usually do not commit noisy event logs
.codex/memory/events.jsonl
.codex/memory/prompt_index.jsonl
```

## Best practice

Commit stable memory files when they encode team/project rules. Do not commit noisy logs or private task history.
