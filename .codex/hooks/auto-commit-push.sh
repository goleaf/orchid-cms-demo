#!/usr/bin/env bash

set -Eeuo pipefail

if [[ "${CODEX_AUTO_PUSH_DISABLED:-}" == "1" ]]; then
    exit 0
fi

if [[ "${CODEX_CHANGELOG_COMMIT_RUNNING:-}" == "1" ]]; then
    exit 0
fi

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    exit 0
fi

repo_root="$(git rev-parse --show-toplevel)"
cd "${repo_root}"

log_file="$(git rev-parse --git-path codex-auto-push.log)"
lock_dir="$(git rev-parse --git-path codex-auto-push.lock)"

mkdir -p "$(dirname "${log_file}")"
exec >> "${log_file}" 2>&1

log() {
    printf '%s %s\n' "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "$*"
}

if ! mkdir "${lock_dir}" 2>/dev/null; then
    log "skip: another Codex auto-push hook is running"
    exit 0
fi

cleanup() {
    rmdir "${lock_dir}" 2>/dev/null || true
}

trap cleanup EXIT

branch="$(git branch --show-current)"

if [[ -z "${branch}" ]]; then
    log "skip: detached HEAD cannot be pushed safely"
    exit 0
fi

if [[ -e "$(git rev-parse --git-path MERGE_HEAD)" ]] \
    || [[ -d "$(git rev-parse --git-path rebase-merge)" ]] \
    || [[ -d "$(git rev-parse --git-path rebase-apply)" ]]; then
    log "skip: repository has an active merge or rebase"
    exit 0
fi

if [[ -z "$(git status --porcelain=v1)" ]]; then
    log "skip: working tree is clean"
    exit 0
fi

git add -A

if git diff --cached --quiet --exit-code; then
    log "skip: no staged changes after git add"
    exit 0
fi

if ! python3 .codex/hooks/generate_commit_artifacts.py; then
    log "warning: commit artifact generation failed; using fallback message"
    git_dir="$(git rev-parse --git-dir)"
    printf '%s\n' "${CODEX_AUTO_PUSH_MESSAGE:-chore: update project changes}" > "${git_dir}/codex-commit-message.txt"
fi

git add -A

if git diff --cached --quiet --exit-code; then
    log "skip: no staged changes after changelog update"
    exit 0
fi

message_file="$(git rev-parse --git-path codex-commit-message.txt)"

if ! git commit -F "${message_file}"; then
    log "skip: git commit failed"
    exit 0
fi

if git rev-parse --abbrev-ref --symbolic-full-name '@{u}' >/dev/null 2>&1; then
    git push || log "warning: git push failed"
elif git remote get-url origin >/dev/null 2>&1; then
    git push -u origin "${branch}" || log "warning: git push -u origin ${branch} failed"
else
    log "warning: commit created but push skipped because no git remote is configured"
fi
