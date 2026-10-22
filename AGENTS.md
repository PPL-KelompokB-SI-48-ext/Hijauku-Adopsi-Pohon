# Project instructions for AI agents

Read docs/project-context.md and the relevant docs before changing application code. Use docs/source-audit.md for verified course, roster, Jira, GitHub, and Stitch facts.

## Project facts

- Stack: Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+.
- Five members: Yoga, Bitari, Arif, Wira, and Rofii. Dinda is not a member.
- Class handout says teams have 6-7 members; lecturer confirmation for this five-person team is still open.
- Active Jira: 5 epics and 45 stories HJK-001 to HJK-045 across M01 to M15. Each member has 3 modules × 3 stories = 9 stories.
- HAP-6 was permanently deleted from Jira at the PM's request. M16-M18 are outside the active scope. Three model/table scaffolds remain in the initial migration; do not build their screens or workflows unless the PM opens a new scope.
- HAP-61 acceptance criteria are aligned to five members and 45 stories. HAP-1 and HAP-61 remain To Do until PM accepts the PR.
- Existing HAP issue keys and HJK IDs are stable; do not renumber.
- Sprint 0/1/2 exist, but all still show future although Sprint 0 end and Sprint 1 start dates have passed. PM decides state changes.
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
- Follow CONTRIBUTING.md. Members open PRs and assign Yoga (@mygads); PR authors resolve conflicts; only PM merges.
- Use installed Laravel Boost and the project skill under .agents/skills/hijauku-project/.
- Check .env before local migrations; never redirect to a database containing user data without explicit confirmation.
