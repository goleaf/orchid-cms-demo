#!/usr/bin/env bash
set -euo pipefail

repo_url="${ORCHID_DOCS_REPO:-https://github.com/orchidsoftware/orchid.software.git}"
branch="${ORCHID_DOCS_BRANCH:-master}"
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
skill_dir="$(cd "${script_dir}/.." && pwd)"
docs_dir="${skill_dir}/references/docs"
tmp_dir="$(mktemp -d "${TMPDIR:-/tmp}/orchid-docs.XXXXXX")"

cleanup() {
    rm -rf "${tmp_dir}"
}

trap cleanup EXIT

git clone --depth 1 --filter=blob:none --sparse --branch "${branch}" "${repo_url}" "${tmp_dir}"
git -C "${tmp_dir}" sparse-checkout set docs/en/docs readme.md composer.json

rm -rf "${docs_dir}"
mkdir -p "${docs_dir}"
cp -R "${tmp_dir}/docs/en/docs/." "${docs_dir}/"

commit="$(git -C "${tmp_dir}" rev-parse HEAD)"
synced_at="$(date -u +"%Y-%m-%dT%H:%M:%SZ")"

cat > "${skill_dir}/references/SOURCE.md" <<SOURCE
# Orchid Documentation Source

The local Markdown mirror in \`references/docs\` is synced from the official Orchid website repository.

- Source repository: ${repo_url}
- Source path: \`docs/en/docs\`
- Branch: \`${branch}\`
- Commit: \`${commit}\`
- Synced at: \`${synced_at}\`
- Public docs URL: https://orchid.software/en/docs/
- Context7 fallback library id: \`/orchidsoftware/platform\`

Refresh with:

\`\`\`bash
.agents/skills/orchid-platform/scripts/sync_orchid_docs.sh
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
\`\`\`
SOURCE

echo "Synced Orchid docs from ${commit} into ${docs_dir}"
