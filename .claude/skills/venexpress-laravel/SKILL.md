---
name: venexpress-laravel
description: "Use when developing, debugging, or modifying the Venexpress Laravel application. Covers Laravel 12, Livewire, Blade, Tailwind, MySQL, authentication, roles, packages, agencies, routes, drivers, clients, tariffs, tracking, QR guides, and logistics business logic."
---

# Venexpress Laravel Development

This skill defines how Claude Code should work on the Venexpress project.

Venexpress is a logistics and shipping platform for Venezuela built as an
MVP using Laravel, Livewire, Blade, Tailwind CSS and MySQL.

The project is developed by two developers, so changes must remain simple,
focused and easy to understand.

---

# Core Principle

Before changing code:

1. Understand the existing implementation.
2. Search for existing functionality.
3. Identify the entry point of the feature.
4. Trace its dependencies.
5. Make the smallest safe change.
6. Verify the result.

Do not rewrite working functionality without a clear reason.

Do not invent architecture, routes, models, relationships or business rules.

If the existing code contradicts assumptions, trust the source code.

---

# Technology Stack

Current project technologies include:

- Laravel 12
- PHP 8.2+
- Livewire
- Blade
- Tailwind CSS
- MySQL
- JavaScript
- PWA-oriented responsive frontend

Follow the project's existing conventions.

Do not introduce unnecessary frameworks or libraries.

---

# Investigation Strategy

When asked to modify a feature, do not inspect the entire repository.

Start with the most relevant files.

Typical investigation order:

1. Route
2. Livewire component or controller
3. Blade view
4. Model
5. Service
6. Migration
7. Related components

For example, for a driver scanner issue:

```text
route
↓
Driver\Scanner
↓
scanner.blade.php
↓
Package
↓
PackageHistory
↓
Route / RouteStop