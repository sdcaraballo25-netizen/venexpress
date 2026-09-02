# Graph Report - venexpress  (2026-09-02)

## Corpus Check
- 206 files · ~224,181 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 923 nodes · 1428 edges · 153 communities (129 shown, 24 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 11 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4244263b`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- Illuminate\Database\Migrations\Migration
- AllyUser
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- bootstrap/app.php
- LoginForm.php
- Venexpress — Project Rules
- AppServiceProvider
- Illuminate\View\Component
- logging.php
- Illuminate\Database\Eloquent\Factories\HasFactory
- graphify reference: extra exports and benchmark
- README.md
- ExampleTest
- profile.blade.php
- console.php
- verify-email.blade.php
- ally.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- Livewire\Component
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- Ally
- copilot-instructions.md
- rate-matrix-manager.blade.php
- Actualización automática de tasa BCV
- BcvRate
- User
- AuthenticationTest
- EmailVerificationTest.php
- PasswordResetTest.php
- PackageCreate
- PackageService
- DatabaseSeeder.php
- UsersManager
- Illuminate\Database\Schema\Blueprint
- Ally/navigation.blade.php
- Route
- routes-manager.blade.php
- package-create.blade.php
- Illuminate\Support\Facades\Schema
- App\Livewire\Ally\CreatePackage
- Ally/dashboard.blade.php
- Dashboard
- TariffService
- Dashboard
- Driver/dashboard.blade.php
- Package
- Commissions
- AllyStaffService
- clearSearch

## God Nodes (most connected - your core abstractions)
1. `User` - 76 edges
2. `Package` - 46 edges
3. `Route` - 35 edges
4. `Ally` - 30 edges
5. `RouteService` - 29 edges
6. `RoutesManager` - 22 edges
7. `TestCase` - 20 edges
8. `PackageCreate` - 19 edges
9. `PackageService` - 18 edges
10. `RouteStop` - 18 edges

## Surprising Connections (you probably didn't know these)
- `RouteService` --references--> `PackageService`  [EXTRACTED]
  app/Services/RouteService.php → app/Services/PackageService.php
- `TariffService` --references--> `BcvRateService`  [EXTRACTED]
  app/Services/TariffService.php → app/Services/BcvRateService.php
- `ProfileTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/ProfileTest.php → tests/TestCase.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php
- `EmailVerificationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/EmailVerificationTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (153 total, 24 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.18
Nodes (8): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, ExampleTest, TestCase

### Community 2 - "AllyUser"
Cohesion: 0.10
Nodes (10): AllyUser, self, self, self, AllyUserService, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Foundation\Auth\User (+2 more)

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

### Community 8 - "bootstrap/app.php"
Cohesion: 0.16
Nodes (11): DriverScanController, EnsureUserHasRole, Closure, Controller, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+3 more)

### Community 9 - "LoginForm.php"
Cohesion: 0.09
Nodes (16): VerifyEmailController, Controller, Logout, LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Auth (+8 more)

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

### Community 14 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.06
Nodes (13): CityDistanceManager, App\Models\Ally, CityDistance, Customer, App\Models\Driver, Incident, App\Models\PackageHistory, PackageHistory (+5 more)

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

### Community 72 - "Livewire\Component"
Cohesion: 0.23
Nodes (17): App\Livewire\Admin\AlliesManager, App\Livewire\Admin\BcvRateManager, App\Livewire\Admin\CityDistanceManager, App\Livewire\Admin\RateMatrixManager, App\Livewire\Admin\RoutesDashboard, RoutesDashboard, App\Livewire\Admin\RoutesManager, App\Livewire\Admin\UsersManager (+9 more)

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

### Community 84 - "Ally"
Cohesion: 0.24
Nodes (3): AlliesManager, Dashboard, Ally

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 91 - "BcvRate"
Cohesion: 0.11
Nodes (9): SyncBcvRate, BcvRateManager, BcvRate, BcvRateService, Carbon, Carbon\Carbon, Illuminate\Console\Command, Illuminate\Support\Facades\Cache (+1 more)

### Community 92 - "User"
Cohesion: 0.11
Nodes (5): User, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Notifications\Notifiable, ProfileTest, UserAuthorizationTest

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.29
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "PasswordResetTest.php"
Cohesion: 0.25
Nodes (3): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, PasswordResetTest

### Community 98 - "PackageService"
Cohesion: 0.23
Nodes (3): PackageService, TariffService, Driver

### Community 101 - "DatabaseSeeder.php"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 113 - "Route"
Cohesion: 0.07
Nodes (8): RoutesManager, Driver, Route, RouteStop, RouteService, VenezuelaLocationService, Illuminate\Database\Eloquent\Collection, Illuminate\Support\Facades\File

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, saveRecipientCustomer, saveSenderCustomer, $set(

### Community 128 - "App\Livewire\Ally\CreatePackage"
Cohesion: 0.29
Nodes (3): App\Livewire\Ally\CreatePackage, CreatePackage, Livewire\Attributes\Computed

### Community 135 - "TariffService"
Cohesion: 0.13
Nodes (5): RateMatrixManager, RateMatrix, DistanceApiService, App\Services\TariffService, TariffService

### Community 141 - "Package"
Cohesion: 0.15
Nodes (3): Scanner, Package, DateTimeInterface

## Knowledge Gaps
- **178 isolated node(s):** `registerAnother`, `openSenderCustomerModal`, `openRecipientCustomerModal`, `$set(`, `saveSenderCustomer` (+173 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **24 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `AllyUser`, `DatabaseSeeder.php`, `UsersManager`, `Livewire\Component`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyStaffService`, `Ally`, `EmailVerificationTest.php`, `AuthenticationTest`, `.isAdmin`, `PasswordResetTest.php`?**
  _High betweenness centrality (0.125) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `App\Livewire\Ally\CreatePackage`, `PackageService`, `Dashboard`, `TariffService`, `bootstrap/app.php`, `Livewire\Component`, `Dashboard`, `Commissions`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Route`, `Ally`?**
  _High betweenness centrality (0.067) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `AllyUser`, `PackageService`, `UsersManager`, `Livewire\Component`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyStaffService`, `Route`, `User`, `.isAdmin`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Package` (e.g. with `.monthlyBreakdown()` and `.render()`) actually correct?**
  _`Package` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `registerAnother`, `openSenderCustomerModal`, `openRecipientCustomerModal` to the rest of the system?**
  _178 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AllyUser` be split into smaller, more focused modules?**
  _Cohesion score 0.10461538461538461 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.045454545454545456 - nodes in this community are weakly interconnected._