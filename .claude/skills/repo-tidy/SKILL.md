---
name: repo-tidy
description: 'Finish outstanding Git work in the current repository when the user says "tidy repo", "repo tidy", "merge", or "merge and commit". Commit reviewed changes with signing, complete checks and merge eligible branches/PRs into main, then verify the remote result. Use these triggers for Git actions, not unrelated uses of "merge".'
---

# Repo tidy

Leave the requested repository with its outstanding work committed, pushed and merged into
`main`, with verification complete. An open PR or an auto-merge request is unfinished work.
The invocation authorises this workflow within the requested scope; carry it through without
asking again for routine commits or merges.

1. **Inspect.** Read the repository instructions, fetch the remote, and inspect working-tree
   changes, worktrees, local/remote branches and open PRs. Use the verified default branch if
   it is not `main`. A named branch or PR limits the scope; otherwise account for all outstanding
   work in this repository. Preserve another session's active edits; use an isolated worktree
   when needed. Report anything held out rather than silently calling the repo tidy.
2. **Commit.** Review the actual diffs, group intended changes into coherent commits and stage
   explicit paths. Exclude secrets and generated junk. Use the configured signing identity;
   verify new commits are signed. Add a DCO sign-off only when required by the repository; a
   sign-off trailer is not a cryptographic signature. Never disable signing. If 1Password
   signing fails, ask a user to unlock 1Password manually and wait; never inspect or control its UI.
3. **Validate and merge.** Run the checks required by the changed files and repository, fix
   failures and resolve merge conflicts yourself: inspect both sides and their intent, preserve
   valid changes, sign the conflict-resolution commit, rerun checks and finish merging into
   the default branch. Do not hand routine conflicts or recoverable failures back to the user;
   try safe alternative routes. Ask only when the intended behaviour cannot be established or
   a required access/approval gate needs a person. Push, create or reuse scoped PRs, review
   their final diffs, and merge eligible work through the permitted route. Respect
   required reviews, checks and branch protection; never force-push or use an admin bypass.
   Finish unaffected merges if one is blocked. Do not stop at PR creation or queued auto-merge.
4. **Verify.** Fetch again and read back the remote default branch. Confirm each intended change
   landed, PRs are merged, required checks passed and required signatures verified, including
   any new merge/squash commit. Reconcile branch tips and remaining working-tree changes with
   the initial inventory; a squash merge need not contain the original commit IDs. Update the
   local default branch only when a clean, safe fast-forward is possible. Never reset or stash
   someone else's work to manufacture a clean result.
5. **Report.** Give the merged PR links and resulting commit, plus any remaining branch, file
   or exact gate. Mark complete only when all in-scope work is verified on the remote default
   branch. If blocked, state the one concrete action needed to finish.
