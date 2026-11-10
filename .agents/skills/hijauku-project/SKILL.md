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
- Story Done requires acceptance, relevant tests, CI, review, and PM acceptance under its issue's DoD. At sprint end, each member opens one PR for that sprint's completed stories. The PM sets merge order and merges after CI and review pass. Sprint integration tasks are Done only after the assigned PRs are merged and final CI passes. Reopen a story if integration review finds a defect.
- Jangan menghapus atau mengganti nama branch anggota lain selama PR masih terbuka atau commit belum ada di `main`. Pemilik branch memindahkan commit unik ke branch personal dan membuka PR pengganti dulu. Hapus branch remote hanya setelah memastikan tidak ada PR terbuka dan commit unik; pertahankan branch personal setelah merge.
- Sprint 0 is preparation. Sprint 1 follows weeks 6-10, Sprint 2 weeks 11-15. Week 6 is backlog/refinement without coding; implementation starts week 7. Each module's three stories stay together. The PM-approved replan moves one whole module per member from Sprint 2 to Sprint 1; M05, M11, and M14 use agreed schema contracts/fixtures until their remaining cross-module dependencies are integrated.
- Jira was checked on 11 November 2026 after PR #19: 45 stories have 7 Done (HAP-7/8/9, HAP-25, HAP-43/44/45), 10 In Progress (HAP-10/11/12, HAP-16/17/18, HAP-26/27, HAP-34/35), and 28 To Do. HAP-10/11/12 are in progress on Yoga's personal branch. Sprint 1 contains 30 stories, five member PR preparation tasks HAP-62 through HAP-66, and PM integration/closeout tasks HAP-71/HAP-72. Redundant HAP-70 was deleted; readiness checks are part of HAP-71/HAP-72. Sprint 2 contains 15 stories, integration HAP-67/HAP-68, UAT/deploy HAP-69, and final findings/report/presentation HAP-73. Recheck Jira before a later status decision.
- Sprint 0 - Persiapan (id 6) is closed. The duplicate future Sprint 0 (id 9) was removed; HAP-61 remains in the closed sprint. Sprint 1 keeps weeks 6-10, 19 October-22 November. Fifteen moved stories are due one per member on 15 November and two per member on 22 November. Sprint 2 keeps weeks 11-15, 23 November-27 December; its 15 stories are scheduled as two per person in week 11 and one in week 12. Week 13 is PR integration; week 14 is UAT/deploy/docs; week 15 is findings, final report, and presentation prep.
- Jira sprint goals and issue links reflect the sequence: HAP-62/63/64 block HAP-71; HAP-65/66 and HAP-71 block HAP-72; HAP-72 blocks HAP-67; HAP-67 blocks HAP-68, which blocks HAP-69, which blocks HAP-73. Readiness is included in integration, with no standalone preflight issue. Do not mark an integration/closeout task done before its prerequisites.
- `main` at audit time on 11 November is `bee4a0d2dc3033553403ba88f3e086b5290050a1` after PR #19. PR #12, #14, and #15 are closed without merge; GitHub has 0 open PRs. Their branches retain unique commits and are behind `main` by 16, 16, and 22 commits. Owners preserve those commits, sync `main`, resolve conflicts, address applicable review feedback, rerun CI, and open replacement PRs. Prior green CI runs are stale.
- CI is configured to run Laravel Dusk against a seeded, isolated SQLite file. PR #17 passed Dusk for M01 HJK-002/HJK-003; PR #16 passed current CI for HAP-44/45. Recheck the newest run for the exact head before reporting a PR as verified.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Role panels: manager /app, field_staff /petugas, citizen /warga.
- Panel gates and base Policies are in place. M01 HJK-001-HJK-003, M07 HJK-019, and M13 HJK-037-HJK-039 are in `main`; other resources/workflows remain in their Jira stories. Every new resource still needs query ownership and policy tests.
- MySQL is the development baseline. Preserve the owner's .env and local DB; never run migrations against that DB.
- Stitch's guidance excludes M16-M18. The 25 mockup frames were inspected for proposal/presentation work; acceptance of each implemented screen remains part of its PBI.
- For proposal, presentation, or video work, read docs/proposal-audit.md and docs/video-script.md. The 25 Stitch frames were inspected as mockups with sample data; this is not acceptance of implemented UI. Frame numbers 16-25 are screen numbers, not new business modules.
- `care_plans.assignment_id` is optional. HAP-44 has added its priority column and index through migration in `main`. Audit changes through `activity_logs.actor_id`; do not invent `updated_by` columns.
- M01 uses Filament/Livewire with a shared theme and reusable panel configuration aligned to the Stitch shell. For a screen with a mapped frame, target 1:1 visual alignment for the elements the frame shows. Use reusable Filament form/table/action patterns. A form/detail screen without its own frame follows the shared Filament pattern; record the limit and do not claim a visual comparison that is not available. Do not add behavior without Jira scope or claim all business screens are already 1:1.
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
