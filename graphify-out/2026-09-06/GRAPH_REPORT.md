# Graph Report - venexpress  (2026-09-06)

## Corpus Check
- 296 files · ~265,509 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1448 nodes · 2675 edges · 224 communities (179 shown, 45 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 64 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `377f4f82`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AuthenticationTest
- Illuminate\Support\Facades\Schema
- AuditLog
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Illuminate\View\View
- LoginForm.php
- Venexpress — Project Rules
- AppServiceProvider.php
- logging.php
- Route
- graphify reference: extra exports and benchmark
- README.md
- bootstrap/app.php
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- Illuminate\Database\Eloquent\Relations\HasMany
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
- Ally
- User
- App\Models\Package
- EmailVerificationTest.php
- require-dev
- Illuminate\Database\Migrations\Migration
- PackageCreate
- Illuminate\Support\Facades\DB
- CityDistance
- UsersManager
- ally-finance.blade.php
- TariffService
- AuditLogViewer
- RouteService
- routes-manager.blade.php
- package-create.blade.php
- Tests\Feature\Concerns\CreatesTestPackages
- Illuminate\Database\Eloquent\Factories\HasFactory
- Illuminate\Database\Schema\Blueprint
- PaymentOrders
- .route
- Livewire\Attributes\Layout
- Livewire\Component
- App\Models\DriverPayment
- Ally/dashboard.blade.php
- require
- setup
- DriverPayments
- client/dashboard.blade.php
- Package
- Commissions.php
- PackageServiceCodTest
- AllyUser
- TariffServiceTest
- package-detail.blade.php
- RateMatrixManager
- CreatePackage.php
- CityDistanceManager
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- OfficeLocator
- Illuminate\Foundation\Testing\RefreshDatabase
- config
- DistanceApiService
- PasswordResetTest
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PriceCalculator
- price-calculator.blade.php
- keywords
- dev
- AllyFinancialTransaction
- Illuminate\Http\Request
- PackageLabelController.php
- PaymentReconciliationService
- Package
- PackageDispatch
- PackageDetail
- PublicTracking
- RateMatrix
- Ally
- UserFactory.php
- Cod
- resend

## God Nodes (most connected - your core abstractions)
1. `User` - 97 edges
2. `Package` - 83 edges
3. `Ally` - 50 edges
4. `AuditLog` - 45 edges
5. `AllyFinancialService` - 35 edges
6. `PackageService` - 30 edges
7. `RouteService` - 29 edges
8. `PaymentOrder` - 29 edges
9. `Incident` - 27 edges
10. `Route` - 27 edges

## Surprising Connections (you probably didn't know these)
- `createAlly()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/User.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php

## Import Cycles
- None detected.

## Communities (224 total, 45 thin omitted)

### Community 2 - "AuditLog"
Cohesion: 0.07
Nodes (8): AllySettlement, AllyFinance, DailyCashCut, AllyFinancialTransaction, AuditLog, AllyFinancialService, Package, AllyFinancialSettlementTest

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

### Community 8 - "Illuminate\View\View"
Cohesion: 0.24
Nodes (6): Package, TrackingController, AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 9 - "LoginForm.php"
Cohesion: 0.17
Nodes (8): LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Str, Illuminate\Validation\ValidationException, Livewire\Attributes\Validate, Livewire\Form, Pdo\Mysql

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider.php"
Cohesion: 0.18
Nodes (6): App\Observers\PackageObserver, PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\Facades\Log, Illuminate\Support\ServiceProvider

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "Route"
Cohesion: 0.12
Nodes (3): Route, RouteStop, DeliveryAssignmentService

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 17 - "bootstrap/app.php"
Cohesion: 0.26
Nodes (8): EnsureAccountIsVerified, App\Http\Middleware\EnsureUserHasRole, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Symfony\Component\HttpFoundation\Response

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 26 - "BcvRate"
Cohesion: 0.18
Nodes (3): BcvRateManager, BcvRate, self

### Community 27 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.11
Nodes (4): AllySettlement, DriverPayment, PackageHistory, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 71 - "Incident"
Cohesion: 0.10
Nodes (6): IncidentsManager, Dashboard, Incidents, Incident, IncidentService, Package

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
Cohesion: 0.10
Nodes (4): User, AllyStaffService, ProfileTest, UserAuthorizationTest

### Community 93 - "App\Models\Package"
Cohesion: 0.27
Nodes (6): App\Models\Driver, App\Models\Package, PackageService, Driver, Package, PackageHistory

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 97 - "PackageCreate"
Cohesion: 0.08
Nodes (9): PackageCreate, PendingPayments, Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, TariffService, ClientDashboardTest (+1 more)

### Community 98 - "Illuminate\Support\Facades\DB"
Cohesion: 0.14
Nodes (8): App\Models\PackageHistory, App\Models\RouteStop, HubReceptionService, Package, LogisticsScanService, Driver, Package, Illuminate\Support\Facades\DB

### Community 100 - "CityDistance"
Cohesion: 0.24
Nodes (3): CityDistance, self, static

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 111 - "TariffService"
Cohesion: 0.07
Nodes (15): CheckProductionReadiness, SyncBcvRate, App\Models\BcvRate, App\Models\RateMatrix, BcvRateService, TariffService, Money, BcvRate (+7 more)

### Community 113 - "RouteService"
Cohesion: 0.09
Nodes (9): RoutesManager, App\Models\Route, Driver, RouteService, VenezuelaLocationService, Illuminate\Database\Eloquent\Collection, Illuminate\Support\Facades\File, Route (+1 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.12
Nodes (16): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+8 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 120 - "Tests\Feature\Concerns\CreatesTestPackages"
Cohesion: 0.17
Nodes (4): AllyFinancialServiceTest, Tests\Feature\Concerns\CreatesTestPackages, createAlly(), createPackage()

### Community 121 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.16
Nodes (15): App\Http\Controllers\TrackingController, App\Models\Ally, App\Models\AllySettlement, App\Models\AuditLog, App\Models\CityDistance, App\Models\Customer, App\Services\PackageService, App\Services\TariffService (+7 more)

### Community 125 - ".route"
Cohesion: 0.10
Nodes (12): App\Http\Controllers\Auth\VerifyEmailController, VerifyEmailController, Controller, VerifyAccount, App\Notifications\PackageStatusUpdated, PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Foundation\Auth\EmailVerificationRequest (+4 more)

### Community 126 - "Livewire\Attributes\Layout"
Cohesion: 0.24
Nodes (21): App\Livewire\Admin\AlliesManager, App\Livewire\Admin\AllyFinance, App\Livewire\Admin\AuditLogViewer, App\Livewire\Admin\BcvRateManager, App\Livewire\Admin\CityDistanceManager, App\Livewire\Admin\DriverAssignment, App\Livewire\Admin\DriverPayments, App\Livewire\Admin\IncidentsManager (+13 more)

### Community 127 - "Livewire\Component"
Cohesion: 0.15
Nodes (6): Dashboard, RoutesDashboard, Dashboard, Packages, Illuminate\Support\Facades\Auth, Livewire\Component

### Community 128 - "App\Models\DriverPayment"
Cohesion: 0.44
Nodes (4): App\Models\DriverPayment, DriverPaymentService, Package, DriverPayment

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
Nodes (8): DriverAssignment, PackageReception, PackagePickup, App\Livewire\Ally\PackageReception, PackageReception, Package, DestinationReceptionService, DateTimeInterface

### Community 142 - "Commissions.php"
Cohesion: 0.21
Nodes (4): Commissions, Dashboard, Carbon, Illuminate\Support\Carbon

### Community 144 - "AllyUser"
Cohesion: 0.23
Nodes (4): AllyUser, AllyUserService, Illuminate\Foundation\Auth\User, Illuminate\Support\Facades\Hash

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 157 - "CreatePackage.php"
Cohesion: 0.22
Nodes (4): CreatePackage, App\Livewire\Public\OfficeLocator, App\Livewire\Public\PriceCalculator, Livewire\Attributes\Computed

### Community 161 - "driver-payments.blade.php"
Cohesion: 0.50
Nodes (3): cancelPayment({{ $payment->id }}), markAsPaid({{ $payment->id }}), markPaid({{ $payment->id }})

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "Illuminate\Foundation\Testing\RefreshDatabase"
Cohesion: 0.12
Nodes (11): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Route, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest (+3 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 178 - "DistanceApiService"
Cohesion: 0.33
Nodes (3): DistanceApiService, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http

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

### Community 204 - "Illuminate\Http\Request"
Cohesion: 0.36
Nodes (4): DriverScanController, Illuminate\Foundation\Application, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 205 - "PackageLabelController.php"
Cohesion: 0.24
Nodes (7): PackageLabelController, Barryvdh\DomPDF\Facade\Pdf, Controller, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Writer\SvgWriter, Illuminate\Http\Response, Picqer\Barcode\BarcodeGeneratorSVG

### Community 206 - "PaymentReconciliationService"
Cohesion: 0.27
Nodes (5): App\Http\Controllers\PaymentWebhookController, PaymentWebhookController, PaymentReconciliationService, Illuminate\Support\Facades\Validator, Throwable

## Knowledge Gaps
- **215 isolated node(s):** `acceptDelivery({{ $package->id }})`, `startRejectDelivery({{ $package->id }})`, `cancelRejectDelivery`, `rejectDelivery`, `resend` (+210 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **45 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AuthenticationTest`, `PackageCreate`, `UsersManager`, `.isAdmin`, `Illuminate\Foundation\Testing\RefreshDatabase`, `AllyUser`, `PasswordResetTest`, `Ally`, `UserFactory.php`, `EmailVerificationTest.php`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Tests\Feature\Concerns\CreatesTestPackages`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `.route`, `Livewire\Attributes\Layout`, `Livewire\Component`?**
  _High betweenness centrality (0.105) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `Illuminate\Database\Eloquent\Factories\HasFactory`, `Incident`, `AppServiceProvider.php`, `Illuminate\Http\Request`, `Route`, `Commissions.php`, `PackageDispatch`, `Scanner`, `App\Models\Package`, `Cod`, `.route`, `Tests\Feature\Concerns\CreatesTestPackages`, `Illuminate\Database\Eloquent\Relations\HasMany`, `BcvRate`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `CreatePackage.php`, `Livewire\Attributes\Layout`, `Livewire\Component`?**
  _High betweenness centrality (0.085) - this node is a cross-community bridge._
- **Why does `Money` connect `TariffService` to `PaymentOrder`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `App\Models\Package`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **What connects `acceptDelivery({{ $package->id }})`, `startRejectDelivery({{ $package->id }})`, `cancelRejectDelivery` to the rest of the system?**
  _215 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AuditLog` be split into smaller, more focused modules?**
  _Cohesion score 0.06972789115646258 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._