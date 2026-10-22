# Hijauku project context

Read `AGENTS.md` and `docs/project-context.md` before editing. Use `docs/source-audit.md` for known facts and unresolved decisions; Jira and Stitch have not been accessible to the project owner during the last audit.

This repository is a Laravel 13 + Filament 5 starter. Preserve the existing domain boundaries and Indonesian user-facing text. The course rule derives to at least 3 PBI/person/sprint (6 across two sprints for one minimum feature each), not 3 PBI/person for the whole project. Check authorization in Policies and ownership rules, use transactions for quota/allocation operations, and keep evidence files private. Update the relevant `docs/` file when requirements, architecture, or decisions change. Do not commit `.env`, private data, or credentials. Do not treat the provisional MySQL configuration as the team’s final database choice.
