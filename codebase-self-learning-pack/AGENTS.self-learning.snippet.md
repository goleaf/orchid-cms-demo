# Optional AGENTS.md snippet

Codex should use the `codebase-self-learning` skill for implementation, debugging, refactoring, and review tasks.

Before editing code:

- Read `.codex/memory/project_brief.md`, `.codex/memory/rules.md`, `.codex/memory/decisions.md`, and `.codex/memory/learned_patterns.md` when relevant.
- Treat memory as project guidance, but verify against current code.
- If memory conflicts with code, current code wins; add a learning candidate with evidence.

After completing code work:

- Add stable, evidence-backed project conventions to `.codex/memory/*.md`.
- Add uncertain observations to `.codex/memory/learning_candidates.md`.
- Never store secrets, credentials, tokens, cookies, or private personal data.
- Do not store full user prompts unless explicitly requested.
- Let the Stop hook update `changelog.md` and generate the Conventional Commit message unless `CODEX_AUTO_PUSH_DISABLED=1` is set for a deliberate local run.

Repository skill discovery:

- Run `python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json` to refresh `.codex/memory/skill_inventory.json`.
- Fix invalid repository skills by adding `SKILL.md` frontmatter with `name` and `description`.
- Keep discovered skill context concise: name, description, relative path, and warnings only.
- Use the `orchid-platform` skill for Orchid screens, layouts, menus, permissions, routes, filters, fields, tables, modals, and local Orchid docs.

Project direction:

- Local driving-school product, not SaaS.
- Laravel + Orchid admin panel.
- Superadmin is local company administrator, not SaaS owner.
- Visible UI text must be translatable and editable through the translation system where appropriate.
- Codex prompt hooks should expose compact repository skill inventory by default; full docs remain searchable local references.
- Generated changelog entries should be plain human language without code identifiers, file paths, raw URLs, or private data.
