# Graph Report - venexpress  (2026-09-04)

## Corpus Check
- 272 files · ~255,272 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1352 nodes · 2464 edges · 193 communities (159 shown, 34 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 55 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5c1329eb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- .route
- Illuminate\Database\Migrations\Migration
- AllyFinancialService
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Illuminate\Http\Request
- CityDistance
- Venexpress — Project Rules
- AppServiceProvider.php
- logging.php
- AuditLog
- graphify reference: extra exports and benchmark
- README.md
- PackageServiceCodTest
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- Ally
- BcvRate
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Incident
- App\Livewire\Ally\PackageReception
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
- Customer
- User
- App\Models\Package
- EmailVerificationTest.php
- PackageDetail
- PackageCreate
- RuntimeException
- Packages.php
- UsersManager
- ally-finance.blade.php
- TariffService
- AuditLogViewer
- RouteService
- routes-manager.blade.php
- package-create.blade.php
- AllyFinancialServiceTest
- Illuminate\Database\Schema\Blueprint
- PackageStatusUpdated
- Livewire\Component
- Illuminate\Database\Eloquent\Factories\HasFactory
- Ally/dashboard.blade.php
- Dashboard
- DailyCashCut
- DriverPayments
- client/dashboard.blade.php
- Package
- Commissions.php
- AllyStaffService
- AllyFinance
- package-detail.blade.php
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- TestCase
- PasswordResetTest.php
- bcv-rate-manager.blade.php
- Logout.php
- CreatePackage.php
- price-calculator.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 89 edges
2. `Package` - 87 edges
3. `Ally` - 52 edges
4. `AuditLog` - 44 edges
5. `AllyFinancialService` - 32 edges
6. `PackageService` - 32 edges
7. `RouteService` - 29 edges
8. `TariffService` - 27 edges
9. `Route` - 27 edges
10. `Money` - 24 edges

## Surprising Connections (you probably didn't know these)
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php

## Import Cycles
- None detected.

## Communities (193 total, 34 thin omitted)

### Community 2 - "AllyFinancialService"
Cohesion: 0.29
Nodes (5): AllyFinancialTransaction, AllySettlement, App\Models\AllySettlement, AllyFinancialService, Package

### Community 3 - "composer.json"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

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
Cohesion: 0.07
Nodes (25): VerifyEmailController, Controller, DriverScanController, PackageLabelController, Package, TrackingController, EnsureUserHasRole, AppLayout (+17 more)

### Community 9 - "CityDistance"
Cohesion: 0.07
Nodes (15): CityDistanceManager, LoginForm, self, CityDistance, self, UserFactory, Illuminate\Auth\Events\Lockout, Illuminate\Database\Eloquent\Factories\Factory (+7 more)

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider.php"
Cohesion: 0.18
Nodes (6): App\Observers\PackageObserver, PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\Facades\Log, Illuminate\Support\ServiceProvider

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

### Community 25 - "Ally"
Cohesion: 0.06
Nodes (12): AlliesManager, OfficeLocator, Ally, AllyUser, Driver, AllyUserService, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User (+4 more)

### Community 26 - "BcvRate"
Cohesion: 0.06
Nodes (9): BcvRateManager, RateMatrixManager, BcvRate, self, RateMatrix, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder (+1 more)

### Community 27 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.08
Nodes (5): AllyFinancialTransaction, AllySettlement, DriverPayment, PackageHistory, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 71 - "Incident"
Cohesion: 0.15
Nodes (3): IncidentsManager, Incident, IncidentService

### Community 72 - "App\Livewire\Ally\PackageReception"
Cohesion: 0.28
Nodes (3): App\Livewire\Ally\PackageReception, PackageReception, DestinationReceptionService

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

### Community 91 - "Customer"
Cohesion: 0.50
Nodes (3): Customer, ClientDashboardTest, User

### Community 92 - "User"
Cohesion: 0.13
Nodes (4): User, Illuminate\Database\Eloquent\Relations\HasOne, ProfileTest, UserAuthorizationTest

### Community 93 - "App\Models\Package"
Cohesion: 0.08
Nodes (21): CreatePackage, Package, PublicTracking, App\Models\Driver, App\Models\DriverPayment, App\Models\Package, App\Models\PackageHistory, DriverPaymentService (+13 more)

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 98 - "RuntimeException"
Cohesion: 0.12
Nodes (8): App\Livewire\Admin\DriverAssignment, Dashboard, DeliveryAssignmentService, PackageDispatchService, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Route, RuntimeException

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 111 - "TariffService"
Cohesion: 0.05
Nodes (18): CheckProductionReadiness, SyncBcvRate, App\Models\BcvRate, App\Models\RateMatrix, BcvRateService, DistanceApiService, TariffService, Money (+10 more)

### Community 113 - "RouteService"
Cohesion: 0.06
Nodes (12): RoutesManager, App\Models\Route, Route, App\Models\RouteStop, RouteStop, Driver, RouteService, VenezuelaLocationService (+4 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.12
Nodes (16): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+8 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 125 - "PackageStatusUpdated"
Cohesion: 0.32
Nodes (4): App\Notifications\PackageStatusUpdated, PackageStatusUpdated, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 126 - "Livewire\Component"
Cohesion: 0.21
Nodes (21): App\Livewire\Admin\AlliesManager, App\Livewire\Admin\AllyFinance, App\Livewire\Admin\AuditLogViewer, App\Livewire\Admin\BcvRateManager, App\Livewire\Admin\CityDistanceManager, Dashboard, App\Livewire\Admin\IncidentsManager, App\Livewire\Admin\RateMatrixManager (+13 more)

### Community 128 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.21
Nodes (15): App\Models\Ally, App\Models\AllyFinancialTransaction, App\Models\AuditLog, App\Models\CityDistance, App\Models\Customer, App\Models\Incident, App\Models\User, Illuminate\Database\Eloquent\Factories\HasFactory (+7 more)

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.07
Nodes (7): DriverAssignment, PackageDispatch, PackageReception, Cod, PackagePickup, Package, DateTimeInterface

### Community 142 - "Commissions.php"
Cohesion: 0.21
Nodes (4): Commissions, Dashboard, Carbon, Illuminate\Support\Carbon

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 161 - "driver-payments.blade.php"
Cohesion: 0.50
Nodes (3): cancelPayment({{ $payment->id }}), markAsPaid({{ $payment->id }}), markPaid({{ $payment->id }})

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "TestCase"
Cohesion: 0.13
Nodes (7): Illuminate\Foundation\Testing\TestCase, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, ExampleTest, TestCase

### Community 179 - "PasswordResetTest.php"
Cohesion: 0.25
Nodes (3): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, PasswordResetTest

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

### Community 184 - "CreatePackage.php"
Cohesion: 0.25
Nodes (3): App\Livewire\Public\PriceCalculator, PriceCalculator, Livewire\Attributes\Computed

## Knowledge Gaps
- **214 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+209 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **34 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `.route`, `UsersManager`, `CityDistance`, `.isAdmin`, `TestCase`, `AllyStaffService`, `PasswordResetTest.php`, `EmailVerificationTest.php`, `AllyFinancialServiceTest`, `Ally`, `BcvRate`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Livewire\Component`?**
  _High betweenness centrality (0.116) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `RuntimeException`, `Packages.php`, `Illuminate\Http\Request`, `App\Livewire\Ally\PackageReception`, `AppServiceProvider.php`, `Commissions.php`, `Scanner`, `PackageStatusUpdated`, `CreatePackage.php`, `Ally`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `App\Models\Package`, `Livewire\Component`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyFinancialService`, `UsersManager`, `Incident`, `.isAdmin`, `AllyStaffService`, `AllyFinance`, `RouteService`, `BcvRate`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `User`, `App\Models\Package`, `Livewire\Component`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Are the 5 inferred relationships involving `Ally` (e.g. with `.createAdjustment()` and `.createSettlement()`) actually correct?**
  _`Ally` has 5 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _214 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._