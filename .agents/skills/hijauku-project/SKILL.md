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
- Use one personal branch per member. Current names on GitHub: Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, Rofii `Rofii`. The PM accepts the spelling/case variations; do not rename an active branch. Every commit and PR must include its uppercase HAP issue key. Verify Jira's Development panel after push.
- After acceptance criteria and relevant tests pass on the pushed personal branch, the contributor may mark that story Done and check Jira's Development panel. At sprint end, each member opens one PR for that sprint's completed stories. The PM sets merge order and merges after CI and review pass. Reopen a story if integration review finds a defect.
- Jangan menghapus atau mengganti nama branch anggota lain selama PR masih terbuka atau commit belum ada di `main`. Pemilik branch memindahkan commit unik ke branch personal dan membuka PR pengganti dulu. Hapus branch remote hanya setelah memastikan tidak ada PR terbuka dan commit unik; pertahankan branch personal setelah merge.
- Sprint 0 is preparation. Sprint 1 follows weeks 6-10, Sprint 2 weeks 11-15. Week 6 is backlog/refinement without coding; implementation starts week 7. Keep the three stories for one module in the same sprint unless the PM records an approved dependency exception.
- Jira was checked on 10 November 2026: 45 stories have 7 Done (HAP-7/8/9, HAP-25, HAP-43/44/45), 7 In Progress (HAP-16/17/18, HAP-26/27, HAP-34/35), and 31 To Do. The six M04/M07/M10 stories remain In Progress pending owner confirmation of each story's acceptance and tests on the current pushed branch. A PR that waits for end-of-sprint integration is not by itself a reason to block Done. Recheck Jira before a later status decision.
- `main` at audit time is `8930f589`. PR #16 for HAP-44/45, PR #17 for HAP-8/9, and PR #18 for documentation are merged. PR #12 is 7 commits behind/5 ahead, PR #14 is 7 behind/6 ahead, and PR #15 is 13 behind/2 ahead. Their last successful CI runs predate latest `main`; owners must sync and rerun CI before the end-of-sprint integration review.
- CI is configured to run Laravel Dusk against a seeded, isolated SQLite file. PR #17 passed Dusk for M01 HJK-002/HJK-003; PR #16 passed current CI for HAP-44/45. Recheck the newest run for the exact head before reporting a PR as verified.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Role panels: manager /app, field_staff /petugas, citizen /warga.
- Panel gates and base Policies are in place. M01 HJK-001-HJK-003, M07 HJK-019, and M13 HJK-037-HJK-039 are in `main`; other resources/workflows remain in their Jira stories. Every new resource still needs query ownership and policy tests.
- MySQL is the development baseline. Preserve the owner's .env and local DB; never run migrations against that DB.
- Stitch's guidance excludes M16-M18. The 25 mockup frames were inspected for proposal/presentation work; acceptance of each implemented screen remains part of its PBI.
- For proposal, presentation, or video work, read docs/proposal-audit.md and docs/video-script.md. The 25 Stitch frames were inspected as mockups with sample data; this is not acceptance of implemented UI. Frame numbers 16-25 are screen numbers, not new business modules.
- `care_plans.assignment_id` is optional. HAP-44 has added its priority column and index through migration in `main`. Audit changes through `activity_logs.actor_id`; do not invent `updated_by` columns.
- M01 uses Filament/Livewire with a shared theme aligned to the Stitch shell. Page content is not pixel-identical because some mockup controls and modules are outside current HJK-001-HJK-003 acceptance. Do not add behavior without Jira scope or claim a full 1:1 match.
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
- Follow CONTRIBUTING.md. Only the PM merges. Member PRs need PM approval; a PM-authored PR skips self-approval but still requires green CI. Do not merge unless the user explicitly asks the acting PM to do so.

## Writing for the team

- Write naturally in Indonesian, with short, specific instructions a teammate can follow.
- Avoid canned assistant phrasing, promotional language, and repetition. State what was verified and what still needs work.
- Use an ordinary hyphen `-` in project-authored text. Do not use em dash or en dash.
