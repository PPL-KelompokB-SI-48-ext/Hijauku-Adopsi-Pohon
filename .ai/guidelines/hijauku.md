# Hijauku project guidance

Hijauku is a student team project for recording tree-planting programs, adoption requests, field activities, care records, and growth observations. Before coding, read `docs/project-context.md` and the relevant requirement or architecture document.

- Do not invent facts from Jira HAP or the Stitch project. Their contents were unavailable during the last audit; ask the task owner to provide or open the issue/design when needed.
- Keep the known roster conflict visible: the proposal/repository list five members, class rules say 6–7, and the architecture plus SETUP-001 assumes six. The 18 modules / 54 PBI target is therefore provisional.
- The course rule is at least one medium/high-complexity feature per person per formal sprint and at least three PBI per feature. Do not restate it as three PBI per person.
- MySQL in `.env.example` is a local convenience. The architecture proposes PostgreSQL 18 and the team must choose one engine before implementation is standardized.
- Use policies for role and ownership checks, transactional services for adoption/quota/capacity rules, and private storage for evidence photos.
- Update `docs/` when a decision changes. Preserve the user-owned `desain.md`.
- Never commit `.env`, real credentials, or real personal data.
