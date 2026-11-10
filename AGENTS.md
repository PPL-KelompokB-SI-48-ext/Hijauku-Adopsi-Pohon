# Project instructions for AI agents

Read docs/project-context.md and the relevant docs before changing application code. Use docs/source-audit.md for verified course, roster, Jira, GitHub, and Stitch facts.

## Project facts

- Stack: Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Five members: Yoga, Bitari, Arif, Wira, and Rofii. Dinda is not a member.
- Class handout says teams have 6-7 members; lecturer confirmation for this five-person team is still open.
- Active Jira: 5 epics and 45 stories HJK-001 to HJK-045 across M01 to M15. Each member has 3 modules × 3 stories = 9 stories.
- HAP-6 was permanently deleted from Jira at the PM's request. M16-M18 are outside the active scope. Three model/table scaffolds remain in the initial migration; do not build their screens or workflows unless the PM opens a new scope.
- Jira rechecked on 10 November 2026: HAP-1, HAP-7, HAP-8, HAP-9, HAP-25, and HAP-43 through HAP-45 are Done; HAP-2 through HAP-5 are In Progress; HAP-16 through HAP-18, HAP-26 through HAP-27, and HAP-34 through HAP-35 are In Progress. The remaining 31 stories are To Do. The 45-story count is 7 Done, 7 In Progress, and 31 To Do. The six M04/M07/M10 stories remain In Progress while their owners reconcile scope and verify acceptance on their pushed personal branch. A pending end-of-sprint PR is not by itself a reason to block a story from Done. HAP-25 and HAP-43 are merged/Done but still show 4h and 5h remaining with no work logs; their owners should reconcile remaining estimates. Do not invent actual hours.
- GitHub checked 10 November 2026: main is `8930f589`. PR #16 is merged and contains HAP-44/45. PR #17 and #18 are merged and contain HAP-8/9 plus documentation updates. Open feature PRs are #12 (Rofii, 7 commits behind/5 ahead), #14 (Arif, 7 behind/6 ahead), and #15 (Bitari, 13 behind/2 ahead). Their green CI runs predate the latest `main`; they are not ready for final sprint review/merge until their owners sync, reconcile PR scope, and get a fresh green run. Team cadence is to collect completed story changes on personal branches, then open/review/merge one PR per member at sprint end in the PM's agreed order. Do not disturb teammate branches.
- Existing HAP issue keys and HJK IDs are stable; do not renumber.
- Read docs/jira-workflow.md before editing backlog-related docs or preparing a feature. Original estimate is a plan; actual work log belongs to the contributor who did the work. Never invent, backfill, or normalize actual hours to match an estimate.
- Team workflow uses one long-lived personal branch per member. Current names are `yoga`, `bitaridamar`, `arif`, `ayuwira`, and `Rofii`. The PM accepts the existing spelling/case variations; do not rename active branches. Branch name is the exception to the HAP-key rule; commits and PRs must include uppercase HAP keys. Verify Jira's Development panel after push.
- Do not delete or rename a teammate's branch with an open PR or commits missing from `main`. The branch owner migrates legacy feature branches into their personal branch and opens a replacement PR before closing the old one. Delete remote branches only after confirming no open PR and no unique commits; keep personal branches after merge.
- Sprint 0 is closed, Sprint 1 is active, and Sprint 2 is future. Week 6 was for backlog/refinement; coding was planned from week 7. Keep official dates: Sprint 1 weeks 6-10, 19 October-22 November; Sprint 2 weeks 11-15, 23 November-27 December 2026. Due dates for overdue work remain historical until each owner agrees a realistic replan. Do not create actual time from planned dates.
- CI runs Laravel Dusk against an isolated seeded SQLite file. PR #17 passed in run `42774855543` and PR #16 passed in run `42826130678`. PR #12's latest run `41434932370`, PR #14's `41592514567`, and PR #15's `42823882248` passed on older heads before the latest `main`; do not describe them as current green checks. Members run story-level tests before moving Jira to Done and pushing. Recheck full CI on each sprint integration PR after syncing with `main`.
- Role panels: manager /app, field_staff /petugas, citizen /warga. Panel gates and base policies exist; every new resource needs query ownership, policy tests, and safe actions.
- MySQL is the development baseline. Never read or overwrite the owner's `.env` and never run migrations against that database. Tests must use phpunit's SQLite memory DB or another explicitly isolated SQLite file.
- M01 code currently uses Filament/Livewire and is not pixel-identical to the Stitch frame. Do not add edit/delete or a separate REST API unless Jira acceptance is changed; report the visual gap honestly and use the frame for in-scope alignment.
- Preserve the user-owned desain.md and .playwright-mcp/. Do not commit credentials or private personal data.

## Communication and documentation

- Write project guidance in clear, natural Indonesian. Keep sentences direct and useful for a teammate reading them between tasks.
- Avoid canned assistant language, promotional phrasing, unnecessary repetition, and robotic summaries. Be specific about what exists, what is scaffolding, and what remains.
- Use the ordinary hyphen `-` in authored project text. Do not use em dash or en dash.
- Keep names, Jira keys, commands, and file paths exact. Never invent a verification result or claim a story is complete without checking its acceptance criteria.

## Implementation rules

- Keep one Laravel monolith with clear domain boundaries.
- Enforce role and record ownership in Policies and query scopes, not by hiding controls.
- Use transactions and database constraints for adoption allocation, activity quota, and plot capacity.
- Keep evidence photos private unless a reviewed publication workflow allows access.
- State clearly what is implemented and what remains scaffolding.
- Update docs when requirements or decisions change.
- Follow CONTRIBUTING.md. Members work on their personal branch for the sprint. When a story's acceptance criteria and relevant tests pass on that branch, the contributor pushes the HAP-keyed commits, checks Jira Development, and updates that story to Done. At sprint end, each member opens one PR containing that sprint's completed work. PR authors resolve conflicts; only PM merges. Member PRs need PM approval; a PM-authored PR may skip self-approval, but CI must pass. If integration review or CI finds a defect, reopen the affected story and fix it on its owner's branch.
- Keep Jira acceptance, sprint assignment, estimate and work log consistent with actual work. A story can be Done before the sprint integration PR is merged, but only after its acceptance and relevant tests are verified on the pushed personal branch. Do not invent actual hours.
- Use installed Laravel Boost and the project skill under .agents/skills/hijauku-project/.
- Check .env before local migrations; never redirect to a database containing user data without explicit confirmation.
