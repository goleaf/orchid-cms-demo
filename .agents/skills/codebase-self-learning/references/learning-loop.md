# Learning Loop Reference

The self-learning loop has four parts:

1. **Memory files** under `.codex/memory/` hold stable project knowledge.
2. **SessionStart/UserPromptSubmit hooks** inject relevant memory into Codex context.
3. **PostToolUse/Stop hooks** collect tool events and propose learning candidates.
4. **This skill** tells Codex when and how to promote candidates into stable memory.

## Recommended workflow

1. User asks for code work.
2. Hook injects project memory.
3. Codex reads code and implements changes.
4. Hook records changed files and command/test events.
5. Codex uses this skill to write reviewed learning entries.
6. Future sessions receive the updated project memory.

## Memory file purposes

- `project_brief.md`: high-level project direction.
- `rules.md`: hard rules and user requirements.
- `learned_patterns.md`: conventions discovered from code.
- `decisions.md`: architecture decisions and why they were made.
- `recurring_mistakes.md`: repeated mistakes to avoid.
- `tool_notes.md`: useful commands and test shortcuts.
- `learning_candidates.md`: unreviewed possible memories.
- `skill_inventory.json`: compact repository-local Codex skill inventory.
- `events.jsonl`: low-level hook event log, safe and sanitized.

## Repository skill discovery

Repository skill discovery scans only known local skill roots:

- `.agents/skills/`
- `skills/`
- `.codex/skills/`
- plugin skill roots when a plugin manifest already exists

A skill is a directory containing `SKILL.md`. The discovery script parses simple frontmatter (`name` and `description`), records optional `scripts/`, `references/`, and `assets/` folders, and stores warnings for incomplete or malformed skills.

Manual command:

```bash
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```

Use `--no-write` for read-only verification, `--root PATH` to scan a specific repository, and `--verbose` to print scanned roots and warnings.

Session-start context can include a short skill inventory. User-prompt context includes it only for prompts about skills, hooks, self-learning, memory, or discovery. Hooks must continue if discovery fails.

## Promotion policy

A candidate can become stable memory if one of these is true:

- The user explicitly confirms it.
- At least two code locations support it.
- A test or CI result proves it.
- Existing documentation supports it.

## Deletion policy

Remove or update a memory entry when:

- Code now contradicts it.
- The user changes direction.
- It causes wrong behavior.
- It is too broad or too vague.
