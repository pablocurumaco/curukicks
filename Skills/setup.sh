#!/bin/bash
# Setup AI Skills for HEG System development
# Configures AI coding assistants that follow agentskills.io standard:
#   - Claude Code: .claude/skills/ symlink + CLAUDE.md copies
#   - Gemini CLI: .gemini/skills/ symlink + GEMINI.md copies
#   - Codex (OpenAI): .codex/skills/ symlink + AGENTS.md (native)
#   - Antigravity: .agent/skills/ symlink + AGENTS.md (native)
#
# Usage:
#   ./setup.sh              # Interactive mode (select AI assistants)
#   ./setup.sh --all        # Configure all AI assistants
#   ./setup.sh --claude     # Configure only Claude Code
#   ./setup.sh --claude --codex  # Configure multiple

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(dirname "$SCRIPT_DIR")"
SKILLS_SOURCE="$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Selection flags
SETUP_CLAUDE=false
SETUP_GEMINI=false
SETUP_CODEX=false
SETUP_ANTIGRAVITY=false

# Source of truth (AGENTS.md or CLAUDE.md)
SOURCE_FILE="CLAUDE.md"

# =============================================================================
# HELPER FUNCTIONS
# =============================================================================

show_help() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Configure AI coding assistants for HEG System development."
    echo ""
    echo "Options:"
    echo "  --all           Configure all AI assistants"
    echo "  --claude        Configure Claude Code"
    echo "  --gemini        Configure Gemini CLI"
    echo "  --codex         Configure Codex (OpenAI)"
    echo "  --antigravity   Configure Antigravity"
    echo "  --source FILE   Source of truth: agents or claude (default: claude)"
    echo "  --help          Show this help message"
    echo ""
    echo "If no options provided, runs in interactive mode."
    echo ""
    echo "Examples:"
    echo "  $0                              # Interactive selection"
    echo "  $0 --all                        # All AI assistants"
    echo "  $0 --claude --codex             # Only Claude and Codex"
    echo "  $0 --all --source agents        # All AIs, AGENTS.md as source"
}

show_menu() {
    echo -e "${BOLD}Which AI assistants do you use?${NC}"
    echo -e "${CYAN}(Use numbers to toggle, Enter to confirm)${NC}"
    echo ""

    local options=("Claude Code" "Gemini CLI" "Codex (OpenAI)" "Antigravity")
    local selected=(true false false false)  # Claude selected by default

    while true; do
        for i in "${!options[@]}"; do
            if [ "${selected[$i]}" = true ]; then
                echo -e "  ${GREEN}[x]${NC} $((i+1)). ${options[$i]}"
            else
                echo -e "  [ ] $((i+1)). ${options[$i]}"
            fi
        done
        echo ""
        echo -e "  ${YELLOW}a${NC}. Select all"
        echo -e "  ${YELLOW}n${NC}. Select none"
        echo ""
        echo -n "Toggle (1-4, a, n) or Enter to confirm: "

        read -r choice

        case $choice in
            1) selected[0]=$([ "${selected[0]}" = true ] && echo false || echo true) ;;
            2) selected[1]=$([ "${selected[1]}" = true ] && echo false || echo true) ;;
            3) selected[2]=$([ "${selected[2]}" = true ] && echo false || echo true) ;;
            4) selected[3]=$([ "${selected[3]}" = true ] && echo false || echo true) ;;
            a|A) selected=(true true true true) ;;
            n|N) selected=(false false false false) ;;
            "") break ;;
            *) echo -e "${RED}Invalid option${NC}" ;;
        esac

        # Move cursor up to redraw menu
        echo -en "\033[10A\033[J"
    done

    SETUP_CLAUDE=${selected[0]}
    SETUP_GEMINI=${selected[1]}
    SETUP_CODEX=${selected[2]}
    SETUP_ANTIGRAVITY=${selected[3]}
}

show_source_menu() {
    echo -e "${BOLD}Which file is the source of truth?${NC}"
    echo -e "${CYAN}All other AI instruction files will be synced from this one.${NC}"
    echo ""
    echo -e "  1. CLAUDE.md ${YELLOW}(default - Claude Code as primary)${NC}"
    echo -e "  2. AGENTS.md ${YELLOW}(multi-AI teams)${NC}"
    echo ""
    echo -n "Select (1-2, Enter for default): "

    read -r source_choice

    case $source_choice in
        2) SOURCE_FILE="AGENTS.md" ;;
        *) SOURCE_FILE="CLAUDE.md" ;;
    esac
}

setup_claude() {
    local target="$REPO_ROOT/.claude/skills"

    if [ ! -d "$REPO_ROOT/.claude" ]; then
        mkdir -p "$REPO_ROOT/.claude"
    fi

    if [ -L "$target" ]; then
        rm "$target"
    elif [ -d "$target" ]; then
        mv "$target" "$REPO_ROOT/.claude/skills.backup.$(date +%s)"
    fi

    ln -s "$SKILLS_SOURCE" "$target"
    echo -e "${GREEN}  ✓ .claude/skills -> skills/${NC}"

    # Sync source of truth to CLAUDE.md
    copy_source_md "CLAUDE.md"
}

setup_gemini() {
    local target="$REPO_ROOT/.gemini/skills"

    if [ ! -d "$REPO_ROOT/.gemini" ]; then
        mkdir -p "$REPO_ROOT/.gemini"
    fi

    if [ -L "$target" ]; then
        rm "$target"
    elif [ -d "$target" ]; then
        mv "$target" "$REPO_ROOT/.gemini/skills.backup.$(date +%s)"
    fi

    ln -s "$SKILLS_SOURCE" "$target"
    echo -e "${GREEN}  ✓ .gemini/skills -> skills/${NC}"

    # Sync source of truth to GEMINI.md
    copy_source_md "GEMINI.md"
}

setup_codex() {
    local target="$REPO_ROOT/.codex/skills"

    if [ ! -d "$REPO_ROOT/.codex" ]; then
        mkdir -p "$REPO_ROOT/.codex"
    fi

    if [ -L "$target" ]; then
        rm "$target"
    elif [ -d "$target" ]; then
        mv "$target" "$REPO_ROOT/.codex/skills.backup.$(date +%s)"
    fi

    ln -s "$SKILLS_SOURCE" "$target"
    echo -e "${GREEN}  ✓ .codex/skills -> skills/${NC}"
    echo -e "${GREEN}  ✓ Codex uses AGENTS.md natively${NC}"
}

setup_antigravity() {
    local target="$REPO_ROOT/.agent/skills"

    if [ ! -d "$REPO_ROOT/.agent" ]; then
        mkdir -p "$REPO_ROOT/.agent"
    fi

    if [ -L "$target" ]; then
        rm "$target"
    elif [ -d "$target" ]; then
        mv "$target" "$REPO_ROOT/.agent/skills.backup.$(date +%s)"
    fi

    ln -s "$SKILLS_SOURCE" "$target"
    echo -e "${GREEN}  ✓ .agent/skills -> skills/${NC}"
    echo -e "${GREEN}  ✓ Antigravity uses AGENTS.md natively${NC}"
}

copy_source_md() {
    local target_name="$1"
    local source_files
    local count=0

    # Skip if target is the same as source
    if [ "$target_name" = "$SOURCE_FILE" ]; then
        echo -e "${GREEN}  ✓ $target_name is the source of truth (skipped)${NC}"
        return
    fi

    source_files=$(find "$REPO_ROOT" -name "$SOURCE_FILE" -not -path "*/node_modules/*" -not -path "*/.git/*" 2>/dev/null)

    for source_file in $source_files; do
        local source_dir
        source_dir=$(dirname "$source_file")
        cp "$source_file" "$source_dir/$target_name"
        count=$((count + 1))
    done

    echo -e "${GREEN}  ✓ Copied $count $SOURCE_FILE -> $target_name${NC}"
}

# =============================================================================
# PARSE ARGUMENTS
# =============================================================================

while [[ $# -gt 0 ]]; do
    case $1 in
        --all)
            SETUP_CLAUDE=true
            SETUP_GEMINI=true
            SETUP_CODEX=true
            SETUP_ANTIGRAVITY=true
            shift
            ;;
        --claude)
            SETUP_CLAUDE=true
            shift
            ;;
        --gemini)
            SETUP_GEMINI=true
            shift
            ;;
        --codex)
            SETUP_CODEX=true
            shift
            ;;
        --antigravity)
            SETUP_ANTIGRAVITY=true
            shift
            ;;
        --source)
            case "$2" in
                claude) SOURCE_FILE="CLAUDE.md" ;;
                agents) SOURCE_FILE="AGENTS.md" ;;
                *)
                    echo -e "${RED}Invalid source: $2 (use 'agents' or 'claude')${NC}"
                    exit 1
                    ;;
            esac
            shift 2
            ;;
        --help|-h)
            show_help
            exit 0
            ;;
        *)
            echo -e "${RED}Unknown option: $1${NC}"
            show_help
            exit 1
            ;;
    esac
done

# =============================================================================
# MAIN
# =============================================================================

echo "🤖 HEG System AI Skills Setup"
echo "=============================="
echo ""

# Count skills
SKILL_COUNT=$(find "$SKILLS_SOURCE" -maxdepth 2 -name "SKILL.md" | wc -l | tr -d ' ')

if [ "$SKILL_COUNT" -eq 0 ]; then
    echo -e "${RED}No skills found in $SKILLS_SOURCE${NC}"
    exit 1
fi

echo -e "${BLUE}Found $SKILL_COUNT skills to configure${NC}"
echo ""

# Interactive mode if no flags provided
if [ "$SETUP_CLAUDE" = false ] && [ "$SETUP_GEMINI" = false ] && [ "$SETUP_CODEX" = false ] && [ "$SETUP_ANTIGRAVITY" = false ]; then
    show_source_menu
    echo ""
    show_menu
    echo ""
fi

# Check if at least one selected
if [ "$SETUP_CLAUDE" = false ] && [ "$SETUP_GEMINI" = false ] && [ "$SETUP_CODEX" = false ] && [ "$SETUP_ANTIGRAVITY" = false ]; then
    echo -e "${YELLOW}No AI assistants selected. Nothing to do.${NC}"
    exit 0
fi

# Run selected setups
STEP=1
TOTAL=0
[ "$SETUP_CLAUDE" = true ] && TOTAL=$((TOTAL + 1))
[ "$SETUP_GEMINI" = true ] && TOTAL=$((TOTAL + 1))
[ "$SETUP_CODEX" = true ] && TOTAL=$((TOTAL + 1))
[ "$SETUP_ANTIGRAVITY" = true ] && TOTAL=$((TOTAL + 1))

if [ "$SETUP_CLAUDE" = true ]; then
    echo -e "${YELLOW}[$STEP/$TOTAL] Setting up Claude Code...${NC}"
    setup_claude
    STEP=$((STEP + 1))
fi

if [ "$SETUP_GEMINI" = true ]; then
    echo -e "${YELLOW}[$STEP/$TOTAL] Setting up Gemini CLI...${NC}"
    setup_gemini
    STEP=$((STEP + 1))
fi

if [ "$SETUP_CODEX" = true ]; then
    echo -e "${YELLOW}[$STEP/$TOTAL] Setting up Codex (OpenAI)...${NC}"
    setup_codex
    STEP=$((STEP + 1))
fi

if [ "$SETUP_ANTIGRAVITY" = true ]; then
    echo -e "${YELLOW}[$STEP/$TOTAL] Setting up Antigravity...${NC}"
    setup_antigravity
fi

# Sync AGENTS.md from source if source is CLAUDE.md
# (Codex and Antigravity use AGENTS.md natively, so it must stay in sync)
if [ "$SOURCE_FILE" = "CLAUDE.md" ]; then
    echo ""
    echo -e "${YELLOW}Syncing AGENTS.md from $SOURCE_FILE...${NC}"
    copy_source_md "AGENTS.md"
fi

# =============================================================================
# SUMMARY
# =============================================================================
echo ""
echo -e "${GREEN}✅ Successfully configured $SKILL_COUNT AI skills!${NC}"
echo ""
echo "Configured:"
[ "$SETUP_CLAUDE" = true ] && echo "  • Claude Code:    .claude/skills/ + CLAUDE.md"
[ "$SETUP_CODEX" = true ] && echo "  • Codex (OpenAI): .codex/skills/ + AGENTS.md (native)"
[ "$SETUP_GEMINI" = true ] && echo "  • Gemini CLI:     .gemini/skills/ + GEMINI.md"
[ "$SETUP_ANTIGRAVITY" = true ] && echo "  • Antigravity:    .agent/skills/ + AGENTS.md (native)"
echo ""
echo -e "${BLUE}Source of truth: ${BOLD}$SOURCE_FILE${NC}"
echo -e "${BLUE}Edit $SOURCE_FILE, then re-run this script to sync all AI assistants.${NC}"
echo -e "${BLUE}Restart your AI assistant to load the skills.${NC}"
