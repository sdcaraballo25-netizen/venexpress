# Graph Report - venexpress  (2026-09-03)

## Corpus Check
- 250 files · ~238,812 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1142 nodes · 1889 edges · 194 communities (147 shown, 47 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 30 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `e8f235f6`
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
- Illuminate\Http\Request
- BcvRate
- Venexpress — Project Rules
- AppServiceProvider
- Illuminate\View\Component
- logging.php
- Illuminate\Database\Eloquent\Relations\BelongsTo
- graphify reference: extra exports and benchmark
- README.md
- ExampleTest
- profile.blade.php
- console.php
- verify-email.blade.php
- app.blade.php
- users-manager.blade.php
- layout/navigation.blade.php
- RuntimeException
- Route
- IncidentsManager
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- Dashboard
- copilot-instructions.md
- rate-matrix-manager.blade.php
- Actualización automática de tasa BCV
- CityDistance
- User
- App\Models\Package
- VerifyEmailController.php
- Dashboard
- PackageCreate
- AuthenticationTest
- Illuminate\Database\Migrations\Migration
- DatabaseSeeder
- UsersManager
- Illuminate\Database\Schema\Blueprint
- Scanner
- Ally/navigation.blade.php
- RouteService
- routes-manager.blade.php
- package-create.blade.php
- ProfileTest
- Livewire\Component
- EmailVerificationTest.php
- Ally/dashboard.blade.php
- App\Livewire\Admin\DriverAssignment
- TariffService
- Illuminate\Database\Eloquent\Factories\HasFactory
- Driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- Commissions
- AllyStaffService
- Illuminate\Support\Facades\Schema
- package-detail.blade.php
- liquidate({{ $package->id }})
- markAsPaid({{ $payment->id }})
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- Driver
- Illuminate\Database\Eloquent\Relations\HasMany
- UserAuthorizationTest
- PackageReception
- App\Models\Ally
- AuditLog
- PasswordResetTest
- bcv-rate-manager.blade.php
- Logout.php
- Cod
- PriceCalculator
- price-calculator.blade.php
- Route
- TariffService

## God Nodes (most connected - your core abstractions)
1. `User` - 80 edges
2. `Package` - 79 edges
3. `RouteService` - 29 edges
4. `PackageService` - 28 edges
5. `Ally` - 28 edges
6. `RoutesManager` - 22 edges
7. `TestCase` - 20 edges
8. `PackageCreate` - 19 edges
9. `Route` - 18 edges
10. `UsersManager` - 17 edges

## Surprising Connections (you probably didn't know these)
- `RouteService` --references--> `PackageService`  [EXTRACTED]
  app/Services/RouteService.php → app/Services/PackageService.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php
- `EmailVerificationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/EmailVerificationTest.php → tests/TestCase.php
- `PasswordResetTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/PasswordResetTest.php → tests/TestCase.php
- `ProfileTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/ProfileTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (194 total, 47 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.14
Nodes (10): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest (+2 more)

### Community 2 - "Ally"
Cohesion: 0.09
Nodes (7): AlliesManager, OfficeLocator, Ally, AllyUser, AllyUserService, Illuminate\Foundation\Auth\User, Illuminate\Support\Facades\Hash

### Community 3 - "composer.json"
Cohesion: 0.04
Nodes (46): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+38 more)

### Community 4 - "allies-manager.blade.php"
Cohesion: 0.25
Nodes (7): activate({{ $ally->id }}), approve({{ $ally->id }}), editLocation({{ $ally->id }}), reject({{ $ally->id }}), $set(, saveLocation, suspend({{ $ally->id }})

### Community 5 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 6 - "devDependencies"
Cohesion: 0.08
Nodes (25): autoprefixer, axios, concurrently, laravel-vite-plugin, devDependencies, autoprefixer, axios, concurrently (+17 more)

### Community 7 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.12
Nodes (15): DriverScanController, PackageLabelController, EnsureUserHasRole, Barryvdh\DomPDF\Facade\Pdf, Closure, Controller, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application (+7 more)

### Community 9 - "BcvRate"
Cohesion: 0.07
Nodes (16): BcvRateManager, LoginForm, BcvRate, self, self, self, UserFactory, Illuminate\Auth\Events\Lockout (+8 more)

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

### Community 14 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.14
Nodes (4): DriverPayments, DriverPayment, Incident, Illuminate\Database\Eloquent\Relations\BelongsTo

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

### Community 25 - "RuntimeException"
Cohesion: 0.11
Nodes (11): App\Livewire\Admin\RoutesManager, App\Livewire\Ally\CreatePackage, PackageHistory, DeliveryAssignmentService, Route, DestinationReceptionService, HubReceptionService, PackageDispatchService (+3 more)

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

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 92 - "User"
Cohesion: 0.14
Nodes (4): Ally, User, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Notifications\Notifiable

### Community 93 - "App\Models\Package"
Cohesion: 0.09
Nodes (15): CreatePackage, PackageDetail, App\Models\Package, PackageStatusUpdated, Package, LogisticsScanService, PackageService, Driver (+7 more)

### Community 94 - "VerifyEmailController.php"
Cohesion: 0.31
Nodes (5): VerifyEmailController, Controller, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 101 - "DatabaseSeeder"
Cohesion: 0.33
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 113 - "RouteService"
Cohesion: 0.09
Nodes (8): RoutesManager, App\Models\Route, Driver, RouteService, VenezuelaLocationService, Illuminate\Support\Facades\File, Route, RouteStop

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 126 - "Livewire\Component"
Cohesion: 0.18
Nodes (18): App\Livewire\Admin\AlliesManager, App\Livewire\Admin\BcvRateManager, App\Livewire\Admin\CityDistanceManager, Dashboard, App\Livewire\Admin\DriverPayments, App\Livewire\Admin\RateMatrixManager, App\Livewire\Admin\RoutesDashboard, RoutesDashboard (+10 more)

### Community 128 - "EmailVerificationTest.php"
Cohesion: 0.29
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 135 - "TariffService"
Cohesion: 0.08
Nodes (14): SyncBcvRate, RateMatrixManager, App\Models\BcvRate, RateMatrix, BcvRateService, DistanceApiService, App\Services\TariffService, TariffService (+6 more)

### Community 136 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.23
Nodes (13): App\Models\AuditLog, App\Models\Customer, Customer, App\Models\Driver, App\Models\PackageHistory, App\Models\RateMatrix, App\Models\RouteStop, Illuminate\Database\Eloquent\Collection (+5 more)

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.08
Nodes (7): PackageDispatch, Dashboard, PackagePickup, App\Livewire\Ally\PackageReception, PackageReception, Package, DateTimeInterface

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 177 - "App\Models\Ally"
Cohesion: 0.48
Nodes (4): App\Models\Ally, App\Models\Incident, IncidentService, Incident

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.50
Nodes (3): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit

## Knowledge Gaps
- **205 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+200 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **47 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `EmailVerificationTest.php`, `Ally`, `AuthenticationTest`, `DatabaseSeeder`, `UsersManager`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `BcvRate`, `Illuminate\Database\Eloquent\Relations\HasMany`, `UserAuthorizationTest`, `AllyStaffService`, `AuditLog`, `PasswordResetTest`, `ProfileTest`, `Livewire\Component`?**
  _High betweenness centrality (0.116) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `App\Livewire\Admin\DriverAssignment`, `Route`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `TariffService`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Commissions`, `PackageReception`, `Scanner`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AuditLog`, `Dashboard`, `Cod`, `RuntimeException`, `App\Models\Package`, `Livewire\Component`?**
  _High betweenness centrality (0.068) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Database\Eloquent\Relations\HasMany`, `AllyStaffService`, `App\Models\Ally`, `RuntimeException`, `Livewire\Component`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Package` (e.g. with `.monthlyBreakdown()` and `.render()`) actually correct?**
  _`Package` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _205 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.13666666666666666 - nodes in this community are weakly interconnected._
- **Should `Ally` be split into smaller, more focused modules?**
  _Cohesion score 0.09090909090909091 - nodes in this community are weakly interconnected._