---
name: hijauku-project
description: Project context and implementation rules for auditing requirements, architecture, backlog, and building Hijauku Laravel features.
---

# Hijauku project skill

Before editing, read AGENTS.md, docs/project-context.md, docs/source-audit.md, and the relevant architecture, requirements, or database page.

## Verified project facts

- Five members: Yoga, Bitari, Arif, Wira, Rofii. Dinda is not on the roster.
- The course handout says groups have 6–7 members; lecturer confirmation for five members is open.
- Jira has 5 active epics and 45 stories HJK-001–045 for M01–M15. Each person owns 3 modules × 3 stories = 9 stories.
- HAP-6/M16–M18 is archived and deferred. Do not add its screens/workflows before scope and acceptance criteria are reopened.
- HAP-61's acceptance criteria are aligned to the current roster/scope. Existing Jira keys do not get renumbered.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Role panels: manager /app, field_staff /petugas, citizen /warga.
- Gate and base Policies are in place. Resource query ownership and feature workflows still need implementation.
- MySQL is the development baseline. Preserve the owner's .env and local DB; never run migrations against that DB.
- Stitch's latest guidance excludes M16–M18. Current starter home copy/flow is aligned but visual review of all frames remains open.
- Preserve user-owned desain.md and .playwright-mcp/.

## Implementation guidance

- Keep one modular Laravel monolith.
- Check sibling code and follow Laravel conventions.
- Enforce role and ownership in Policies and filtered queries, not only UI visibility.
- Dedicated actions/services must validate create payloads; Policy create receives no submitted attributes.
- Use transaction/locking for adoption allocation, activity quotas, and plot capacity.
- Prevent self-review for submitted care records and growth observations.
- Store evidence privately, validate file types/sizes, and authorize downloads.
- Write Indonesian user-facing text.
- Update docs when scope, requirements, or architecture decisions change.
- Report what works, what is scaffold, tests run, and remaining work.
- Follow CONTRIBUTING.md; never merge a PR. The PM merges.
