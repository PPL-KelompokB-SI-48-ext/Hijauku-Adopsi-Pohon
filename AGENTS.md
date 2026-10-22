# Project instructions for AI agents

Before changing application code, read `docs/project-context.md` and the relevant documents in `docs/`. Use `docs/source-audit.md` for facts about the course rules, roster, Jira, and Stitch.

## Project-specific facts

- The stack is Laravel 13, Filament 5, Blade, Vite, and Eloquent.
- The project owner has confirmed a five-person roster: Yoga, Bitari, Arif, Wira, and Rofii. Dinda is not a team member. The course handout says groups have 6–7 people, so the five-person team still needs lecturer approval for course compliance.
- Jira was inspected in the authenticated session. It currently has 6 epics and 45 stories for M01–M15, assigned as 3 modules × 3 stories per person (9 stories each). HAP-6 for M16–M18 has no stories. HAP-61 still says 6 members and 54 stories; treat that acceptance criterion as stale until the project owner updates it.
- Stitch was inspected. Its 25-frame project includes public, citizen, manager, and field-staff screens. Its latest handoff excludes the article, gallery, and citizen-proposal modules (architecture M16–M18). The public home template now follows the latest frame's copy, CTA labels, and three-step flow; the supplied brand image and full frame-by-frame visual review are still outstanding.
- The course asks every member to deliver at least one medium/high-complexity feature per formal sprint, with at least three PBI per feature. This derives to at least 3 PBI/person/sprint (6 per person across two sprints for one minimum feature each), not 3 PBI/person for the whole project.
- The owner reports that the local MySQL database is already configured and working. Preserve the existing `.env`; the original architecture proposes PostgreSQL 18, so the shared team engine is still undecided.
- Laravel Dusk 8.7 is installed as a dev dependency; CI runs a browser smoke test. Active users in all three role enum cases may enter the base panel, but role-specific Resource/Policy authorization is not implemented yet.
- Jira currently has three future-state sprints. As of the 2026-10-22 audit, Sprint 0's date had ended and Sprint 1's start date had passed, but both still showed `future`; Sprint 2's goal also says weeks 11–14 while the course plan says weeks 11–15.

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
