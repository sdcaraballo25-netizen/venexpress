# Graph Report - venexpress  (2026-08-23)

## Corpus Check
- 155 files · ~195,305 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 651 nodes · 889 edges · 120 communities (105 shown, 15 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 4 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `7b5a907b`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Foundation\Testing\RefreshDatabase
- Illuminate\Database\Migrations\Migration
- Package
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- VerifyEmailController.php
- LoginForm.php
- Venexpress — Project Rules
- AppServiceProvider
- Illuminate\View\Component
- logging.php
- CityDistance
- graphify reference: extra exports and benchmark
- README.md
- ExampleTest
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- TariffService
- copilot-instructions.md
- rate-matrix-manager.blade.php
- UsersManager
- User
- BcvRate
- EmailVerificationTest.php
- PasswordResetTest.php
- Illuminate\Support\Facades\Schema
- Illuminate\Database\Schema\Blueprint
- ProfileTest
- TestCase
- DatabaseSeeder.php
- package-create.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 66 edges
2. `Package` - 21 edges
3. `TestCase` - 20 edges
4. `UsersManager` - 17 edges
5. `TariffService` - 17 edges
6. `Ally` - 16 edges
7. `BcvRate` - 16 edges
8. `CityDistance` - 14 edges
9. `RateMatrix` - 14 edges
10. `Venexpress — Project Rules` - 13 edges

## Surprising Connections (you probably didn't know these)
- `TariffService` --references--> `BcvRateService`  [EXTRACTED]
  app/Services/TariffService.php → app/Services/BcvRateService.php
- `PackageService` --references--> `TariffService`  [EXTRACTED]
  app/Services/PackageService.php → app/Services/TariffService.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php
- `EmailVerificationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/EmailVerificationTest.php → tests/TestCase.php
- `PasswordConfirmationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/PasswordConfirmationTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (120 total, 15 thin omitted)

### Community 0 - "Illuminate\Foundation\Testing\RefreshDatabase"
Cohesion: 0.16
Nodes (6): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Support\Facades\Hash, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest

### Community 2 - "Package"
Cohesion: 0.08
Nodes (11): CreatePackage, Driver, Package, PackageHistory, PackageService, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo (+3 more)

### Community 3 - "composer.json"
Cohesion: 0.05
Nodes (43): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+35 more)

### Community 4 - "allies-manager.blade.php"
Cohesion: 0.40
Nodes (4): activate({{ $ally->id }}), approve({{ $ally->id }}), reject({{ $ally->id }}), suspend({{ $ally->id }})

### Community 5 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 6 - "devDependencies"
Cohesion: 0.08
Nodes (25): autoprefixer, axios, concurrently, laravel-vite-plugin, devDependencies, autoprefixer, axios, concurrently (+17 more)

### Community 7 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "VerifyEmailController.php"
Cohesion: 0.13
Nodes (13): VerifyEmailController, Controller, DriverScanController, EnsureUserHasRole, Closure, Illuminate\Auth\Events\Verified, Illuminate\Foundation\Application, Illuminate\Foundation\Auth\EmailVerificationRequest (+5 more)

### Community 9 - "LoginForm.php"
Cohesion: 0.11
Nodes (11): Logout, LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Session, Illuminate\Support\Str, Illuminate\Validation\ValidationException (+3 more)

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider"
Cohesion: 0.28
Nodes (3): AppServiceProvider, VoltServiceProvider, Illuminate\Support\ServiceProvider

### Community 12 - "Illuminate\View\Component"
Cohesion: 0.43
Nodes (4): AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "CityDistance"
Cohesion: 0.11
Nodes (8): CityDistanceManager, self, CityDistance, self, self, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 23 - "users-manager.blade.php"
Cohesion: 0.25
Nodes (7): closeCreateModal, createUser, deleteUser, openCreateModal, requestDelete({{ $user->id }}), $set(, toggleStatus({{ $user->id }})

### Community 76 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 77 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 78 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 79 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

### Community 84 - "TariffService"
Cohesion: 0.16
Nodes (3): RateMatrixManager, RateMatrix, TariffService

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 91 - "UsersManager"
Cohesion: 0.09
Nodes (12): AlliesManager, Dashboard, UsersManager, Ally, AuditLog, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Route, Illuminate\Validation\Rules (+4 more)

### Community 92 - "User"
Cohesion: 0.13
Nodes (5): User, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, AuthenticationTest

### Community 93 - "BcvRate"
Cohesion: 0.16
Nodes (5): BcvRateManager, BcvRate, BcvRateService, Carbon, Carbon\Carbon

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.29
Nodes (3): Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "PasswordResetTest.php"
Cohesion: 0.25
Nodes (3): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, PasswordResetTest

### Community 100 - "TestCase"
Cohesion: 0.22
Nodes (4): Illuminate\Foundation\Testing\TestCase, ExampleTest, TestCase, UserAuthorizationTest

### Community 101 - "DatabaseSeeder.php"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

## Knowledge Gaps
- **148 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+143 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **15 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Foundation\Testing\RefreshDatabase`, `Package`, `.isAdmin`, `ProfileTest`, `DatabaseSeeder.php`, `TestCase`, `CityDistance`, `UsersManager`, `EmailVerificationTest.php`, `PasswordResetTest.php`?**
  _High betweenness centrality (0.151) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `UsersManager`, `TariffService`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _148 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Package` be split into smaller, more focused modules?**
  _Cohesion score 0.08405797101449275 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.045454545454545456 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._
- **Should `devDependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.07692307692307693 - nodes in this community are weakly interconnected._