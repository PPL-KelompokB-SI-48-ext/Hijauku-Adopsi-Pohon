# Hijauku project context

Read AGENTS.md, docs/project-context.md, and docs/source-audit.md before work. This is a five-person project: Yoga, Bitari, Arif, Wira, Rofii. Dinda is not a member. The class group-size rule says 6–7, so lecturer confirmation remains open.

Current Jira has five active epics and 45 stories HJK-001–045 for M01–M15: 3 modules × 3 stories per person. HAP-6/M16–M18 is archived/deferred. HAP-61 is updated for the five-member roster and 45-story scope. Do not renumber existing issue keys.

The stack is Laravel 13, Filament 5, Blade, Vite, Eloquent, and MySQL 8+ for local development. The app has role-specific Filament panels: manager /app, field_staff /petugas, citizen /warga. Gate and Policy scaffolding is in place; future Resources must scope list queries, protect record ownership, use safe actions for review/accept/verify, and have tests.

Use database transactions for quota, plot capacity, and adoption allocation. Store evidence on private disk. Preserve the owner's .env and local database. Never commit credentials or personal data. Follow CONTRIBUTING.md: author resolves conflicts, members request Yoga's review, and PM alone merges. Describe scaffolding honestly and update relevant docs.
