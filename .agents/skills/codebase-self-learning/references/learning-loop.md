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
- `events.jsonl`: low-level hook event log, safe and sanitized.

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
