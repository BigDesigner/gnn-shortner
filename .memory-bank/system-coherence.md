# System Coherence

## Operational Protocols

### Session Start Protocol
1. Check current branch and git status to establish environmental awareness.
2. Read `.memory-bank/active-session.json` to verify current sprint, active tasks, and status.
3. Detect operating mode from environment variables (e.g., `CI=true`).

### Operating Mode Detection
- **CI Mode:** Triggered if `CI=true`. Operates non-interactively. Does not block on unconfirmed decisions, dirty worktree, or approval gates. Writes run summaries instead of proposing commits.
- **Interactive Mode:** Default mode. Requires user confirmation for critical decisions, dirty worktree bypass, and final git commits.

### Discovery Approval Gate
- After completing discovery (Step 1), compile findings and present them to the user.
- In Interactive mode, block execution until the user explicitly approves proceeding to step 2.

### Worktree Cleanliness Checks
- Verify worktree status before starting any workspace changes.
- If changes exist:
  - **Interactive Mode:** Prompt the user to stash, commit, or proceed with dirty worktree.
  - **CI Mode:** Log a warning in `bug-list.md` and proceed without stashing/committing.

### Branch Awareness
- Keep track of the active branch at all times.
- Ensure all commits and work items map correctly to the target branch.

### Context Drift Prevention
- Refer to `.specs/` and `.memory-bank/` as the single sources of truth.
- Update files immediately upon finishing tasks to prevent out-of-sync context.

---

## Change Checklists

### Pre-change Checklist
- [ ] Verify that the file to edit exists and is not restricted.
- [ ] Confirm no application source code is touched (unless explicitly requested).
- [ ] Identify dependencies and files impacted by the change.
- [ ] Check if the change introduces security risks or requires a new ADR.

### Post-change Checklist
- [ ] Run validation commands (if available).
- [ ] Check if the changes match PSR-12 (PHP) or ES6+ (JS) standards.
- [ ] Log changes in `verified-worklog.md`.
- [ ] Update `active-session.json` state.
- [ ] Check `git diff` to ensure no unintended files were modified.

---

## Validation & Handoff Rules

### Validation Recommendation Rules
- Do not run validation commands automatically unless explicitly requested.
- Present validation commands corresponding to the project's technology stack (e.g., PHPUnit, PHPCS).
- If validation tools are missing, flag as `Environment unavailable` and suggest installation/setup steps.

### Handoff Rules
- When wrapping up a session or passing tasks, create or update `.tasks/handoff.md` with:
  - Current mode, branch, last commit, and worktree status.
  - Summary of changes and validations.
  - Known failures and next recommended actions.

---

## Concurrency & Locking
- Concurrent agents must respect the `concurrency_lock` property in `active-session.json`.
- Do not modify files if another process holds the lock, unless resolving a deadlock or explicitly requested.

---

## Unconfirmed Decision Protocol
- If a fact cannot be verified from repository files:
  - **Critical facts (architecture, security, deployment, public APIs):**
    - **Interactive mode:** Halt and ask the user for clarification.
    - **CI mode:** Create a proposed, unconfirmed ADR for review and proceed.
  - **Non-critical facts:** Mark as `Unconfirmed` and proceed.
