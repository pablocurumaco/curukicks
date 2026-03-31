---
name: skill-sync
description: >
  Syncs skill metadata to Auto-invoke sections in the source of truth file (CLAUDE.md or AGENTS.md).
  Trigger: When updating skill metadata (metadata.scope/metadata.auto_invoke), regenerating Auto-invoke tables, or running ./skills/skill-sync/assets/sync.sh (including --dry-run/--scope/--source).
license: Apache-2.0
metadata:
  author: pablocurumaco
  version: "1.1"
  scope: [root]
  auto_invoke:
    - "After creating/modifying a skill"
    - "Regenerate Auto-invoke tables (sync.sh)"
    - "Troubleshoot why a skill is missing from auto-invoke"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash
---

## Purpose

Keeps Auto-invoke sections in sync with skill metadata. When you create or modify a skill, run the sync script to automatically update the source of truth file (CLAUDE.md or AGENTS.md). Then run `./skills/setup.sh` to distribute to all AI assistants.

## Required Skill Metadata

Each skill that should appear in Auto-invoke sections needs these fields in `metadata`.

`auto_invoke` can be either a single string **or** a list of actions:

```yaml
metadata:
  author: pablocurumaco
  version: "1.0"
  scope: [root]                                  # Which files to update: root

  # Option A: single action
  auto_invoke: "Creating/modifying components"

  # Option B: multiple actions
  # auto_invoke:
  #   - "Creating/modifying components"
  #   - "Refactoring component folder placement"
```

### Scope Values

| Scope | Updates (default source) |
|-------|-------------------------|
| `root` | `AGENTS.md` or `CLAUDE.md` (repo root) |

Use `--source claude` to write to CLAUDE.md instead of AGENTS.md.

---

## Usage

### After Creating/Modifying a Skill

```bash
./skills/skill-sync/assets/sync.sh
```

### What It Does

1. Reads all `skills/*/SKILL.md` files
2. Extracts `metadata.scope` and `metadata.auto_invoke`
3. Generates Auto-invoke tables for each AGENTS.md
4. Updates the `### Auto-invoke Skills` section in each file

---

## Example

Given this skill metadata:

```yaml
# skills/commit/SKILL.md
metadata:
  author: pablocurumaco
  version: "1.0"
  scope: [root]
  auto_invoke: "Committing changes to git"
```

The sync script generates in the source of truth file (e.g., `CLAUDE.md`):

```markdown
### Auto-invoke Skills

When performing these actions, ALWAYS invoke the corresponding skill FIRST:

| Action | Skill |
|--------|-------|
| Committing changes to git | `commit` |
```

---

## Commands

```bash
# Sync to AGENTS.md (default)
./skills/skill-sync/assets/sync.sh

# Sync to CLAUDE.md (if that's your source of truth)
./skills/skill-sync/assets/sync.sh --source claude

# Dry run (show what would change)
./skills/skill-sync/assets/sync.sh --dry-run

# Sync specific scope only
./skills/skill-sync/assets/sync.sh --scope root

# Combine flags
./skills/skill-sync/assets/sync.sh --source claude --dry-run
```

---

## Full Workflow

```bash
# 1. Create/modify a skill
# 2. Sync auto-invoke tables to your source of truth
./skills/skill-sync/assets/sync.sh --source claude

# 3. Distribute to all AI assistants
./skills/setup.sh --all --source claude
```

---

## Checklist After Modifying Skills

- [ ] Added `metadata.scope` to new/modified skill
- [ ] Added `metadata.auto_invoke` with action description
- [ ] Ran `./skills/skill-sync/assets/sync.sh --source claude`
- [ ] Ran `./skills/setup.sh --all --source claude` to distribute
- [ ] Verified source of truth file updated correctly
