---
name: hijauku-project
description: Project context and implementation rules for auditing requirements, architecture, backlog, and building Hijauku Laravel features.
---

# Hijauku project skill

Before editing, read AGENTS.md, docs/project-context.md, docs/source-audit.md, and the relevant architecture, requirements, or database page.
Read docs/jira-workflow.md when planning a story, estimating work, logging time, or linking commits to Jira.

## Verified project facts

- Five members: Yoga, Bitari, Arif, Wira, Rofii. Dinda is not on the roster.
- The course handout says groups have 6-7 members; lecturer confirmation for five members is open.
- Jira has 5 active epics and 45 stories HJK-001 to HJK-045 for M01 to M15. Each person owns 3 modules × 3 stories = 9 stories.
- HAP-6 was permanently deleted from Jira at the PM's request. M16 to M18 are outside the active scope. Do not add screens or workflows unless the PM opens a new scope with acceptance criteria.
- HAP-61's acceptance criteria are aligned to the current roster/scope. Existing Jira keys do not get renumbered.
- Original estimates are plans. Actual Jira work logs are entered only by the person who performed the work; never invent or alter actual hours to fit an estimate.
- Use one personal branch per member: `yoga`, `bitari`, `arif`, `wira`, or `rofii`. The branch name is the exception to the HAP-key rule; every commit and PR must include its uppercase HAP issue key. Verify Jira's Development panel after push.
- Jangan menghapus atau mengganti nama branch anggota lain selama PR masih terbuka atau commit belum ada di `main`. Pemilik branch memindahkan commit unik ke branch personal dan membuka PR pengganti dulu. Hapus branch remote hanya setelah memastikan tidak ada PR terbuka dan commit unik; pertahankan branch personal setelah merge.
- Sprint 0 is preparation. Sprint 1 follows weeks 6-10, Sprint 2 weeks 11-15. Week 6 is backlog/refinement without coding; implementation starts week 7. Keep the three stories for one module in the same sprint unless the PM records an approved dependency exception.
- CI is configured to run Laravel Dusk against a seeded, isolated SQLite file. The manager program-creation test needs a passing CI result before reporting it as verified.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Role panels: manager /app, field_staff /petugas, citizen /warga.
- Gate and base Policies are in place. Resource query ownership and feature workflows still need implementation.
- MySQL is the development baseline. Preserve the owner's .env and local DB; never run migrations against that DB.
- Stitch's latest guidance excludes M16-M18. Current starter home copy/flow is aligned but visual review of all frames remains open.
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
- Follow CONTRIBUTING.md; never merge a PR. The PM merges. Member PRs need PM approval; a PM-authored PR skips self-approval but still requires green CI.

## Writing for the team

- Write naturally in Indonesian, with short, specific instructions a teammate can follow.
- Avoid canned assistant phrasing, promotional language, and repetition. State what was verified and what still needs work.
- Use an ordinary hyphen `-` in project-authored text. Do not use em dash or en dash.
