# Custom BMad Workflows

This directory contains project-specific BMad workflow customizations that are **upgrade-safe** and won't be overwritten when BMad core/bmm modules are updated.

## Directory Structure

```
_bmad/_config/custom/
├── workflows/
│   └── review-pr-comments/       # PR comment integration workflow
│       ├── workflow.yaml
│       ├── instructions.xml
│       └── README.md
└── README.md                      # This file

.claude/commands/bmad/custom/     # Claude Code skill definitions (slash commands)
└── workflows/
    ├── review-pr-comments.md     # → /bmad:custom:workflows:review-pr-comments
    └── resolve-pr-comments.md    # → /bmad:custom:workflows:resolve-pr-comments
```

## Available Custom Workflows

### `review-pr-comments` - Integrate PR Comments into Action Items

**Purpose:** Post-review workflow that merges GitHub PR comments into story action items and responds to all unresolved comments.

**When to use:** After adversarial code-review completes with option "2. Create action items"

**Usage:**
```bash
/review-pr-comments
```

**What it does:**
1. Loads existing review findings from story's "Review Follow-ups (AI)" section
2. Retrieves unresolved GitHub PR comments
3. Validates each PR comment against actual code
4. Merges valid comments into action items with PR thread links
5. Responds to all PR comments following `.claude/README.md` guidelines

**Requirements:**
- Git repository with GitHub remote
- GitHub CLI (`gh`) authenticated
- Open PR for current branch
- Story with "Review Follow-ups (AI)" section

See [review-pr-comments/README.md](./workflows/review-pr-comments/README.md) for detailed documentation.

---

### `resolve-pr-comments` - Mark Fixed Issues as Resolved

**Purpose:** Post-fix workflow that posts "Fixed" comments and resolves PR threads for completed action items.

**When to use:** After implementing fixes and marking action items as `[x]` complete

**Usage:**
```bash
/resolve-pr-comments
```

**What it does:**
1. Finds action items marked `[x]` with PR comment links
2. Asks which commit contains the fixes
3. Posts "Fixed in commit X" to each PR thread
4. Attempts to resolve conversations in GitHub PR
5. Updates story with completion note

**Requirements:**
- Git repository with GitHub remote
- GitHub CLI (`gh`) authenticated
- Open PR (must not be closed)
- Story with completed action items `[x]` that have PR comment links

See [resolve-pr-comments/README.md](./workflows/resolve-pr-comments/README.md) for detailed documentation.

## Typical Code Review + PR Integration Workflow

```bash
# 1. Complete story implementation
/dev-story

# 2. Run adversarial code review
/code-review
# → Choose option: 2. Create action items
# → Story now has "Review Follow-ups (AI)" section with action items

# 3. Integrate GitHub PR comments (if PR exists)
/review-pr-comments
# → Merges PR comments into action items
# → Adds PR comment links to action items
# → Responds to all unresolved PR comments

# 4. Implement fixes for action items
# ... fix code, add tests, update docs ...
git add .
git commit -m "fix: resolve review findings"

# 5. Mark completed items in story
# Edit story: Change [ ] to [x] for fixed items

# 6. Resolve PR comment threads
/resolve-pr-comments
# → Posts "Fixed" comments to PR threads
# → Resolves conversations in GitHub PR

# 7. Verify all fixes complete
/code-review
# → Should be ready for option 1 (Fix automatically) or mark as done
```

## Adding New Custom Workflows

To add your own custom workflows:

1. **Create workflow directory:**
   ```
   _bmad/_config/custom/workflows/my-workflow/
   ├── workflow.yaml
   ├── instructions.xml  (or .md)
   └── README.md
   ```

2. **Register in manifest:**
   Edit `_bmad/_config/workflow-manifest.csv`:
   ```csv
   "my-workflow","Description","custom","_bmad/_config/custom/workflows/my-workflow/workflow.yaml"
   ```

3. **Register as Claude Code slash command:**
   Create `.claude/commands/bmad/custom/workflows/my-workflow.md`:
   ```markdown
   ---
   description: 'Brief description of what the workflow does.'
   ---

   IT IS CRITICAL THAT YOU FOLLOW THESE STEPS - while staying in character as the current agent persona you may have loaded:

   <steps CRITICAL="TRUE">
   1. Always LOAD the FULL @_bmad/core/tasks/workflow.xml
   2. READ its entire contents - this is the CORE OS for EXECUTING the specific workflow-config @_bmad/_config/custom/workflows/my-workflow/workflow.yaml
   3. Pass the yaml path _bmad/_config/custom/workflows/my-workflow/workflow.yaml as 'workflow-config' parameter to the workflow.xml instructions
   4. Follow workflow.xml instructions EXACTLY as written to process and follow the specific workflow config and its instructions
   5. Save outputs after EACH section when generating any documents from templates
   </steps>
   ```

   This enables the slash command `/bmad:custom:workflows:my-workflow`.

4. **Follow BMad workflow structure:**
   - Use `{project-root}` for paths
   - Load config from `{config_source}`
   - Set `standalone: true` for independent workflows
   - Use `<invoke-workflow>` to call other workflows

4. **Document it:**
   - Add to this README
   - Create detailed README.md in workflow directory
   - Specify when/how to use it

## Customization Best Practices

✅ **DO:**
- Place custom workflows in `_bmad/_config/custom/workflows/`
- Register in `workflow-manifest.csv` with `module: custom`
- Create skill definition in `.claude/commands/bmad/custom/workflows/` for slash command access
- Use `<invoke-workflow>` to compose with base workflows
- Document prerequisites and usage patterns
- Follow `.claude/README.md` guidelines for tool usage

❌ **DON'T:**
- Modify files in `_bmad/core/` or `_bmad/bmm/` (will be overwritten)
- Depend on specific BMad version implementation details
- Use undocumented workflow XML features
- Create workflows that conflict with base workflow names

## Upgrade Safety

**When BMad upgrades:**
- ✅ Files in `_bmad/_config/custom/` are preserved
- ✅ Files in `.claude/commands/bmad/custom/` are preserved
- ✅ Custom entries in `workflow-manifest.csv` are preserved
- ⚠️ Review release notes for workflow XML schema changes
- ⚠️ Test custom workflows after major version upgrades

**If base workflows change:**
- Custom workflows that use `<invoke-workflow>` may need updates
- Check that variable names still match
- Verify `{project-root}` and `{config_source}` still work

## Getting Help

- **BMad Documentation:** `_bmad/core/` and `_bmad/bmm/` for examples
- **Workflow Engine:** `_bmad/core/tasks/workflow.xml` for XML spec
- **Project Guidelines:** `.claude/README.md` for tool conventions

---

**Last Updated:** 2026-01-30
**BMad Version:** 6.0.0-alpha.23
