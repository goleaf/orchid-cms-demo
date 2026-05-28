---
name: codebase-self-learning
description: Learn and reuse repository-specific coding conventions from code, tests, user corrections, and previous task outcomes. Use for implementation, debugging, refactoring, code review, architecture planning, or whenever the user asks to make the agent remember or improve from codebase experience.
---

# Codebase Self-Learning Skill

This skill creates a local, evidence-gated learning loop for a repository. It does **not** retrain the model. It maintains project memory files and uses them to make future coding tasks more consistent.

## Core rule

Learn only from evidence:

1. Current repository code.
2. Passing tests or failing tests with clear cause.
3. Explicit user corrections.
4. Repeated patterns found in multiple files.
5. Existing project docs such as `AGENTS.md`, README, architecture notes, migration conventions, and test conventions.

Do not learn from guesses, one-off accidents, secrets, credentials, private customer data, or temporary implementation details.

## Before working on code

1. Read relevant project guidance:
   - `AGENTS.md`
   - `.codex/memory/project_brief.md`
   - `.codex/memory/rules.md`
   - `.codex/memory/learned_patterns.md`
   - `.codex/memory/decisions.md`
   - `.codex/memory/recurring_mistakes.md`
2. Inspect the code before changing it.
3. Identify likely conventions:
   - framework version and package versions
   - directory structure
   - naming style
   - validation style
   - test style
   - localization/translation style
   - UI/admin patterns
   - service/action patterns
4. If memory conflicts with code, trust current code and record a candidate correction.

## While working

Use the project memory as guardrails, not as blind rules. If a memory entry is wrong, stale, or contradicted by code, do not follow it. Add a candidate update with evidence.

When implementing a feature:

1. Reuse existing patterns before inventing new ones.
2. Prefer small, testable changes.
3. Run the smallest relevant checks first.
4. Record discovered project-specific patterns only when they are stable and useful.

## Repository skill discovery

This package can discover repository-local Codex skills without scanning the whole project. It checks bounded roots such as `.agents/skills/`, `skills/`, `.codex/skills/`, and plugin-style skill roots only when plugin manifests are present.

Run discovery manually with:

```bash
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```

Discovery writes:

- `.codex/memory/skill_inventory.json`: compact inventory with skill names, descriptions, paths, optional folders, validity, warnings, and mtimes.
- `.codex/memory/events.jsonl`: compact `repository_skill_discovery` event with counts and skill names only.
- `.codex/memory/tool_notes.md`: a human-readable summary that is replaced on each scan.

Session-start context may include a concise inventory: name, description, relative path, and key warnings. User-prompt context includes the inventory only when the prompt mentions skills, hooks, self-learning, memory, or discovery. Full `SKILL.md` contents, scripts, references, and assets are never injected automatically.

Fix invalid skills by adding a `SKILL.md` with frontmatter:

```markdown
---
name: example-skill
description: Short stable description.
---
```

The parser is intentionally standard-library only and supports simple `key: value` frontmatter. Malformed metadata produces warnings instead of failing discovery.

## What to learn

Good memory entries are short, durable, and useful. Examples:

- “Visible UI labels in Orchid screens must use `tkey()` and translation keys, not hardcoded Russian labels.”
- “CRM lead status display should prefer `name_translations`, fallback to `name`, then `code`.”
- “This project uses service classes under `App\Services\...` for business logic.”
- “Run `php artisan test --filter=Lead` for CRM lead changes before full test suite.”
- “Do not create SaaS tenant logic; this is a local driving-school product.”

## What not to learn

Do not store:

- API keys, tokens, credentials, cookies, private URLs.
- Customer or student personal data.
- Full user prompts unless the user explicitly asks you to store them.
- Large code snippets.
- Unsupported guesses.
- Facts that only applied to one temporary bug.

## At the end of a task

Update learning only if there is evidence.

Use one of these options:

### Option A — Add reviewed memory directly

Use when the learning is clearly proven and stable:

```bash
python3 .agents/skills/codebase-self-learning/scripts/update_memory.py \
  --category rules \
  --fact "Visible UI labels must use tkey() translation keys." \
  --evidence "User requirement + multilingual foundation implementation."
```

Categories:

- `rules`
- `learned_patterns`
- `decisions`
- `recurring_mistakes`
- `tool_notes`

### Option B — Add a candidate for later review

Use when the pattern is likely but needs user or developer confirmation:

```bash
python3 .agents/skills/codebase-self-learning/scripts/update_memory.py \
  --candidate \
  --category learned_patterns \
  --fact "Possible convention: CRM screens use separate Screen classes under App\\Orchid\\Screens\\Crm." \
  --evidence "Observed in LeadListScreen and LeadEditScreen."
```

## Memory quality checklist

Before writing a memory entry, check:

- Is it useful for future coding tasks?
- Is it supported by evidence?
- Is it short enough to read quickly?
- Is it not secret or personal data?
- Is it project-specific rather than generic programming advice?
- Does it avoid overfitting to one file?

## Project-specific seed rules

The current product direction is:

- Local driving-school operating system, not SaaS.
- Laravel + Orchid admin panel.
- Public website + CRM + students + groups + schedule + instructors + vehicles + documents + payments later.
- Superadmin is the highest local admin of the driving school, not a SaaS owner.
- All visible UI text should be translatable.
- Avoid hardcoded visible labels in admin screens, public pages, notifications, validations, and documents.
- Prefer translation keys through `tkey()` or Laravel localization.
- Business content that users edit should support multilingual values when needed.
- Orchid implementation should use the local `orchid-platform` skill and mirrored docs before version-sensitive admin code changes.

Keep these rules unless the user explicitly changes the project direction.
