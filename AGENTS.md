# Repository guidance

This repository is public. Keep client reports, audit data, private notes and credentials out of
the repository and its issues or pull requests. Record those in the authorised private project
workspace after checking the intended audience and direct plus inherited access.

If a private audit or report produces page-copy drafts while working in this repository, save
each page's draft in a separate Content record linked to the audit Work record in the authorised
private workspace. Keep findings on the Work record. Do not put draft copy in a Work document,
this public repository, or a public issue or pull request. Check the private workspace's current
schema and permissions before creating records; link an existing unpublished draft where
appropriate, and create a new linked revision for copy that is already published.

When an external handoff requires a Google Doc, use Pageless mode and paragraph spacing after
body paragraphs. Do not insert blank paragraphs to create visual gaps. Make every reference a
complete, visible, clickable URL that the intended reader can open.

Read the repository's existing contribution and release instructions before changing code.

## Finish Git work before ending every session

- This rule applies to every operator and AI agent, in interactive sessions and scheduled runs.
  Session closeout is part of the task, not an optional follow-up. Before the final reply, inspect
  Git status, session branches and worktrees, and open PRs; account for every file and branch the
  session changed. A read-only session needs no empty commit or PR.
- Review the actual diff, run the required checks, stage explicit intended paths, create signed
  commits with the configured identity and push them. Never leave completed work uncommitted,
  staged, stashed or only in local commits. Do not commit secrets, generated junk or another
  session's edits. If signing needs 1Password, ask the operator to unlock it manually and wait;
  never inspect or control its UI or bypass signing.
- Finish integration through the repository's permitted route: push directly only where allowed,
  otherwise create or reuse a scoped PR, review the final diff, complete checks and required
  reviews, and merge eligible work into the verified remote default branch. An open PR or queued
  auto-merge is unfinished work. Resolve routine failures and conflicts, and complete unaffected
  merges. Honour explicit draft/hold instructions and required approval, access and protection
  gates; never force-push or use an admin bypass. For scoped skill and team-rule distribution,
  the agent may merge its own verified PRs under the standing team authorisation.
- Fetch and read back the remote default branch before cleanup. After confirming the work landed
  and no other session uses the branch or worktree, delete this session's merged local and remote
  branches and remove its clean temporary worktrees. Verify squash/rebase integration explicitly
  when commit ancestry differs. Preserve unmerged work, required artefacts and other sessions'
  branches or edits; never bulk-land unrelated work, reset, stash or force-remove it to claim a
  clean result. Fast-forward the local default branch only when clean and safe.
- Verify final Git status and remaining branches, then report the landed PR/commit and cleanup
  result. If a real gate or an explicit hold prevents completion, push a recoverable signed WIP
  branch when safe, identify the exact file/branch/PR, gate and next action, and state that
  closeout is incomplete. A stop or interruption request takes priority; preserve recoverable
  state and report the remaining work. Do not call a repository tidy while session work remains.
