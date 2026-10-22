# Instructions for GitHub Copilot

Read AGENTS.md and docs/project-context.md. For the task's module, also read docs/architecture.md, docs/requirements.md, and docs/database.md.

Hijauku uses Laravel 13, Filament 5, Blade, Vite, Eloquent, and MySQL 8+. The confirmed team has five members: Yoga, Bitari, Arif, Wira, Rofii. Dinda is not on the team. Jira has five active epics and 45 stories HJK-001–045 across M01–M15. HAP-6/M16–M18 is archived and deferred; do not implement that scope without PM approval.

Panels are separated by role: manager /app, field_staff /petugas, citizen /warga. Keep authorization server-side using Policy plus ownership-scoped queries. Tests must prove both allowed and denied cases for new access rules. Use transactions for quota, plot capacity, and adoption allocation. Keep evidence uploads private.

Do not read or overwrite .env, connect to the owner's database, or commit secrets/private data. Preserve desain.md. Follow CONTRIBUTING.md: work on a Jira-key branch, ask Yoga (@mygads) for review, author resolves conflicts, and PM merges.
