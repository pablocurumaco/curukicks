# AI Agent Skills

This directory contains **Agent Skills** following the [Agent Skills open standard](https://agentskills.io). Skills provide domain-specific patterns, conventions, and guardrails that help AI coding assistants (Claude Code, OpenCode, Codex, Gemini, etc.) understand project-specific requirements.

## Setup

Run the setup script to configure skills for all supported AI coding assistants:

```bash
./Skills/setup.sh
```

This creates symlinks so each tool finds skills in its expected location:

| Tool | Symlink Created |
|------|-----------------|
| Claude Code / OpenCode | `.claude/skills/` |
| Codex (OpenAI) | `.codex/skills/` |
| Gemini CLI | `.gemini/skills/` |
| Antigravity | `.agent/skills/` |

## Available Skills

### Backend / Admin

| Skill | Description |
|-------|-------------|
| `filament-5` | Filament 5.4 resources, forms (Schema), tables, widgets, enums |
| `pest` | Pest 4.4 testing for Laravel 13 + Filament, factories, datasets |

### Styling

| Skill | Description |
|-------|-------------|
| `tailwind-4` | Tailwind CSS 4 patterns for Blade templates, @class directive, theme conventions |

### Workflow

| Skill | Description |
|-------|-------------|
| `commit` | Conventional commits (feat, fix, docs, etc.) |
| `create-pr` | GitHub Pull Requests with Pint validation and TODO detection |

### Meta

| Skill | Description |
|-------|-------------|
| `skill-creator` | Create new AI agent skills |
| `skill-sync` | Sync skill metadata to CLAUDE.md auto-invoke sections |

## Creating New Skills

Use the `skill-creator` skill for guidance:

```
Read Skills/skill-creator/SKILL.md
```

## Directory Structure

```
Skills/
├── {skill-name}/
│   ├── SKILL.md              # Required - main instruction and metadata
│   ├── scripts/              # Optional - executable code
│   ├── assets/               # Optional - templates, schemas, resources
│   └── references/           # Optional - links to local docs
├── setup.sh                  # Configure AI assistants
├── setup_test.sh             # Tests for setup.sh
└── README.md                 # This file
```

## Resources

- [Agent Skills Standard](https://agentskills.io) - Open standard specification
