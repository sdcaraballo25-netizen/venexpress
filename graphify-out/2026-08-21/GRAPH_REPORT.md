# Graph Report - venexpress  (2026-08-21)

## Corpus Check
- 140 files · ~191,742 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 574 nodes · 744 edges · 88 communities (77 shown, 11 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 4 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f36a987e`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- User
- Illuminate\Database\Migrations\Migration
- Package
- composer.json
- CityDistance
- What You Must Do When Invoked
- devDependencies
- scripts
- VerifyEmailController.php
- LoginForm.php
- Venexpress — Project Rules
- AppServiceProvider
- Illuminate\View\Component
- logging.php
- BcvRate
- graphify reference: extra exports and benchmark
- README.md
- ExampleTest
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- package-create.blade.php
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

## God Nodes (most connected - your core abstractions)
1. `User` - 41 edges
2. `Package` - 21 edges
3. `TestCase` - 18 edges
4. `TariffService` - 17 edges
5. `BcvRate` - 16 edges
6. `CityDistance` - 14 edges
7. `RateMatrix` - 14 edges
8. `Venexpress — Project Rules` - 13 edges
9. `BcvRateManager` - 12 edges
10. `CityDistanceManager` - 12 edges

## Surprising Connections (you probably didn't know these)
- `TariffService` --references--> `BcvRateService`  [EXTRACTED]
  app/Services/TariffService.php → app/Services/BcvRateService.php
- `PackageService` --references--> `TariffService`  [EXTRACTED]
  app/Services/PackageService.php → app/Services/TariffService.php
- `VerifyEmailController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Auth/VerifyEmailController.php → app/Http/Controllers/Controller.php
- `DriverScanController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DriverScanController.php → app/Http/Controllers/Controller.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (88 total, 11 thin omitted)

### Community 0 - "User"
Cohesion: 0.05
Nodes (24): User, DatabaseSeeder, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Database\Seeder, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase (+16 more)

### Community 1 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.06
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 2 - "Package"
Cohesion: 0.09
Nodes (12): CreatePackage, Ally, Driver, Package, PackageHistory, PackageService, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model (+4 more)

### Community 3 - "composer.json"
Cohesion: 0.05
Nodes (43): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+35 more)

### Community 4 - "CityDistance"
Cohesion: 0.11
Nodes (8): CityDistanceManager, self, CityDistance, self, self, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

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

### Community 14 - "BcvRate"
Cohesion: 0.11
Nodes (12): BcvRateManager, Dashboard, BcvRate, BcvRateService, Carbon, Carbon\Carbon, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Route (+4 more)

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

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
Cohesion: 0.15
Nodes (3): RateMatrixManager, RateMatrix, TariffService

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

## Knowledge Gaps
- **137 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+132 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **11 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Package`, `CityDistance`?**
  _High betweenness centrality (0.091) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `TariffService`, `BcvRate`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Why does `BcvRate` connect `BcvRate` to `Package`, `CityDistance`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _137 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.05443371378402107 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Database\Migrations\Migration` be split into smaller, more focused modules?**
  _Cohesion score 0.0593990216631726 - nodes in this community are weakly interconnected._
- **Should `Package` be split into smaller, more focused modules?**
  _Cohesion score 0.0898989898989899 - nodes in this community are weakly interconnected._