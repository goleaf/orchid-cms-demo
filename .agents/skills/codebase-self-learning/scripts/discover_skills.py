#!/usr/bin/env python3
"""Discover repository-local Codex skills and write a compact inventory."""
from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path


def add_hooks_to_path() -> None:
    for parent in Path(__file__).resolve().parents:
        hooks_dir = parent / ".codex" / "hooks"
        if (hooks_dir / "memorylib.py").is_file():
            sys.path.insert(0, hooks_dir.as_posix())
            return


add_hooks_to_path()

from memorylib import (  # noqa: E402
    discover_repository_skills,
    find_repo_root,
    path_relative_to,
    write_skill_inventory,
)


def print_summary(inventory: dict, inventory_path: Path | None, verbose: bool) -> None:
    skills = inventory.get("skills") if isinstance(inventory.get("skills"), list) else []
    warning_count = len(inventory.get("warnings") or []) + sum(len(skill.get("warnings") or []) for skill in skills)
    print(
        "Repository skill discovery: "
        f"{len(skills)} skills found, "
        f"{sum(1 for skill in skills if skill.get('valid'))} valid, "
        f"{warning_count} warnings."
    )
    if inventory_path is not None:
        print(f"Inventory: {inventory_path}")
    if skills:
        print("Skills: " + ", ".join(str(skill.get("name")) for skill in skills if skill.get("name")))
    if verbose:
        print("Scanned roots: " + ", ".join(str(root) for root in inventory.get("skill_roots_scanned", [])))
        warnings = [*inventory.get("warnings", [])]
        for skill in skills:
            for warning in skill.get("warnings") or []:
                warnings.append(f"{skill.get('path')}: {warning}")
        if warnings:
            print("Warnings:")
            for warning in warnings:
                print(f"- {warning}")


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description="Discover repository-local Codex skills.")
    parser.add_argument("--json", action="store_true", help="Print JSON inventory to stdout.")
    parser.add_argument("--no-write", action="store_true", help="Do not write memory files.")
    parser.add_argument("--root", help="Repository root to scan.")
    parser.add_argument("--verbose", action="store_true", help="Print scanned roots and warnings.")
    args = parser.parse_args(argv)

    root = Path(args.root).resolve() if args.root else find_repo_root()
    inventory = discover_repository_skills(root)

    inventory_path: Path | None = None
    if not args.no_write:
        inventory_path = write_skill_inventory(root, inventory)

    if args.json:
        print(json.dumps(inventory, ensure_ascii=False, indent=2, default=str))
    else:
        relative_inventory_path = path_relative_to(root, inventory_path) if inventory_path else None
        print_summary(inventory, Path(relative_inventory_path) if relative_inventory_path else None, args.verbose)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
