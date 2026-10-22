# Instructions for GitHub Copilot

Read AGENTS.md and docs/project-context.md. For the task's module, also read docs/architecture.md, docs/requirements.md, and docs/database.md.

Hijauku uses Laravel 13, Filament 5, Blade, Vite, Eloquent, and MySQL 8+. The confirmed team has five members: Yoga, Bitari, Arif, Wira, and Rofii. Dinda is not on the team. Jira has five active epics and 45 stories HJK-001 to HJK-045 across M01 to M15. HAP-6 was permanently deleted; M16 to M18 are outside the active scope. Do not implement that scope without a new PM decision.

Panels are separated by role: manager /app, field_staff /petugas, citizen /warga. Keep authorization server-side using Policy plus ownership-scoped queries. Tests must prove both allowed and denied cases for new access rules. Use transactions for quota, plot capacity, and adoption allocation. Keep evidence uploads private.

Do not read or overwrite .env, connect to the owner's database, or commit secrets/private data. Preserve desain.md. Follow CONTRIBUTING.md: work on a Jira-key branch, ask Yoga (@mygads) for review, author resolves conflicts, and PM merges. Write project documentation in clear, natural Indonesian without generic AI-sounding wording or repeated summaries. Use `-` instead of em dash or en dash, and describe scaffolding honestly.
