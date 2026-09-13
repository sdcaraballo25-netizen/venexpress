# Venexpress — Project Rules

## Project Context

Venexpress is a national logistics and shipping platform for Venezuela.

The system uses a decentralized network of physical partner locations
("Agencias Aliadas") to receive, process and deliver shipments.

The project is an MVP. Prefer simple, maintainable solutions over
unnecessary complexity.

---

## General Development Rules

Before modifying the project:

1. Understand the existing implementation.
2. Search for existing functionality before creating new functionality.
3. Reuse existing components, services, models and views when appropriate.
4. Make the smallest safe change that solves the requested problem.
5. Do not refactor unrelated code.
6. Do not introduce new architectural patterns without a clear reason.

Never invent architecture, models, relationships, routes or business rules
that are not supported by the existing code.

---

## Token Efficiency

Do not read the entire repository unless explicitly requested.

Prefer targeted searches and reading only the files relevant to the task.

When investigating a feature, start from the relevant:

- route
- Livewire component/controller
- model
- service
- Blade view
- migration

Avoid recursively inspecting unrelated directories.

---

## Laravel Architecture

Venexpress uses Laravel with Livewire and Blade.

Before creating a new:

- controller
- Livewire component
- service
- model
- middleware
- request
- policy

first check whether an existing component already performs the same
responsibility.

Prefer extending existing functionality instead of duplicating logic.

Follow the conventions already used in the project.

Do not introduce unnecessary repositories, DTOs, services or other
abstractions simply for the sake of architecture.

---

## Database

Never modify the database structure without inspecting the existing:

- migrations
- models
- relationships
- foreign keys

Before changing a migration:

1. Inspect the related model.
2. Inspect relevant migrations.
3. Check relationships and foreign keys.
4. Identify the application components affected by the change.

Never silently delete production-relevant data.

Prefer additive and backwards-compatible changes when possible.

---

## Backend / API

Before modifying an API endpoint, identify:

1. Route
2. Controller or Livewire component
3. Validation
4. Services
5. Models
6. Consumers of the endpoint

Verify that changes do not break existing consumers.

Reuse existing business logic whenever possible.

---

## Frontend

Venexpress is designed as a responsive web/PWA application.

Before modifying a frontend feature, identify:

1. Route or page
2. Livewire component/controller
3. Related Blade views
4. Reusable components
5. Backend functionality used by the page

Maintain responsive behavior for:

- desktop
- tablet
- mobile

Prefer the project's existing UI patterns and Tailwind conventions.

Do not replace working components unnecessarily.

---

## UI / Design

Venexpress visual identity uses:

- Yellow
- Blue
- Red

The interface should be:

- modern
- clean
- minimalist
- professional
- easy to understand

Avoid excessive decoration.

Prioritize usability and consistency across:

- Admin
- Agencia Aliada
- Repartidor
- Cliente

When modifying an existing interface, preserve established design patterns
unless the task specifically requests a redesign.

---

## Authentication and Authorization

Authentication and authorization are high-impact areas.

Before modifying them, inspect:

- routes
- middleware
- controllers
- Livewire components
- models
- policies/gates
- email verification when relevant

Never weaken authentication or authorization simply to make a feature work.

Always preserve role restrictions.

Current application roles include:

- admin
- aliado
- chofer
- cliente

Do not add or remove roles without confirming that the change is required.

---

## Business Logic

Venexpress handles logistics operations including:

- package registration
- shipping guides
- agencies
- routes
- drivers/repartidores
- clients
- tariffs
- package tracking
- incidents
- cash closing
- QR/barcode identification

Business rules must be implemented consistently.

Before changing a business rule, inspect where the rule is currently
implemented and whether other parts of the application depend on it.

Avoid duplicating business calculations in multiple places.

---

## Changes

For changes involving multiple files, briefly explain:

- entry point
- affected components
- important dependencies
- expected impact

Then implement the smallest reasonable change.

Do not perform unrelated refactors.

If a requested change requires a larger architectural modification,
explain why before making it.

---

## Verification

After making changes:

1. Run the most relevant focused tests.
2. Run static analysis or lint checks when available.
3. Verify affected routes/components.
4. Check for obvious regressions.

Always report:

- what was changed
- what was tested
- what could not be tested

Never claim that something works without verification.

---

## Generated Files

Do not manually edit:

- generated build files
- cache files
- compiled assets
- vendor files

unless the task explicitly requires it.

---

## Git

Keep changes focused and easy to review.

Before committing:

1. Check `git status`.
2. Review changed files.
3. Verify the implementation.
4. Avoid committing unrelated changes.

Do not reset, delete or overwrite other developers' work without
explicitly confirming it is safe.

---

## Working With Two Developers

The repository is shared by two developers.

Avoid modifying files unrelated to the requested task.

Be careful when changing shared configuration.

Before destructive Git operations such as:

- reset
- checkout of other developers' changes
- force push
- deleting branches

ask for confirmation unless the user explicitly requested the operation.

---

## Uncertainty

If the existing source code contradicts assumptions in these rules,
trust the current source code.

Report the discrepancy clearly.

Never invent:

- database relationships
- routes
- business rules
- component behavior
- API contracts

When necessary, inspect the relevant source files before deciding.