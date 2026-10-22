# Project instructions for AI agents

Before changing application code, read `docs/project-context.md` and the relevant documents in `docs/`. Use `docs/source-audit.md` for facts about the course rules, roster, Jira, and Stitch.

## Project-specific facts

- The stack is Laravel 13, Filament 5, Blade, Vite, and Eloquent.
- Jira HAP and the Stitch project required sign-in during the last audit. Do not invent or claim their contents were reviewed.
- Five members are listed on the proposal cover and have GitHub access; the course rule says groups have 6–7 people; architecture and SETUP-001 assume six. Treat 18 modules / 54 PBI as provisional until the roster is confirmed.
- The course asks every member to deliver at least one medium/high-complexity feature per formal sprint, with at least three PBI per feature. It does not state three PBI per person.
- The MySQL setting in `.env.example` is a local convenience. The original architecture proposes PostgreSQL 18. The team has not confirmed a shared engine.

## Implementation rules

- Keep the application as one Laravel monolith with clear domain boundaries.
- Enforce roles and record ownership in Policies and server-side queries, not only by hiding UI controls.
- Protect adoption allocations, activity quotas, and plot capacity with database transactions and constraints.
- Keep evidence photos private unless a specific publication workflow grants access.
- Preserve the user-owned `desain.md`. Never commit `.env`, credentials, or real personal data.
- Update the relevant documents in `docs/` when requirements or decisions change. Say which work is scaffolding and which behavior is implemented.
- Use the installed Laravel Boost and `.agents/skills/` guidance for framework-specific work. Do not install more packages without a clear project need.

## Running the application

Follow the setup instructions in `README.md`. Check `.env` before running migrations; do not redirect migrations to a database containing user data unless the project owner has confirmed the target.
