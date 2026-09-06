# Graph Report - venexpress  (2026-09-05)

## Corpus Check
- 286 files · ~261,970 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1406 nodes · 2591 edges · 204 communities (167 shown, 37 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 60 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `440ac131`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- .route
- Illuminate\Support\Facades\Schema
- AllyFinancialService
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- PaymentReconciliationService
- CityDistance
- Venexpress — Project Rules
- AppServiceProvider.php
- User
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
- PaymentOrder
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
- Customer
- ProfileTest
- App\Models\Package
- EmailVerificationTest.php
- require-dev
- Illuminate\Database\Migrations\Migration
- PackageCreate
- RuntimeException
- Packages
- UsersManager
- ally-finance.blade.php
- TariffService
- AuditLogViewer
- RouteService
- routes-manager.blade.php
- package-create.blade.php
- AllyFinancialServiceTest
- Illuminate\Foundation\Testing\RefreshDatabase
- Illuminate\Database\Schema\Blueprint
- PaymentOrders
- PackageStatusUpdated
- Livewire\Component
- Client/Dashboard.php
- Illuminate\Database\Eloquent\Factories\HasFactory
- Ally/dashboard.blade.php
- require
- setup
- DriverPayments
- client/dashboard.blade.php
- Package
- Commissions.php
- Illuminate\Support\Facades\Hash
- AllyFinance
- package-detail.blade.php
- VerifyEmailController.php
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- TestCase
- config
- PublicTracking
- PasswordResetTest.php
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PriceCalculator
- price-calculator.blade.php
- keywords
- dev
- AllyFinancialTransaction

## God Nodes (most connected - your core abstractions)
1. `User` - 89 edges
2. `Package` - 83 edges
3. `Ally` - 53 edges
4. `AuditLog` - 45 edges
5. `AllyFinancialService` - 35 edges
6. `PackageService` - 30 edges
7. `PaymentOrder` - 29 edges
8. `RouteService` - 29 edges
9. `Route` - 27 edges
10. `AllyFinancialTransaction` - 26 edges

## Surprising Connections (you probably didn't know these)
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php

## Import Cycles
- None detected.

## Communities (204 total, 37 thin omitted)

### Community 2 - "AllyFinancialService"
Cohesion: 0.12
Nodes (6): AllySettlement, DailyCashCut, AllyFinancialTransaction, App\Models\AllySettlement, AllyFinancialService, Package

### Community 3 - "composer.json"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, extra, laravel, dont-discover, license, minimum-stability (+5 more)

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
Cohesion: 0.13
Nodes (15): scripts, post-autoload-dump, post-create-project-cmd, post-update-cmd, pre-package-uninstall, test, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, Illuminate\\Foundation\\ComposerScripts::prePackageUninstall (+7 more)

### Community 8 - "PaymentReconciliationService"
Cohesion: 0.07
Nodes (23): DriverScanController, PaymentWebhookController, App\Http\Controllers\TrackingController, Package, TrackingController, EnsureUserHasRole, PaymentReconciliationService, AppLayout (+15 more)

### Community 9 - "CityDistance"
Cohesion: 0.06
Nodes (16): CityDistanceManager, LoginForm, self, CityDistance, self, self, UserFactory, Illuminate\Auth\Events\Lockout (+8 more)

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider.php"
Cohesion: 0.18
Nodes (6): App\Observers\PackageObserver, PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\Facades\Log, Illuminate\Support\ServiceProvider

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "AuditLog"
Cohesion: 0.18
Nodes (5): AuditLog, DriverPaymentService, Package, DriverPayment, AllyFinancialSettlementTest

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
Nodes (10): AlliesManager, OfficeLocator, Ally, AllyUser, Driver, AllyUserService, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User (+2 more)

### Community 26 - "BcvRate"
Cohesion: 0.06
Nodes (8): BcvRateManager, RateMatrixManager, BcvRate, RateMatrix, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, TariffServiceTest

### Community 27 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.12
Nodes (4): AllySettlement, DriverPayment, PackageHistory, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 71 - "Incident"
Cohesion: 0.11
Nodes (5): IncidentsManager, Dashboard, Package, Incident, IncidentService

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

### Community 93 - "App\Models\Package"
Cohesion: 0.08
Nodes (22): PackageLabelController, CreatePackage, PackageDetail, Package, App\Models\Driver, App\Models\Package, App\Models\PackageHistory, Package (+14 more)

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "RuntimeException"
Cohesion: 0.16
Nodes (8): App\Livewire\Admin\DriverAssignment, App\Livewire\Ally\PackageReception, DeliveryAssignmentService, DestinationReceptionService, HubReceptionService, PackageDispatchService, Illuminate\Support\Facades\DB, RuntimeException

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 111 - "TariffService"
Cohesion: 0.06
Nodes (18): CheckProductionReadiness, SyncBcvRate, App\Models\BcvRate, App\Models\RateMatrix, BcvRateService, DistanceApiService, App\Services\TariffService, TariffService (+10 more)

### Community 113 - "RouteService"
Cohesion: 0.05
Nodes (11): RoutesManager, App\Models\Route, Route, RouteStop, Driver, RouteService, VenezuelaLocationService, Illuminate\Database\Eloquent\Collection (+3 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.12
Nodes (16): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+8 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 121 - "Illuminate\Foundation\Testing\RefreshDatabase"
Cohesion: 0.40
Nodes (5): Illuminate\Foundation\Testing\RefreshDatabase, InvalidArgumentException, Tests\Feature\Concerns\CreatesTestPackages, PublicTrackingIncidentTest, Tests\TestCase

### Community 125 - "PackageStatusUpdated"
Cohesion: 0.32
Nodes (4): App\Notifications\PackageStatusUpdated, PackageStatusUpdated, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 126 - "Livewire\Component"
Cohesion: 0.20
Nodes (24): App\Livewire\Admin\AlliesManager, App\Livewire\Admin\AllyFinance, App\Livewire\Admin\AuditLogViewer, App\Livewire\Admin\BcvRateManager, App\Livewire\Admin\CityDistanceManager, Dashboard, App\Livewire\Admin\DriverPayments, App\Livewire\Admin\IncidentsManager (+16 more)

### Community 128 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.20
Nodes (12): App\Models\Ally, App\Models\AuditLog, App\Models\CityDistance, App\Models\Customer, App\Models\DriverPayment, App\Models\Incident, App\Models\RouteStop, App\Models\User (+4 more)

### Community 134 - "require"
Cohesion: 0.22
Nodes (9): require, barryvdh/laravel-dompdf, ext-bcmath, laravel/framework, laravel/tinker, livewire/livewire, livewire/volt, php (+1 more)

### Community 135 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.07
Nodes (8): DriverAssignment, PackageDispatch, PackageReception, Cod, PackagePickup, PackageReception, Package, DateTimeInterface

### Community 142 - "Commissions.php"
Cohesion: 0.21
Nodes (4): Commissions, Dashboard, Carbon, Illuminate\Support\Carbon

### Community 144 - "Illuminate\Support\Facades\Hash"
Cohesion: 0.22
Nodes (3): AllyStaffService, Illuminate\Support\Facades\Hash, PasswordUpdateTest

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 157 - "VerifyEmailController.php"
Cohesion: 0.43
Nodes (4): VerifyEmailController, Controller, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse

### Community 161 - "driver-payments.blade.php"
Cohesion: 0.50
Nodes (3): cancelPayment({{ $payment->id }}), markAsPaid({{ $payment->id }}), markPaid({{ $payment->id }})

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "TestCase"
Cohesion: 0.11
Nodes (8): Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Route, Livewire\Volt\Volt, PasswordConfirmationTest, RegistrationTest, ExampleTest, TestCase, UserAuthorizationTest

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 179 - "PasswordResetTest.php"
Cohesion: 0.25
Nodes (3): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, PasswordResetTest

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

### Community 183 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 188 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 189 - "dev"
Cohesion: 0.67
Nodes (3): dev, Composer\\Config::disableProcessTimeout, npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others

## Knowledge Gaps
- **214 isolated node(s):** `API / Backend`, `Architecture Navigation`, `Authentication and Authorization`, `Changes`, `Database` (+209 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **37 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Foundation\Testing\RefreshDatabase`, `.route`, `UsersManager`, `CityDistance`, `TestCase`, `Illuminate\Support\Facades\Hash`, `PasswordResetTest.php`, `EmailVerificationTest.php`, `AllyFinancialServiceTest`, `Ally`, `BcvRate`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `ProfileTest`, `Livewire\Component`?**
  _High betweenness centrality (0.081) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Foundation\Testing\RefreshDatabase`, `RuntimeException`, `Packages`, `Incident`, `PaymentReconciliationService`, `AppServiceProvider.php`, `Commissions.php`, `RouteService`, `Scanner`, `PackageStatusUpdated`, `Ally`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `App\Models\Package`, `Livewire\Component`, `Client/Dashboard.php`?**
  _High betweenness centrality (0.075) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `AllyFinancialService`, `UsersManager`, `Incident`, `PaymentOrder`, `User`, `Illuminate\Support\Facades\Hash`, `AllyFinance`, `RouteService`, `Illuminate\Foundation\Testing\RefreshDatabase`, `BcvRate`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `App\Models\Package`, `Livewire\Component`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **Are the 6 inferred relationships involving `Ally` (e.g. with `.createAdjustment()` and `.createSettlement()`) actually correct?**
  _`Ally` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `API / Backend`, `Architecture Navigation`, `Authentication and Authorization` to the rest of the system?**
  _214 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AllyFinancialService` be split into smaller, more focused modules?**
  _Cohesion score 0.1164021164021164 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._