---
name: commit
description: >
  Review changes and create a git commit with conventional commit format.
  Trigger: When user asks to commit, save changes, or run /commit.
license: Apache-2.0
metadata:
  author: pablocurumaco
  version: "1.0"
  scope: [root]
  auto_invoke: "Committing changes to git"
allowed-tools: Bash, Read, Glob, Grep
---

## When to Use

- User asks to commit, save changes, or run `/commit`
- User says "commit this", "save my work", "create a commit"
- After completing a task when user explicitly requests a commit

---

## Critical Patterns

### ALWAYS

- Use conventional commit format: `type: description`
- Write commit messages in English
- Ask for confirmation before committing
- Focus on WHAT changed, not HOW it was implemented
- Use HEREDOC for commit messages
- Keep commits focused and atomic

### NEVER

- Add "Co-Authored-By" lines
- Add AI attribution or signatures
- Use emojis in commit messages
- Mention Claude, AI, or assistants
- Commit `.env`, credentials, or private keys
- Create empty commits

---

## Workflow

1. **Gather information (run in parallel):**

```bash
git status
git diff
git diff --cached
git log --oneline -5
git branch --show-current
```

2. **Analyze changes:**
   - Understand scope and impact
   - Identify what changed and why
   - Determine appropriate type (feat, fix, docs, etc.)
   - Keep analysis simple and direct

3. **Stage appropriate files:**
   - Add relevant files with `git add`
   - Exclude files that shouldn't be committed
   - If user specified files, only stage those

4. **Draft commit message and ask for confirmation**

5. **Execute commit**

6. **Verify commit:**
```bash
git log -1 --stat
```

---

## Commit Message Format

```
type: short descriptive title (max 50 chars)

- Detail 1 (ONLY if necessary)
- Detail 2 (ONLY if necessary)
```

### Commit Types

| Type | When to Use |
|------|-------------|
| `feat` | New feature or functionality |
| `fix` | Bug fix |
| `docs` | Documentation changes only |
| `style` | Code formatting, missing semicolons, etc. (no code change) |
| `refactor` | Code restructuring without changing behavior |
| `test` | Adding or updating tests |
| `chore` | Maintenance tasks, dependency updates, config changes |
| `perf` | Performance improvements |

### Good Examples

```
feat: add appointment scheduling module
```

```
fix: correct user role assignment timestamp
```

```
docs: update database configuration table
```

```
refactor: extract patient validation to separate method

- Improve code reusability
- Add type hints
```

```
chore: upgrade Laravel to 12.0
```

### Bad Examples

```
❌ feat: 🚀 Added a new feature for scheduling appointments
❌ Fixed the bug where users couldn't register properly
❌ Updated UserController to use validation rules...
❌ feat: implement appointment module (Co-Authored-By: Claude)
```

---

## Commands

```bash
# Execute commit with HEREDOC
git add <files>
git commit -m "$(cat <<'EOF'
type: description

- Detail (if any)
EOF
)"

# Verify after commit
git log -1 --stat
```

---

## Arguments

If `$ARGUMENTS` is provided, use it as guidance for:
- Which files to include
- Commit message hints
- Scope of the commit

---

## Project-Specific Notes

**CuruKicks Conventions:**
- Follow conventional commits strictly
- No ticket/issue prefixes
- Backend changes: Laravel 13, PHP, Eloquent, Filament 5.4
- Frontend changes: Blade templates, Tailwind CSS 4
- Database: Migrations, seeders, model changes

**Common Patterns:**
- `feat: add [module] resource` - New Filament resource
- `feat: add [feature] to catalog` - Public catalog changes
- `fix: correct [issue] in [module]` - Bug fixes
- `refactor: standardize [pattern] in [module]` - Code improvements
- `chore: update dependencies` - Maintenance
