# Graph Report - venexpress  (2026-09-04)

## Corpus Check
- 263 files · ~250,572 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1262 nodes · 2229 edges · 199 communities (154 shown, 45 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 15 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f93cd9ea`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AuthenticationTest
- Illuminate\Database\Migrations\Migration
- AllyFinancialService
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Illuminate\Http\Request
- LoginForm.php
- Venexpress — Project Rules
- AppServiceProvider.php
- Illuminate\View\Component
- logging.php
- AuditLog
- graphify reference: extra exports and benchmark
- README.md
- ExampleTest
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- Ally
- TariffServiceTest
- AllySettlement
- Incident
- CityDistance
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- Scanner
- copilot-instructions.md
- rate-matrix-manager.blade.php
- Actualización automática de tasa BCV
- Illuminate\Database\Schema\Blueprint
- RateMatrixManager
- User
- PackageService
- EmailVerificationTest.php
- CityDistanceManager
- PackageCreate
- RuntimeException
- UsersManager
- ally-finance.blade.php
- BcvRate
- RateMatrix
- Route
- routes-manager.blade.php
- Illuminate\Support\Facades\Schema
- package-create.blade.php
- AllyFinancialServiceTest
- PackageStatusUpdated
- Livewire\Component
- Illuminate\Database\Eloquent\Factories\HasFactory
- Ally/dashboard.blade.php
- Dashboard
- TariffService
- Illuminate\Database\Eloquent\Relations\BelongsTo
- client/dashboard.blade.php
- CreatePackage.php
- Package
- Dashboard
- AllyStaffService
- AllyFinance
- package-detail.blade.php
- liquidate({{ $package->id }})
- markAsPaid({{ $payment->id }})
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- DriverAssignment.php
- TestCase
- ProfileTest
- PackagePickup.php
- PasswordResetTest
- bcv-rate-manager.blade.php
- Logout.php
- PriceCalculator
- price-calculator.blade.php
- UserFactory.php
- UserAuthorizationTest
- Driver/Dashboard.php

## God Nodes (most connected - your core abstractions)
1. `Package` - 130 edges
2. `User` - 89 edges
3. `Ally` - 54 edges
4. `Route` - 46 edges
5. `AuditLog` - 31 edges
6. `AllyFinancialService` - 29 edges
7. `PackageService` - 29 edges
8. `RouteService` - 29 edges
9. `Driver` - 27 edges
10. `TariffService` - 27 edges

## Surprising Connections (you probably didn't know these)
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `createAlly()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/User.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php

## Import Cycles
- None detected.

## Communities (199 total, 45 thin omitted)

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
Cohesion: 0.09
Nodes (19): VerifyEmailController, Controller, DriverScanController, PackageLabelController, EnsureUserHasRole, Barryvdh\DomPDF\Facade\Pdf, Closure, Illuminate\Console\Scheduling\Schedule (+11 more)

### Community 9 - "LoginForm.php"
Cohesion: 0.17
Nodes (8): LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Str, Illuminate\Validation\ValidationException, Livewire\Attributes\Validate, Livewire\Form, Pdo\Mysql

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider.php"
Cohesion: 0.20
Nodes (4): PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\ServiceProvider

### Community 12 - "Illuminate\View\Component"
Cohesion: 0.43
Nodes (4): AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "AuditLog"
Cohesion: 0.12
Nodes (9): AuditLog, Driver, DeliveryAssignmentService, DriverPaymentService, LogisticsScanService, Illuminate\Database\QueryException, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Log (+1 more)

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "Ally"
Cohesion: 0.05
Nodes (11): AlliesManager, OfficeLocator, Ally, AllyUser, AllyUserService, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Eloquent\Relations\HasMany (+3 more)

### Community 71 - "Incident"
Cohesion: 0.15
Nodes (3): IncidentsManager, Incident, IncidentService

### Community 72 - "CityDistance"
Cohesion: 0.24
Nodes (3): CityDistance, self, static

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
Cohesion: 0.15
Nodes (3): User, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Notifications\Notifiable

### Community 93 - "PackageService"
Cohesion: 0.23
Nodes (3): PackageHistory, HubReceptionService, PackageService

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.29
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 98 - "RuntimeException"
Cohesion: 0.21
Nodes (4): DestinationReceptionService, PackageDispatchService, Illuminate\Support\Facades\Auth, RuntimeException

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 111 - "BcvRate"
Cohesion: 0.08
Nodes (12): SyncBcvRate, BcvRateManager, Commissions, BcvRate, self, BcvRateService, DistanceApiService, Carbon (+4 more)

### Community 112 - "RateMatrix"
Cohesion: 0.25
Nodes (3): self, RateMatrix, InvalidArgumentException

### Community 113 - "Route"
Cohesion: 0.06
Nodes (7): RoutesManager, Route, RouteStop, RouteService, VenezuelaLocationService, Illuminate\Database\Eloquent\Collection, Illuminate\Support\Facades\File

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.12
Nodes (16): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+8 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 120 - "AllyFinancialServiceTest"
Cohesion: 0.17
Nodes (3): AllyFinancialServiceTest, createAlly(), createPackage()

### Community 125 - "PackageStatusUpdated"
Cohesion: 0.32
Nodes (3): PackageStatusUpdated, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 126 - "Livewire\Component"
Cohesion: 0.11
Nodes (15): AuditLogViewer, Dashboard, DriverPayments, RoutesDashboard, DailyCashCut, Incidents, Packages, Illuminate\Support\Carbon (+7 more)

### Community 128 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.22
Nodes (3): Customer, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.06
Nodes (8): PackageDispatch, PackageReception, Cod, PackageReception, PackageDetail, PublicTracking, Package, DateTimeInterface

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "TestCase"
Cohesion: 0.14
Nodes (10): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest (+2 more)

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

## Knowledge Gaps
- **211 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+206 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **45 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyFinancialService`, `Dashboard`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AppServiceProvider.php`, `CreatePackage.php`, `AuditLog`, `Dashboard`, `Ally`, `DriverAssignment.php`, `PackagePickup.php`, `Driver/Dashboard.php`, `Scanner`, `PackageService`, `RuntimeException`, `BcvRate`, `RateMatrix`, `Route`, `AllyFinancialServiceTest`, `PackageStatusUpdated`, `Livewire\Component`?**
  _High betweenness centrality (0.145) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AuthenticationTest`, `UsersManager`, `AuditLog`, `TestCase`, `AllyStaffService`, `ProfileTest`, `PasswordResetTest`, `EmailVerificationTest.php`, `AllyFinancialServiceTest`, `Ally`, `UserFactory.php`, `UserAuthorizationTest`, `Livewire\Component`?**
  _High betweenness centrality (0.083) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyFinancialService`, `UsersManager`, `Incident`, `AuditLog`, `AllyStaffService`, `AllyFinance`, `Route`, `AllyFinancialServiceTest`, `User`, `PackageService`, `Livewire\Component`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _211 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.0425531914893617 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._
- **Should `devDependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.07692307692307693 - nodes in this community are weakly interconnected._