# Codebase Self-Learning Skill + Codex Hooks

This package adds a local self-learning loop for Codex.

It does **not** retrain the model. It creates a repository memory layer that Codex can read and update while working on code.

## What is included

```text
.agents/skills/codebase-self-learning/SKILL.md
.agents/skills/codebase-self-learning/scripts/update_memory.py
.agents/skills/codebase-self-learning/references/learning-loop.md
.codex/hooks.json
.codex/hooks/memorylib.py
.codex/hooks/session_start_context.py
.codex/hooks/user_prompt_context.py
.codex/hooks/post_tool_learning.py
.codex/hooks/stop_learning.py
.codex/memory/*.md
```

## How it works

1. `SessionStart` loads stable memory from `.codex/memory/` into Codex context.
2. `UserPromptSubmit` selects memory entries relevant to the current prompt.
3. `PostToolUse` records sanitized evidence from shell commands and code-edit tools.
4. `Stop` writes a learning candidate summary when files changed.
5. The `codebase-self-learning` skill tells Codex how to promote useful candidates into stable memory.

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
