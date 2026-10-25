# Project instructions for AI agents

Read docs/project-context.md and the relevant docs before changing application code. Use docs/source-audit.md for verified course, roster, Jira, GitHub, and Stitch facts.

## Project facts

- Stack: Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Five members: Yoga, Bitari, Arif, Wira, and Rofii. Dinda is not a member.
- Class handout says teams have 6-7 members; lecturer confirmation for this five-person team is still open.
- Active Jira: 5 epics and 45 stories HJK-001 to HJK-045 across M01 to M15. Each member has 3 modules × 3 stories = 9 stories.
- HAP-6 was permanently deleted from Jira at the PM's request. M16-M18 are outside the active scope. Three model/table scaffolds remain in the initial migration; do not build their screens or workflows unless the PM opens a new scope.
- The latest Jira read on 25 October 2026 shows HAP-61 Done, HAP-1 To Do, and HAP-7 In Progress. PR #1/#2 are merged; the requested revert remains draft PR #5. Recheck live status and acceptance against `main` before acting. A Jira Done status does not authorize merging pending PRs or inventing work logs. HAP-61 still has an old 4h remaining estimate and no work logs; its contributor/PM must reconcile that estimate against real remaining work.
- Existing HAP issue keys and HJK IDs are stable; do not renumber.
- Read docs/jira-workflow.md before editing backlog-related docs or preparing a feature. Original estimate is a plan; actual work log belongs to the contributor who did the work. Never invent, backfill, or normalize actual hours to match an estimate.
- Team workflow uses one long-lived personal branch per member: `yoga`, `bitari`, `arif`, `wira`, and `rofii`. The branch name is the exception to the HAP-key rule; commits and PRs must include uppercase HAP keys. Verify Jira's Development panel after push.
- Do not delete or rename a teammate's branch with an open PR or commits missing from `main`. The branch owner migrates legacy feature branches into their personal branch and opens a replacement PR before closing the old one. Delete remote branches only after confirming no open PR and no unique commits; keep personal branches after merge.
- Sprint 0 is closed, Sprint 1 is active, and Sprint 2 is future. Week 6 was for backlog/refinement without coding; Sprint 1 story work starts in week 7. Keep the formal course calendar: Sprint 1 weeks 6-10 and Sprint 2 weeks 11-15.
- CI is configured to run Laravel Dusk against a seeded, isolated SQLite file. The manager program-creation test is present, but do not call it passing until the latest CI run confirms it.
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
