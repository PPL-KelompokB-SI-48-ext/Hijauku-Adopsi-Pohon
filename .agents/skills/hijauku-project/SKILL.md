---
name: hijauku-project
description: "Project-specific context and implementation guidance for the Hijauku tree adoption application. Use when auditing requirements, architecture, backlog, or building Laravel features in this repository."
---

# Hijauku project skill

Before changing code, read `docs/project-context.md` and the relevant `docs/architecture.md`, `docs/requirements.md`, or `docs/database.md`. Read `docs/source-audit.md` when a task touches Jira, team size, sprint rules, or Stitch.

## Project decisions and open points

- The stack is Laravel 13, Filament 5, Blade, Vite, and Eloquent.
- `manager`, `field_staff`, and `citizen` are the starting roles. Policies and resource-specific permissions still need implementation.
- Five members are on the proposal cover and GitHub; the class rules specify 6–7, and the architecture and SETUP-001 assume six. Treat the 18-module/54-PBI target as provisional until the roster is approved.
- Course material says each person should deliver at least one medium/high-complexity feature in each formal sprint, and each feature has at least three PBI. This derives to at least 3 PBI/person/sprint (6 across two sprints for one minimum feature each), not 3 PBI/person for the whole project.
- Jira HAP and Stitch content were not available to inspect at the last audit. Never claim they were reviewed or infer their contents.
- MySQL in `.env.example` is provisional. The original architecture proposes PostgreSQL 18; use the team's confirmed engine when they decide.
- `desain.md` belongs to the user. Preserve it.

## Implementation guidance

- Keep the application as one Laravel monolith with clear domain boundaries.
- Enforce role, ownership, and publication checks in Policies and queries, not by hiding buttons alone.
- Protect adoption allocation, event quota, and plot capacity checks with database transactions and constraints.
- Keep reports and photos private until a workflow explicitly publishes them.
- Keep user-facing text in Indonesian. Do not put credentials or real personal data in source control.
- When a requirement or decision changes, update the relevant document under `docs/` and state the source of the change.
- Report what is implemented and what remains a scaffold; don't imply the 18 modules already have complete UI or workflows.

## Task prompt template

Use `docs/agent-onboarding.md` for the full handoff prompt and onboarding order. For implementation, ask the agent to work to a supplied Jira issue and its acceptance criteria, identify changed files, preserve access rules, and report verification limits.
