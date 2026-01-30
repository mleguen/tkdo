# GitHub Copilot Code Review Instructions

## Workflow Reference

Follow the adversarial code review workflow defined in:
`_bmad/bmm/workflows/4-implementation/code-review/workflow.yaml`

Use project coding standards from:
`_bmad-output/project-context.md`

## Automation Context

This is an **automated review** with no human operator. You cannot ask questions or request clarification.

**Collect all review inputs from the PR itself:**
- PR title and description for stated objectives
- Changed files diff for implementation review
- Commit messages for change context
- Existing codebase for architecture and pattern validation

**Adapt the workflow for automation:**
- Skip any interactive prompts or user questions
- Do not offer to fix issues - only report findings
- Do not update story files or sprint status
- Output findings directly in the PR review

**Static analysis only:**
- You can ONLY read files and analyze code statically
- Do NOT modify any files or attempt fixes
- Do NOT run any commands (e.g., tests, linters, build scripts)
- Do NOT execute documented commands to verify they work
- Base all findings on code inspection, not execution

## Output Format

Provide your review as GitHub PR review comments with:
- Inline comments on specific lines for file-specific issues
- Summary comment categorizing all findings by severity (Critical/High/Medium/Low)
- Minimum 3 specific, actionable issues per non-trivial PR

## Excluded Paths

Do not review files in: `_bmad/`, `_bmad-output/`, `.claude/`, `.cursor/`, `.windsurf/`, `.gemini/`, `.github/agents/`
