# Graph Report - venexpress  (2026-08-26)

## Corpus Check
- 186 files · ~213,904 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 837 nodes · 1290 edges · 136 communities (117 shown, 19 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 8 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `59e14109`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- Ally
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
- ally.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- TariffService
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- Package
- copilot-instructions.md
- rate-matrix-manager.blade.php
- UsersManager
- BcvRate
- User
- AuthenticationTest
- EmailVerificationTest.php
- PasswordResetTest
- PasswordUpdateTest.php
- DatabaseSeeder.php
- Illuminate\Database\Migrations\Migration
- Illuminate\Database\Schema\Blueprint
- Illuminate\Support\Facades\Schema
- Ally/navigation.blade.php
- Route
- routes-manager.blade.php
- registerAnother
- Ally/dashboard.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 76 edges
2. `Route` - 35 edges
3. `Package` - 32 edges
4. `Ally` - 31 edges
5. `RouteService` - 29 edges
6. `RoutesManager` - 23 edges
7. `TariffService` - 20 edges
8. `TestCase` - 20 edges
9. `RouteStop` - 18 edges
10. `UsersManager` - 17 edges

## Surprising Connections (you probably didn't know these)
- `TariffService` --references--> `BcvRateService`  [EXTRACTED]
  app/Services/TariffService.php → app/Services/BcvRateService.php
- `PackageService` --references--> `TariffService`  [EXTRACTED]
  app/Services/PackageService.php → app/Services/TariffService.php
- `RouteService` --references--> `PackageService`  [EXTRACTED]
  app/Services/RouteService.php → app/Services/PackageService.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php
- `EmailVerificationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/EmailVerificationTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (136 total, 19 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.16
Nodes (9): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, PasswordConfirmationTest, RegistrationTest, ExampleTest (+1 more)

### Community 2 - "Ally"
Cohesion: 0.05
Nodes (14): AlliesManager, Ally, AllyUser, Incident, PackageHistory, AllyUserService, IncidentService, Illuminate\Database\Eloquent\Factories\HasFactory (+6 more)

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
Cohesion: 0.14
Nodes (12): VerifyEmailController, Controller, DriverScanController, EnsureUserHasRole, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Foundation\Configuration\Exceptions (+4 more)

### Community 9 - "LoginForm.php"
Cohesion: 0.11
Nodes (12): Logout, LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\Session, Illuminate\Support\Str (+4 more)

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

### Community 72 - "TariffService"
Cohesion: 0.10
Nodes (4): RateMatrixManager, PackageCreate, RateMatrix, TariffService

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

### Community 84 - "Package"
Cohesion: 0.08
Nodes (16): Dashboard, RoutesDashboard, CreatePackage, Dashboard, Driver, Package, PackageService, Illuminate\Database\Eloquent\Collection (+8 more)

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 91 - "BcvRate"
Cohesion: 0.16
Nodes (5): BcvRateManager, BcvRate, BcvRateService, Carbon, Carbon\Carbon

### Community 92 - "User"
Cohesion: 0.15
Nodes (3): User, ProfileTest, UserAuthorizationTest

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.29
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 99 - "PasswordUpdateTest.php"
Cohesion: 0.22
Nodes (3): AllyStaffService, Illuminate\Support\Facades\Hash, PasswordUpdateTest

### Community 101 - "DatabaseSeeder.php"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 113 - "Route"
Cohesion: 0.07
Nodes (6): RoutesManager, Route, RouteStop, RouteService, VenezuelaLocationService, Illuminate\Support\Facades\File

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

## Knowledge Gaps
- **167 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+162 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **19 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `Ally`, `PasswordUpdateTest.php`, `.isAdmin`, `DatabaseSeeder.php`, `CityDistance`, `Package`, `UsersManager`, `AuthenticationTest`, `EmailVerificationTest.php`, `PasswordResetTest`?**
  _High betweenness centrality (0.132) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `.isAdmin`, `PasswordUpdateTest.php`, `Route`, `Package`, `UsersManager`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `TariffService`, `Route`, `Ally`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _167 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Ally` be split into smaller, more focused modules?**
  _Cohesion score 0.0546583850931677 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.045454545454545456 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._