# Hijauku project guidance

Hijauku is a student team project for recording tree-planting programs, adoption requests, field activities, care records, and growth observations. Before coding, read `docs/project-context.md` and the relevant requirement or architecture document.

- The owner confirmed five members: Yoga, Bitari, Arif, Wira, and Rofii; Dinda is not on the team. The course handout says 6–7, so lecturer approval is still needed for course compliance.
- Jira currently has six epics and 45 stories for M01–M15, assigned as three modules with three stories each per person. HAP-6 has no stories; HAP-61 still says six members and 54 stories. Do not silently change Jira; record this mismatch and use `docs/source-audit.md`.
- Stitch is available and has been inspected. Its latest screen guidance excludes the article, gallery, and citizen-proposal modules. The public home template now follows the latest frame's copy, CTA labels, and three-step flow; the brand image and full visual review are still outstanding.
- Dusk 8.7 is installed and CI runs one public-home smoke test. Active users in all three role enum cases may enter the base panel, but per-resource Policy/RBAC enforcement remains to be implemented before modules are exposed.
- The course rule is at least one medium/high-complexity feature per person per formal sprint and at least three PBI per feature. This derives to at least 3 PBI/person/sprint (6 across two sprints for one minimum feature each), not 3 PBI/person for the whole project.
- The owner says the local MySQL database is already configured and working. Preserve `.env`; the architecture proposes PostgreSQL 18, so the team must still choose one shared engine.
- Use policies for role and ownership checks, transactional services for adoption/quota/capacity rules, and private storage for evidence photos.
- Update `docs/` when a decision changes. Preserve the user-owned `desain.md`.
- Never commit `.env`, real credentials, or real personal data.
