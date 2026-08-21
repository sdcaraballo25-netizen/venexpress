# graphify
- **graphify** (`.claude/skills/graphify/SKILL.md`) - any input to knowledge graph. Trigger: `/graphify`
When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.


# Venexpress — Project Rules

## Project Context

Venexpress is a national logistics and shipping platform for Venezuela.

The system uses a decentralized network of physical partner locations ("Agencias Aliadas") to receive, process and deliver shipments.

Before making architectural changes, use Graphify to understand the existing relationships between controllers, models, services, routes, views and other components.

## Architecture Navigation

Graphify is the project's architecture index.

Before broad repository searches:

1. Consult `graphify-out/GRAPH_REPORT.md`.
2. Use Graphify to identify relevant nodes and relationships.
3. Read only the source files necessary for the requested task.
4. Verify conclusions against the actual source code.
5. Do not treat Graphify's graph as authoritative if it conflicts with current source code.

Useful commands:

- `graphify query "<question>"`
- `graphify explain "<node>"`
- `graphify path "<node A>" "<node B>"`
- `graphify affected "<node>"`
- `graphify god-nodes`

## Token Efficiency

Do not read the entire repository unless explicitly requested.

Do not recursively inspect unrelated directories.

Prefer Graphify to discover relevant files before using broad Glob/Grep searches.

Read only the files necessary to complete the task.

Avoid repeating repository exploration when the required architectural information is already available through Graphify.

## Laravel Rules

Follow the existing Laravel architecture and conventions.

Before creating a new controller, service, model, middleware or component:

1. Check whether an existing component already performs the required responsibility.
2. Use Graphify to inspect related components.
3. Prefer extending existing functionality over creating duplicate logic.

Do not introduce a new architectural pattern without explaining why it is necessary.

## Database

Never modify database structure directly without inspecting the existing migrations and models.

Before modifying a migration:

1. Inspect the related model.
2. Inspect existing migrations.
3. Check relationships and foreign keys.
4. Identify affected application components with Graphify.

Never silently delete production-relevant data.

## API / Backend

Before modifying an API endpoint:

1. Identify its route.
2. Identify its controller.
3. Identify related services.
4. Identify request validation.
5. Identify affected models.
6. Identify consumers of the endpoint.

Use Graphify to trace the relationships before editing.

## Frontend

Before modifying a frontend component:

1. Identify its route/page.
2. Identify its backend endpoint or controller.
3. Identify related components.
4. Check whether the component is reused elsewhere.

Avoid breaking existing responsive/PWA behavior.

## Authentication and Authorization

Authentication and authorization changes are high-impact changes.

Before modifying authentication:

- inspect routes;
- inspect middleware;
- inspect controllers;
- inspect models;
- inspect email verification;
- inspect authorization policies/gates if present;
- use Graphify to identify affected components.

Do not weaken authentication or authorization to make a feature work.

## Changes

Before modifying multiple files, explain briefly:

- entry point;
- affected components;
- relevant Graphify relationships;
- expected impact.

Prefer the smallest safe change.

Do not refactor unrelated code while implementing a requested feature.

## Verification

After changes:

1. Run the most relevant focused tests.
2. Run static/lint checks when available.
3. Verify affected routes/components.
4. Report what was tested and what could not be tested.

Never claim that a change works without verification.

## Generated Files

Do not manually edit:

- `graphify-out/`
- Graphify cache files
- generated build artifacts

unless the task explicitly requires it.

Graphify-generated files should be regenerated using Graphify commands.

## Uncertainty

If Graphify and the current source code disagree:

- trust the current source code;
- report the discrepancy;
- update the graph when appropriate.

Never invent architecture that is not supported by the repository.
