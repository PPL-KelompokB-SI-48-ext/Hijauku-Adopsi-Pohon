# Hijauku project guidance

Hijauku is a Laravel 13 + Filament 5 application for tree programs, inventories, field activities, adoptions, care, and growth monitoring.

- Roster is five: Yoga, Bitari, Arif, Wira, Rofii. Dinda is not a member. The course handout asks for 6-7; lecturer confirmation remains open.
- Jira scope is five active epics and 45 stories HJK-001 to HJK-045 for M01 to M15, with 9 stories per member. HAP-6 was permanently deleted. M16 to M18 are outside the active scope; existing table/model scaffolds are not active features.
- HAP-61 acceptance criteria now match the five-person/45-story scope. Existing Jira keys are stable.
- MySQL 8+ is the team development baseline; SQLite supports fast automated tests and MySQL is checked in CI.
- Panels are manager /app, field_staff /petugas, and citizen /warga. Gate checks panel access; Policies check actions and ownership; Resources must also restrict list queries.
- Review, accept, and verify operations need dedicated actions and transactions. A Policy permission does not validate payload fields or replace a service.
- The owner has a working local database and .env. Preserve both; do not read .env or run migrations against it.
- Use transactions for plot capacity, activity quota, and adoption allocation. Keep evidence files private.
- Preserve desain.md. Never commit tokens, secrets, real personal data, or photos.
- Follow CONTRIBUTING.md: branch per Jira story, request @mygads review, author resolves conflicts, PM merges.

Read AGENTS.md, docs/project-context.md, docs/source-audit.md, and the relevant architecture/requirements/database pages before editing.

Write in clear, natural Indonesian. Prefer direct, useful sentences over generic assistant phrasing or repeated summaries. Use the ordinary hyphen `-`, not em dash or en dash. Do not claim scaffolded work is complete.
