# Project instructions for AI agents

Read docs/project-context.md and the relevant docs before changing application code. Use docs/source-audit.md for verified course, roster, Jira, GitHub, and Stitch facts.

## Project facts

- Stack: Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Five members: Yoga, Bitari, Arif, Wira, and Rofii. Dinda is not a member.
- Class handout says teams have 6-7 members; lecturer confirmation for this five-person team is still open.
- Active Jira: 5 epics and 45 stories HJK-001 to HJK-045 across M01 to M15. Each member has 3 modules × 3 stories = 9 stories.
- HAP-6 was permanently deleted from Jira at the PM's request. M16-M18 are outside the active scope. Three model/table scaffolds remain in the initial migration; do not build their screens or workflows unless the PM opens a new scope.
- Jira rechecked on 10 November 2026: HAP-1, HAP-61, and HAP-7 are Done. HAP-8 and HAP-9 are In Progress under Yoga; both have a recorded replan to 9-15 November after their earlier targets passed. HAP-8/HAP-9 have no work logs. Keep them In Progress until their PR, CI, and PM acceptance are complete. HAP-61 has 18h original and 0h remaining; HAP-7 has 4h original and 0h remaining. Do not invent actual hours. HAP-2/HAP-3 and other contributors' issue statuses still need a live check before decisions.
- Earlier GitHub audit found PR #1, #2, #4, #7, and #9 merged; PR #5 closed without merge; Rofii's replacement PR #6 was on branch `rofii`. Recheck live PR state before acting. Do not disturb teammate branches.
- Existing HAP issue keys and HJK IDs are stable; do not renumber.
- Read docs/jira-workflow.md before editing backlog-related docs or preparing a feature. Original estimate is a plan; actual work log belongs to the contributor who did the work. Never invent, backfill, or normalize actual hours to match an estimate.
- Team workflow uses one long-lived personal branch per member: `yoga`, `bitari`, `arif`, `wira`, and `rofii`. The branch name is the exception to the HAP-key rule; commits and PRs must include uppercase HAP keys. Verify Jira's Development panel after push.
- Do not delete or rename a teammate's branch with an open PR or commits missing from `main`. The branch owner migrates legacy feature branches into their personal branch and opens a replacement PR before closing the old one. Delete remote branches only after confirming no open PR and no unique commits; keep personal branches after merge.
- Sprint 0 is closed, Sprint 1 is active, and Sprint 2 is future. The plan used week 6 for backlog/refinement and scheduled coding from week 7. GitHub shows HJK-001 was merged through PR #2 on 22 October; Dusk and final PM acceptance came with PR #4 on 25 October. HAP-16 and HAP-25 were also In Progress by the end of week 6, before their planned week 7 start. Check their Jira start dates against the actual start of work. Sprint 1 remains weeks 6-10 and Sprint 2 weeks 11-15.
- CI runs Laravel Dusk against a seeded, isolated SQLite file. App and Dusk checks passed on PR #4 head `7a5efae` in run `40181326721`. Documentation PR #7 passed CI in run `40190337974` on head `c72fa23`; diagram/documentation PR #9 passed in run `40194679835` on head `7de32cf`. HJK-002/HJK-003 have local Feature and Dusk coverage; verify the new PR's CI before calling those checks green in GitHub.
- Role panels: manager /app, field_staff /petugas, citizen /warga. Panel gates and base policies exist; every new resource needs query ownership, policy tests, and safe actions.
- MySQL is the development baseline. The owner's local .env already works: never read or overwrite it and never run migrations against that database.
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
- Follow CONTRIBUTING.md. Members work on their personal branch and open PRs to main; PR authors resolve conflicts; only PM merges. Member PRs need PM approval; a PM-authored PR may skip self-approval, but CI must pass.
- Keep Jira acceptance, sprint assignment, estimate and work log consistent with actual work. The issue is not Done until acceptance, CI, review and PM acceptance are complete.
- Use installed Laravel Boost and the project skill under .agents/skills/hijauku-project/.
- Check .env before local migrations; never redirect to a database containing user data without explicit confirmation.
