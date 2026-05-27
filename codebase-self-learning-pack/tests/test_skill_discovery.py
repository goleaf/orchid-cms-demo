#!/usr/bin/env python3
from __future__ import annotations

import json
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, (REPO_ROOT / ".codex" / "hooks").as_posix())

from memorylib import discover_repository_skills  # noqa: E402


class SkillDiscoveryTest(unittest.TestCase):
    def make_skill(self, root: Path, rel_path: str, contents: str) -> Path:
        skill_dir = root / rel_path
        skill_dir.mkdir(parents=True, exist_ok=True)
        (skill_dir / "SKILL.md").write_text(contents, encoding="utf-8")
        return skill_dir

    def test_valid_skill_is_discovered(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            self.make_skill(root, ".agents/skills/example-skill", "---\nname: example-skill\ndescription: Example skill.\n---\n")

            inventory = discover_repository_skills(root)

            self.assertEqual(["example-skill"], [skill["name"] for skill in inventory["skills"]])
            self.assertTrue(inventory["skills"][0]["valid"])

    def test_missing_description_produces_warning(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            self.make_skill(root, ".agents/skills/no-description", "---\nname: no-description\n---\n")

            inventory = discover_repository_skills(root)

            self.assertIn("missing description", inventory["skills"][0]["warnings"])
            self.assertFalse(inventory["skills"][0]["valid"])

    def test_missing_skill_md_is_ignored(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            (root / ".agents/skills/not-a-skill").mkdir(parents=True)

            inventory = discover_repository_skills(root)

            self.assertEqual([], inventory["skills"])

    def test_duplicate_skill_names_produce_warning(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            contents = "---\nname: duplicate-skill\ndescription: First skill.\n---\n"
            self.make_skill(root, ".agents/skills/first", contents)
            self.make_skill(root, "skills/second", contents)

            inventory = discover_repository_skills(root)

            self.assertEqual(2, len(inventory["skills"]))
            self.assertTrue(any("duplicate skill name" in warning for warning in inventory["warnings"]))
            self.assertTrue(all(
                any("duplicate skill name" in warning for warning in skill["warnings"])
                for skill in inventory["skills"]
            ))

    def test_no_write_json_command_outputs_valid_json_without_writing_memory(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            self.make_skill(root, ".agents/skills/example-skill", "---\nname: example-skill\ndescription: Example skill.\n---\n")

            result = subprocess.run(
                [
                    sys.executable,
                    (REPO_ROOT / ".agents/skills/codebase-self-learning/scripts/discover_skills.py").as_posix(),
                    "--root",
                    root.as_posix(),
                    "--no-write",
                    "--json",
                ],
                capture_output=True,
                text=True,
                check=False,
            )

            self.assertEqual("", result.stderr)
            self.assertEqual(0, result.returncode)
            payload = json.loads(result.stdout)
            self.assertEqual("example-skill", payload["skills"][0]["name"])
            self.assertFalse((root / ".codex/memory/skill_inventory.json").exists())

    def test_malformed_frontmatter_does_not_crash(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            self.make_skill(root, ".agents/skills/malformed", "---\nname: malformed\n# no closing marker\n")

            inventory = discover_repository_skills(root)

            self.assertEqual("malformed", inventory["skills"][0]["name"])
            self.assertTrue(any("invalid metadata format" in warning for warning in inventory["skills"][0]["warnings"]))


if __name__ == "__main__":
    unittest.main()
