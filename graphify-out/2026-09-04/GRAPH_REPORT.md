# Graph Report - venexpress  (2026-09-04)

## Corpus Check
- 264 files · ~251,400 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1270 nodes · 2233 edges · 196 communities (152 shown, 44 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 41 edges (avg confidence: 0.85)
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
- Ally
- graphify reference: extra exports and benchmark
- README.md
- ExampleTest
- profile.blade.php
- console.php
- verify-email.blade.php
- app.blade.php
- users-manager.blade.php
- layout/navigation.blade.php
- AllyUser
- TariffServiceTest
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Incident
- static
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
- Illuminate\Support\Facades\Schema
- RateMatrixManager
- User
- PackageService
- EmailVerificationTest.php
- CityDistance
- PackageCreate
- AlliesManager
- UsersManager
- ally-finance.blade.php
- BcvRate
- ally/navigation.blade.php
- Route
- routes-manager.blade.php
- Illuminate\Database\Schema\Blueprint
- package-create.blade.php
- AllyFinancialServiceTest
- PackageStatusUpdated
- Livewire\Component
- Illuminate\Database\Eloquent\Factories\HasFactory
- Ally/dashboard.blade.php
- Dashboard
- TariffService
- DriverPayment
- client/dashboard.blade.php
- Package
- web.php
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
- OfficeLocator
- TestCase
- ProfileTest
- Packages
- PasswordResetTest.php
- bcv-rate-manager.blade.php
- Logout.php
- PriceCalculator
- price-calculator.blade.php
- DailyCashCut
- Dashboard

## God Nodes (most connected - your core abstractions)
1. `Package` - 124 edges
2. `User` - 86 edges
3. `Route` - 46 edges
4. `Ally` - 40 edges
5. `RouteService` - 29 edges
6. `PackageService` - 29 edges
7. `AuditLog` - 28 edges
8. `Driver` - 26 edges
9. `AllyFinancialService` - 26 edges
10. `RoutesManager` - 24 edges

## Surprising Connections (you probably didn't know these)
- `createAlly()` --calls--> `User`  [INFERRED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/User.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php
- `TariffService` --references--> `BcvRateService`  [EXTRACTED]
  app/Services/TariffService.php → app/Services/BcvRateService.php
- `TariffService` --references--> `DistanceApiService`  [EXTRACTED]
  app/Services/TariffService.php → app/Services/DistanceApiService.php
- `RouteService` --references--> `PackageService`  [EXTRACTED]
  app/Services/RouteService.php → app/Services/PackageService.php

## Import Cycles
- None detected.

## Communities (196 total, 44 thin omitted)

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
Cohesion: 0.10
Nodes (18): VerifyEmailController, Controller, DriverScanController, PackageLabelController, EnsureUserHasRole, Barryvdh\DomPDF\Facade\Pdf, Closure, Illuminate\Console\Scheduling\Schedule (+10 more)

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

### Community 25 - "AllyUser"
Cohesion: 0.12
Nodes (8): AllyUser, AllyUserService, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash

### Community 71 - "Incident"
Cohesion: 0.13
Nodes (4): IncidentsManager, Incidents, Incident, IncidentService

### Community 72 - "static"
Cohesion: 0.19
Nodes (6): self, self, self, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

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

### Community 93 - "PackageService"
Cohesion: 0.08
Nodes (9): CreatePackage, PackageHistory, DestinationReceptionService, LogisticsScanService, PackageDispatchService, PackageService, Illuminate\Database\QueryException, Illuminate\Support\Facades\Log (+1 more)

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.29
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 111 - "BcvRate"
Cohesion: 0.08
Nodes (12): SyncBcvRate, BcvRateManager, Commissions, BcvRate, App\Services\BcvRateService, BcvRateService, DistanceApiService, Carbon (+4 more)

### Community 113 - "Route"
Cohesion: 0.05
Nodes (13): RoutesDashboard, RoutesManager, AuditLog, Driver, Route, RouteStop, DeliveryAssignmentService, DriverPaymentService (+5 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.12
Nodes (16): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+8 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 120 - "AllyFinancialServiceTest"
Cohesion: 0.17
Nodes (4): AllyFinancialService, App\Services\AllyFinancialService, InvalidArgumentException, AllyFinancialServiceTest

### Community 125 - "PackageStatusUpdated"
Cohesion: 0.32
Nodes (3): PackageStatusUpdated, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 126 - "Livewire\Component"
Cohesion: 0.14
Nodes (11): AuditLogViewer, App\Models\AuditLog, Illuminate\Support\Facades\Auth, Illuminate\Validation\Rule, Illuminate\Validation\Rules, Livewire\Attributes\Computed, Livewire\Attributes\Layout, Livewire\Attributes\Title (+3 more)

### Community 128 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.17
Nodes (19): App\Models\Ally, App\Models\AllyFinancialTransaction, App\Models\AllySettlement, App\Models\BcvRate, App\Models\CityDistance, Customer, App\Models\Driver, App\Models\DriverPayment (+11 more)

### Community 136 - "DriverPayment"
Cohesion: 0.20
Nodes (3): Dashboard, DriverPayments, DriverPayment

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.05
Nodes (10): DriverAssignment, PackageDispatch, PackageReception, Cod, PackagePickup, PackageDetail, PublicTracking, Package (+2 more)

### Community 142 - "web.php"
Cohesion: 0.18
Nodes (3): Dashboard, PackageReception, Illuminate\Support\Carbon

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "TestCase"
Cohesion: 0.11
Nodes (10): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Route, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, ExampleTest (+2 more)

### Community 179 - "PasswordResetTest.php"
Cohesion: 0.25
Nodes (3): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, PasswordResetTest

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

## Knowledge Gaps
- **214 isolated node(s):** `API / Backend`, `Architecture Navigation`, `Authentication and Authorization`, `Changes`, `Database` (+209 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **44 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyFinancialService`, `Dashboard`, `Illuminate\Http\Request`, `AppServiceProvider.php`, `web.php`, `Ally`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Packages`, `Dashboard`, `Incident`, `Scanner`, `RateMatrixManager`, `PackageService`, `BcvRate`, `Route`, `AllyFinancialServiceTest`, `PackageStatusUpdated`, `Livewire\Component`?**
  _High betweenness centrality (0.134) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AuthenticationTest`, `UsersManager`, `DriverPayment`, `static`, `Ally`, `TestCase`, `AllyStaffService`, `ProfileTest`, `PasswordResetTest.php`, `EmailVerificationTest.php`, `AllyFinancialServiceTest`, `AllyUser`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Livewire\Component`?**
  _High betweenness centrality (0.096) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyFinancialService`, `UsersManager`, `Incident`, `OfficeLocator`, `AllyStaffService`, `AllyFinance`, `Route`, `AllyFinancialServiceTest`, `AllyUser`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `User`, `PackageService`, `Livewire\Component`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 6 inferred relationships involving `User` (e.g. with `.render()` and `.test_credit_adjustment_increases_balance()`) actually correct?**
  _`User` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `API / Backend`, `Architecture Navigation`, `Authentication and Authorization` to the rest of the system?**
  _214 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.0425531914893617 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._