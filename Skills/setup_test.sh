#!/bin/bash
# Unit tests for setup.sh
# Run: ./skills/setup_test.sh
#
# shellcheck disable=SC2317
# Reason: Test functions are discovered and called dynamically via declare -F

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SETUP_SCRIPT="$SCRIPT_DIR/setup.sh"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Test counters
TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0

# Test environment
TEST_DIR=""

# =============================================================================
# TEST FRAMEWORK
# =============================================================================

setup_test_env() {
    TEST_DIR=$(mktemp -d)

    # Create mock repo structure
    mkdir -p "$TEST_DIR/skills/typescript"
    mkdir -p "$TEST_DIR/skills/react-19"
    mkdir -p "$TEST_DIR/.github"

    # Create mock SKILL.md files
    echo "# TypeScript Skill" > "$TEST_DIR/skills/typescript/SKILL.md"
    echo "# React 19 Skill" > "$TEST_DIR/skills/react-19/SKILL.md"

    # Create mock AGENTS.md and CLAUDE.md files
    echo "# Root AGENTS" > "$TEST_DIR/AGENTS.md"
    echo "# Root CLAUDE" > "$TEST_DIR/CLAUDE.md"

    # Copy setup.sh to test dir
    cp "$SETUP_SCRIPT" "$TEST_DIR/skills/setup.sh"
}

teardown_test_env() {
    if [ -n "$TEST_DIR" ] && [ -d "$TEST_DIR" ]; then
        rm -rf "$TEST_DIR"
    fi
}

run_setup() {
    (cd "$TEST_DIR/skills" && bash setup.sh "$@" 2>&1)
}

# Assertions return 0 on success, 1 on failure
assert_equals() {
    local expected="$1" actual="$2" message="$3"
    if [ "$expected" = "$actual" ]; then
        return 0
    fi
    echo -e "${RED}  FAIL: $message${NC}"
    echo "    Expected: $expected"
    echo "    Actual:   $actual"
    return 1
}

assert_contains() {
    local haystack="$1" needle="$2" message="$3"
    if echo "$haystack" | grep -q -F -- "$needle"; then
        return 0
    fi
    echo -e "${RED}  FAIL: $message${NC}"
    echo "    String not found: $needle"
    return 1
}

assert_file_exists() {
    local file="$1" message="$2"
    if [ -f "$file" ]; then
        return 0
    fi
    echo -e "${RED}  FAIL: $message${NC}"
    echo "    File not found: $file"
    return 1
}

assert_file_not_exists() {
    local file="$1" message="$2"
    if [ ! -f "$file" ]; then
        return 0
    fi
    echo -e "${RED}  FAIL: $message${NC}"
    echo "    File should not exist: $file"
    return 1
}

assert_symlink_exists() {
    local link="$1" message="$2"
    if [ -L "$link" ]; then
        return 0
    fi
    echo -e "${RED}  FAIL: $message${NC}"
    echo "    Symlink not found: $link"
    return 1
}

assert_symlink_not_exists() {
    local link="$1" message="$2"
    if [ ! -L "$link" ]; then
        return 0
    fi
    echo -e "${RED}  FAIL: $message${NC}"
    echo "    Symlink should not exist: $link"
    return 1
}

assert_dir_exists() {
    local dir="$1" message="$2"
    if [ -d "$dir" ]; then
        return 0
    fi
    echo -e "${RED}  FAIL: $message${NC}"
    echo "    Directory not found: $dir"
    return 1
}

# =============================================================================
# TESTS: FLAG PARSING
# =============================================================================

test_flag_help_shows_usage() {
    local output
    output=$(run_setup --help)
    assert_contains "$output" "Usage:" "Help should show usage" && \
    assert_contains "$output" "--all" "Help should mention --all flag" && \
    assert_contains "$output" "--claude" "Help should mention --claude flag" && \
    assert_contains "$output" "--source" "Help should mention --source flag"
}

test_flag_unknown_reports_error() {
    local output
    output=$(run_setup --unknown 2>&1) || true
    assert_contains "$output" "Unknown option" "Should report unknown option"
}

test_flag_all_configures_everything() {
    local output
    output=$(run_setup --all)
    assert_contains "$output" "Claude Code" "Should setup Claude" && \
    assert_contains "$output" "Gemini CLI" "Should setup Gemini" && \
    assert_contains "$output" "Codex" "Should setup Codex" && \
    assert_contains "$output" "Antigravity" "Should setup Antigravity"
}

test_flag_single_claude() {
    local output
    output=$(run_setup --claude)
    assert_contains "$output" "Claude Code" "Should setup Claude" && \
    assert_contains "$output" "[1/1]" "Should show 1/1 steps"
}

test_flag_multiple_combined() {
    local output
    output=$(run_setup --claude --codex)
    assert_contains "$output" "[1/2]" "Should show step 1/2" && \
    assert_contains "$output" "[2/2]" "Should show step 2/2"
}

# =============================================================================
# TESTS: SYMLINK CREATION
# =============================================================================

test_symlink_claude_created() {
    run_setup --claude > /dev/null
    assert_symlink_exists "$TEST_DIR/.claude/skills" "Claude skills symlink should exist"
}

test_symlink_gemini_created() {
    run_setup --gemini > /dev/null
    assert_symlink_exists "$TEST_DIR/.gemini/skills" "Gemini skills symlink should exist"
}

test_symlink_codex_created() {
    run_setup --codex > /dev/null
    assert_symlink_exists "$TEST_DIR/.codex/skills" "Codex skills symlink should exist"
}

test_symlink_antigravity_created() {
    run_setup --antigravity > /dev/null
    assert_symlink_exists "$TEST_DIR/.agent/skills" "Antigravity skills symlink should exist"
}

test_symlink_not_created_without_flag() {
    run_setup --codex > /dev/null
    assert_symlink_not_exists "$TEST_DIR/.claude/skills" "Claude symlink should not exist" && \
    assert_symlink_not_exists "$TEST_DIR/.gemini/skills" "Gemini symlink should not exist" && \
    assert_symlink_not_exists "$TEST_DIR/.agent/skills" "Antigravity symlink should not exist"
}

# =============================================================================
# TESTS: MD FILE COPYING
# =============================================================================

test_copy_claude_syncs_from_source() {
    run_setup --claude --source agents > /dev/null
    assert_file_exists "$TEST_DIR/CLAUDE.md" "Root CLAUDE.md should exist"
}

test_copy_gemini_syncs_from_source() {
    run_setup --gemini --source agents > /dev/null
    assert_file_exists "$TEST_DIR/GEMINI.md" "Root GEMINI.md should exist"
}

test_copy_codex_no_extra_files() {
    run_setup --codex > /dev/null
    assert_file_not_exists "$TEST_DIR/CODEX.md" "CODEX.md should not be created"
}

test_copy_antigravity_no_extra_files() {
    run_setup --antigravity > /dev/null
    assert_file_not_exists "$TEST_DIR/ANTIGRAVITY.md" "ANTIGRAVITY.md should not be created"
}

# =============================================================================
# TESTS: DIRECTORY CREATION
# =============================================================================

test_dir_claude_created() {
    rm -rf "$TEST_DIR/.claude"
    run_setup --claude > /dev/null
    assert_dir_exists "$TEST_DIR/.claude" ".claude directory should be created"
}

test_dir_gemini_created() {
    rm -rf "$TEST_DIR/.gemini"
    run_setup --gemini > /dev/null
    assert_dir_exists "$TEST_DIR/.gemini" ".gemini directory should be created"
}

test_dir_codex_created() {
    rm -rf "$TEST_DIR/.codex"
    run_setup --codex > /dev/null
    assert_dir_exists "$TEST_DIR/.codex" ".codex directory should be created"
}

test_dir_antigravity_created() {
    rm -rf "$TEST_DIR/.agent"
    run_setup --antigravity > /dev/null
    assert_dir_exists "$TEST_DIR/.agent" ".agent directory should be created"
}

# =============================================================================
# TESTS: SOURCE OF TRUTH
# =============================================================================

test_source_default_is_claude() {
    local output
    output=$(run_setup --claude)
    assert_contains "$output" "Source of truth:" "Should show source of truth" && \
    assert_contains "$output" "CLAUDE.md" "Default source should be CLAUDE.md"
}

test_source_agents_flag() {
    local output
    output=$(run_setup --claude --source agents)
    assert_contains "$output" "AGENTS.md" "Source should be AGENTS.md"
}

test_source_claude_syncs_agents() {
    echo "# Claude is source" > "$TEST_DIR/CLAUDE.md"
    run_setup --codex --source claude > /dev/null
    local source_content target_content
    source_content=$(cat "$TEST_DIR/CLAUDE.md")
    target_content=$(cat "$TEST_DIR/AGENTS.md")
    assert_equals "$source_content" "$target_content" "AGENTS.md should be synced from CLAUDE.md"
}

test_source_claude_skips_self_copy() {
    echo "# Claude is source" > "$TEST_DIR/CLAUDE.md"
    local output
    output=$(run_setup --claude --source claude)
    assert_contains "$output" "source of truth (skipped)" "Should skip copying to self"
}

test_source_invalid_reports_error() {
    local output
    output=$(run_setup --claude --source invalid 2>&1) || true
    assert_contains "$output" "Invalid source" "Should report invalid source"
}

# =============================================================================
# TESTS: IDEMPOTENCY
# =============================================================================

test_idempotent_multiple_runs() {
    run_setup --claude > /dev/null
    run_setup --claude > /dev/null
    assert_symlink_exists "$TEST_DIR/.claude/skills" "Symlink should still exist after second run"
}

# =============================================================================
# TEST RUNNER (autodiscovery)
# =============================================================================

run_all_tests() {
    local test_functions current_section=""

    # Discover all test_* functions
    test_functions=$(declare -F | awk '{print $3}' | grep '^test_' | sort)

    for test_func in $test_functions; do
        # Extract section from function name (e.g., test_flag_* -> "Flag")
        local section
        section=$(echo "$test_func" | sed 's/^test_//' | cut -d'_' -f1)
        section="$(echo "${section:0:1}" | tr '[:lower:]' '[:upper:]')${section:1}"

        # Print section header if changed
        if [ "$section" != "$current_section" ]; then
            [ -n "$current_section" ] && echo ""
            echo -e "${YELLOW}${section} tests:${NC}"
            current_section="$section"
        fi

        # Convert function name to readable test name
        local test_name
        test_name=$(echo "$test_func" | sed 's/^test_//' | tr '_' ' ')

        TESTS_RUN=$((TESTS_RUN + 1))
        echo -n "  $test_name... "

        setup_test_env

        if $test_func; then
            echo -e "${GREEN}PASS${NC}"
            TESTS_PASSED=$((TESTS_PASSED + 1))
        else
            TESTS_FAILED=$((TESTS_FAILED + 1))
        fi

        teardown_test_env
    done
}

# =============================================================================
# MAIN
# =============================================================================

echo ""
echo "🧪 Running setup.sh unit tests"
echo "==============================="
echo ""

run_all_tests

echo ""
echo "==============================="
if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ All $TESTS_RUN tests passed!${NC}"
    exit 0
else
    echo -e "${RED}❌ $TESTS_FAILED of $TESTS_RUN tests failed${NC}"
    exit 1
fi
