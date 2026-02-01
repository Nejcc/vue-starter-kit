# Creating Skills

Skills give AI agents domain-specific context that loads on demand. When an agent activates a skill, the full `SKILL.md` content is injected into the conversation, providing patterns, code snippets, and conventions for that domain.

## Directory Structure

Each skill lives in its own directory with a single `SKILL.md` file, mirrored in two locations:

```
.claude/skills/<skill-name>/SKILL.md    ← Claude Code
.github/skills/<skill-name>/SKILL.md    ← GitHub Copilot
```

Both files must be identical. The directory name should match the `name` field in the YAML frontmatter.

## File Format

A `SKILL.md` file has two parts: YAML frontmatter and a markdown body.

### Frontmatter

```yaml
---
name: my-skill-name
description: >-
  A concise description of when to activate this skill. This text appears
  in the skill listing and helps agents decide when to load it.
---
```

| Field | Required | Notes |
|-------|----------|-------|
| `name` | Yes | Kebab-case identifier. Must match the directory name. |
| `description` | Yes | One to three sentences. Describe **when** to activate, not what the skill contains. Use `>-` for multi-line (folds to single line, no trailing newline). Can also be a single-line string. |

### Body

The markdown body is freeform, but follow these conventions:

```markdown
# Skill Title

## When to Apply

Bullet list of trigger conditions — what the user or agent is doing
that should cause this skill to activate.

## [Domain-Specific Sections]

Reference material, patterns, architecture, API surfaces, etc.
Organize by topic. Use tables for compact reference data.

## Common Pitfalls

Bullet list of gotchas and mistakes to avoid.
```

### Code Snippets

Use `<code-snippet>` tags instead of fenced code blocks when the snippet is a reusable pattern or reference example:

```html
<code-snippet name="Descriptive Name" lang="php">
// code here
</code-snippet>
```

| Attribute | Required | Notes |
|-----------|----------|-------|
| `name` | Yes | Human-readable label for the snippet |
| `lang` | Yes | Language for syntax highlighting (`php`, `typescript`, `vue`, etc.) |

Fenced code blocks (` ``` `) are fine for short inline examples, shell commands, or directory trees.

## Registration

After creating the `SKILL.md` files, register the skill in two places:

### 1. `boost.json`

Add the skill name to the `skills` array:

```json
{
    "skills": [
        "existing-skill",
        "my-skill-name"
    ]
}
```

### 2. `CLAUDE.md`

Add an activation entry under the **Skills Activation** section:

```markdown
- `my-skill-name` — Description of when to activate this skill.
```

This entry mirrors the `description` from the frontmatter but is written as a single line in CLAUDE.md.

## Activation

Skills are activated in two ways:

1. **Automatic** — The agent reads the skill listing and activates it based on the `description` matching the current task.
2. **Manual** — The user or agent invokes it via the `Skill` tool: `skill: "my-skill-name"`.

## Writing Guidelines

- **Be specific, not generic.** Include actual class names, method signatures, config keys, route paths, and enum values from the codebase. The skill replaces reading dozens of files.
- **Use tables for dense reference data.** Models, enums, routes, and method lists are easier to scan in tables than prose.
- **Include code patterns for creation.** Show how to create a new instance of whatever the skill covers (new service, new controller, new provider driver) — this is what agents need most.
- **Document the contracts/interfaces.** Agents need to know what methods to implement.
- **List common pitfalls.** Constraints that aren't obvious from the code (protected roles, auto-generated slugs, amounts in cents, etc.).
- **Keep it current.** Update the skill when the architecture changes. Stale information is worse than no information.
- **Don't duplicate framework docs.** Reference `search-docs` for general Laravel/Vue/Inertia documentation. Skills should cover project-specific architecture and patterns.

## Existing Skills

| Skill | Purpose |
|-------|---------|
| `wayfinder-development` | Wayfinder TypeScript route generation — imports, methods, Inertia integration |
| `inertia-vue-development` | Inertia.js v2 Vue patterns — pages, forms, navigation, deferred props, prefetching |
| `tailwindcss-development` | Tailwind CSS v4 utilities — styling, responsive design, dark mode, colors |
| `developing-with-fortify` | Laravel Fortify auth — login, registration, 2FA, password reset, email verification |
| `payment-gateway-development` | nejcc/payment-gateway — Payment facade, Billable trait, drivers, transactions, subscriptions, plans, invoices |
| `subscribe-development` | nejcc/subscribe — Subscribe facade, subscribers, lists, email providers, double opt-in |
| `global-settings-development` | laravelplus/global-settings — GlobalSettings facade, Setting model, SettingRole enum |
| `laravelplus-starter-kit` | Core application — services, repositories, controllers, middleware, navigation, shared props |

## Quick Start

```bash
# 1. Create directories
mkdir -p .claude/skills/my-skill-name .github/skills/my-skill-name

# 2. Create SKILL.md (write in .claude, copy to .github)
# ... write the file ...
cp .claude/skills/my-skill-name/SKILL.md .github/skills/my-skill-name/SKILL.md

# 3. Register in boost.json — add to "skills" array

# 4. Register in CLAUDE.md — add to "Skills Activation" section
```
