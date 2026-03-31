---
name: create-pr
description: >
  Create a GitHub Pull Request from the current branch with automated push, assignment, labels, and validations.
  Trigger: When user asks to "create PR", "open pull request", or "make PR".
license: Apache-2.0
metadata:
  author: pablocurumaco
  version: "1.2"
  scope: [root]
  auto_invoke: "Creating a pull request, opening PR, making PR"
---

## When to Use

- User wants to create a PR from the current branch
- User asks to "create PR", "open pull request", or "make PR"
- After finishing feature work and ready to submit for review

## Critical Patterns

### Pre-flight Checks

1. **Verify git repository**: Ensure we're in a git repo
2. **Check for unpushed commits**: Must have local commits to create PR
3. **Verify gh CLI**: Check that `gh` is installed and authenticated
4. **Run linting/formatting**: Validate code quality before creating PR
5. **Detect TODOs**: Scan new changes for TODO comments

### Workflow Steps

1. **Gather branch information** (run in parallel):
   ```bash
   git rev-parse --abbrev-ref HEAD          # Current branch
   git status                                # Working tree state
   git log origin/$(git rev-parse --abbrev-ref HEAD)..HEAD --oneline 2>/dev/null || git log HEAD --oneline -10
   git diff origin/$(git rev-parse --abbrev-ref HEAD)...HEAD 2>/dev/null || git diff HEAD~5..HEAD
   git remote show origin | grep "HEAD branch"  # Detect default branch
   ```

2. **Detect base branch automatically**:
   - Check repo's default branch: `git remote show origin | grep "HEAD branch"`
   - Common patterns: `main`, `dev`, `develop`, `master`
   - Fallback to asking user if detection fails

3. **Run validations**:
   ```bash
   # For CuruKicks (Laravel + Blade)
   ./vendor/bin/pint --test              # Laravel Pint

   # Detect TODOs in changed files
   git diff origin/<base>...HEAD | grep -i "TODO\|FIXME\|XXX"
   ```
   - If linting fails, show errors and ask user if they want to:
     - Fix issues and retry
     - Skip validation and create PR anyway
     - Cancel
   - If TODOs found, inform user PR will be created as draft

4. **Analyze commits and changes**:
   - Review ALL commits that will be included (not just the latest)
   - Understand the full scope from branch divergence
   - Identify the nature of changes (feat, fix, refactor, etc.)
   - Determine labels from commit types

5. **Push to remote**:
   ```bash
   git push -u origin <current-branch>
   ```

5. **Generate PR description**:
   - Format:
     ```markdown
     ## Summary
     - Brief bullet points describing changes
     - Focus on WHAT changed and WHY
     - Keep it concise (3-5 bullets max)
     ```

6. **Create PR with gh CLI**:
   ```bash
   # Determine if draft
   DRAFT_FLAG=""
   if [[ $has_todos == true ]]; then
     DRAFT_FLAG="--draft"
   fi

   # Check which labels exist in the repo before adding them
   LABELS=""
   for label in feat fix refactor docs test; do
     if gh label list --search "$label" --limit 1 | grep -q "$label"; then
       LABELS="$LABELS,$label"
     fi
   done
   LABELS="${LABELS#,}"  # Remove leading comma

   # Build label flag only if labels exist
   LABEL_FLAG=""
   if [[ -n "$LABELS" ]]; then
     LABEL_FLAG="--label $LABELS"
   fi

   # Create PR
   gh pr create --base <base-branch> \
     --title "<title>" \
     --body "$(cat <<'EOF'
   ## Summary
   - Change 1
   - Change 2
   EOF
   )" \
     --assignee @me \
     $LABEL_FLAG \
     $DRAFT_FLAG
   ```

   **Labels**: Only add labels that EXIST in the repository. Check with `gh label list` first.
   If a label doesn't exist, skip it silently — never let `gh pr create` fail because of a missing label.

   **Labels to add based on commit types** (if they exist in the repo):
   - `feat` - if any commit starts with "feat:"
   - `fix` - if any commit starts with "fix:"
   - `refactor` - if any commit starts with "refactor:"
   - `docs` - if any commit starts with "docs:"
   - `test` - if any commit starts with "test:"

7. **Display result**:
   - Show PR URL
   - Confirm creation success

## Edge Cases

### No commits to push
If local branch is up to date with remote:
- Inform user there are no new commits
- Suggest checking if PR already exists: `gh pr view`

### PR already exists
If branch already has an open PR:
- Show existing PR: `gh pr view`
- Ask if they want to update it instead

### gh not configured
If `gh` is not authenticated:
- Instruct user to run: `gh auth login`
- Explain they need GitHub CLI access

### Uncommitted changes
If there are unstaged/uncommitted changes:
- Warn user about uncommitted work
- Suggest committing first or stashing

### Linting failures
If code quality checks fail:
- Show validation errors clearly
- Ask user: Fix and retry / Skip validation / Cancel
- If skipped, add comment to PR body noting validation was skipped

### TODOs detected
If TODO/FIXME/XXX comments found in new code:
- Inform user that PR will be created as draft
- List the TODO items found
- User can convert to ready when TODOs are resolved

## PR Title Format

Keep titles concise (under 70 characters):
- Start with type if clear: `feat:`, `fix:`, `refactor:`
- Describe the main change, not implementation details
- Example: `feat: add form components for select and checkbox fields`

## Commands

```bash
# Check current branch and status
git rev-parse --abbrev-ref HEAD
git status

# Detect default branch
git remote show origin | grep "HEAD branch" | cut -d: -f2 | xargs

# View commits to be included
git log origin/main..HEAD --oneline

# View full diff
git diff origin/main...HEAD

# Run validations (CuruKicks)
./vendor/bin/pint --test        # Laravel Pint

# Check for TODOs in changes
git diff origin/main...HEAD | grep -i "TODO\|FIXME\|XXX"

# Push with upstream tracking
git push -u origin <branch-name>

# Check available labels before adding
gh label list --limit 50

# Create PR with labels (only if they exist in the repo)
gh pr create --base main --assignee @me --label "feat,refactor"

# Create PR without labels (if repo has no matching labels)
gh pr create --base main --assignee @me

# Create draft PR
gh pr create --base main --assignee @me --draft

# View existing PR for current branch
gh pr view

# Check gh authentication
gh auth status
```

## Resources

- **GitHub CLI docs**: https://cli.github.com/manual/gh_pr_create
- **Related skill**: See [commit](../commit/SKILL.md) for commit message conventions
