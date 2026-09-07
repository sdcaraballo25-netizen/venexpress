# Graph Report - venexpress  (2026-09-06)

## Corpus Check
- 296 files · ~269,471 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1443 nodes · 2701 edges · 224 communities (180 shown, 44 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 28 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2bb5288e`
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
- Illuminate\View\View
- static
- Venexpress — Project Rules
- AppServiceProvider.php
- DatabaseSeeder.php
- logging.php
- Dashboard
- graphify reference: extra exports and benchmark
- README.md
- Illuminate\Http\Request
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- Illuminate\Database\Eloquent\Relations\HasMany
- BcvRate
- DriverPayment
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
- Illuminate\Database\Schema\Blueprint
- Ally
- User
- PackageService
- EmailVerificationTest.php
- require-dev
- PackageCreate
- RuntimeException
- Illuminate\Console\Command
- CityDistance
- UsersManager
- ally-finance.blade.php
- Controller
- Packages
- Money
- web.php
- Route
- routes-manager.blade.php
- package-create.blade.php
- AuditLog
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\Database\Migrations\Migration
- PaymentOrders
- PackageStatusUpdated
- Livewire\Component
- Packages
- AllyStaffService
- Ally/dashboard.blade.php
- require
- setup
- PendingPayments
- Driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- Dashboard
- PackageServiceCodTest
- AllyUser
- TariffServiceTest
- package-detail.blade.php
- RateMatrixManager
- CreatePackage.php
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- OfficeLocator
- TestCase
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
- bootstrap/app.php
- PackageLabelController.php
- PaymentReconciliationService
- PackageDetail
- TariffService
- resend

## God Nodes (most connected - your core abstractions)
1. `Package` - 152 edges
2. `User` - 115 edges
3. `Ally` - 55 edges
4. `AuditLog` - 53 edges
5. `Route` - 51 edges
6. `AllyFinancialService` - 35 edges
7. `PackageService` - 32 edges
8. `TestCase` - 32 edges
9. `Incident` - 31 edges
10. `PaymentOrder` - 29 edges

## Surprising Connections (you probably didn't know these)
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `createAlly()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/User.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php

## Import Cycles
- None detected.

## Communities (224 total, 44 thin omitted)

### Community 2 - "AllyFinancialService"
Cohesion: 0.06
Nodes (5): AllyFinance, AllyFinancialTransaction, AllySettlement, AllyFinancialService, AllyFinancialServiceTest

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
Cohesion: 0.18
Nodes (7): TrackingController, Commissions, AppLayout, GuestLayout, Carbon, Illuminate\View\Component, Illuminate\View\View

### Community 9 - "static"
Cohesion: 0.09
Nodes (13): LoginForm, self, self, UserFactory, Illuminate\Auth\Events\Lockout, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Str (+5 more)

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider.php"
Cohesion: 0.18
Nodes (5): PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\Facades\Log, Illuminate\Support\ServiceProvider

### Community 12 - "DatabaseSeeder.php"
Cohesion: 0.33
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 17 - "Illuminate\Http\Request"
Cohesion: 0.26
Nodes (7): DriverScanController, EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Http\JsonResponse, Illuminate\Http\Request, Symfony\Component\HttpFoundation\Response

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
Cohesion: 0.16
Nodes (4): BcvRateManager, BcvRate, BcvRateService, Carbon\Carbon

### Community 27 - "DriverPayment"
Cohesion: 0.17
Nodes (3): DriverPayments, DriverPayment, DriverPaymentService

### Community 71 - "Incident"
Cohesion: 0.17
Nodes (3): Incidents, Incident, IncidentService

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
Nodes (3): User, Illuminate\Database\Eloquent\Relations\HasOne, ProfileTest

### Community 93 - "PackageService"
Cohesion: 0.13
Nodes (3): Driver, LogisticsScanService, PackageService

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "RuntimeException"
Cohesion: 0.15
Nodes (7): PackageHistory, DestinationReceptionService, HubReceptionService, PackageDispatchService, Illuminate\Database\QueryException, Illuminate\Support\Facades\DB, RuntimeException

### Community 99 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 100 - "CityDistance"
Cohesion: 0.17
Nodes (3): CityDistanceManager, CityDistance, self

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 105 - "Controller"
Cohesion: 0.43
Nodes (4): VerifyEmailController, Controller, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse

### Community 111 - "Money"
Cohesion: 0.15
Nodes (4): Money, PHPUnit\Framework\TestCase, ExampleTest, MoneyTest

### Community 112 - "web.php"
Cohesion: 0.12
Nodes (5): AuditLogViewer, IncidentsManager, RoutesDashboard, Cod, Illuminate\Support\Facades\Route

### Community 113 - "Route"
Cohesion: 0.05
Nodes (8): RoutesManager, Dashboard, Route, RouteStop, RouteService, VenezuelaLocationService, Illuminate\Database\Eloquent\Collection, Illuminate\Support\Facades\File

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 120 - "AuditLog"
Cohesion: 0.12
Nodes (7): AuditLog, Customer, Livewire\Livewire, AllyFinancialSettlementTest, ClientDashboardTest, createAlly(), createPackage()

### Community 121 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.18
Nodes (3): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 125 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (4): PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 126 - "Livewire\Component"
Cohesion: 0.15
Nodes (11): Dashboard, DailyCashCut, Incidents, Illuminate\Support\Carbon, Illuminate\Support\Facades\Auth, Illuminate\Validation\Rule, Illuminate\Validation\Rules, Livewire\Attributes\Layout (+3 more)

### Community 134 - "require"
Cohesion: 0.20
Nodes (10): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/tinker, livewire/livewire, livewire/volt (+2 more)

### Community 135 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.06
Nodes (9): DriverAssignment, PackageDispatch, PackageReception, PackagePickup, PackageReception, PublicTracking, Package, DeliveryAssignmentService (+1 more)

### Community 144 - "AllyUser"
Cohesion: 0.20
Nodes (5): AllyUser, AllyUserService, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "TestCase"
Cohesion: 0.09
Nodes (12): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest (+4 more)

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

### Community 204 - "bootstrap/app.php"
Cohesion: 0.33
Nodes (4): Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware

### Community 205 - "PackageLabelController.php"
Cohesion: 0.28
Nodes (6): PackageLabelController, Barryvdh\DomPDF\Facade\Pdf, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Writer\SvgWriter, Illuminate\Http\Response, Picqer\Barcode\BarcodeGeneratorSVG

### Community 206 - "PaymentReconciliationService"
Cohesion: 0.27
Nodes (4): PaymentWebhookController, PaymentReconciliationService, Illuminate\Support\Facades\Validator, Throwable

### Community 211 - "TariffService"
Cohesion: 0.24
Nodes (3): RateMatrix, TariffService, InvalidArgumentException

## Knowledge Gaps
- **217 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+212 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **44 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `AllyFinancialService`, `Illuminate\View\View`, `PendingPayments`, `AppServiceProvider.php`, `Dashboard`, `Dashboard`, `PackageServiceCodTest`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Relations\HasMany`, `DriverPayment`, `CreatePackage.php`, `Incident`, `PackageLabelController.php`, `PaymentReconciliationService`, `PackageDetail`, `TariffService`, `Scanner`, `PackageService`, `RuntimeException`, `Packages`, `web.php`, `Route`, `AuditLog`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `PackageStatusUpdated`, `Livewire\Component`, `Packages`?**
  _High betweenness centrality (0.137) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `.route`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AllyStaffService`, `AllyFinancialService`, `UsersManager`, `static`, `Ally`, `DatabaseSeeder.php`, `TestCase`, `AllyUser`, `PackageServiceCodTest`, `PasswordResetTest`, `EmailVerificationTest.php`, `AuditLog`, `Illuminate\Database\Eloquent\Relations\HasMany`, `DriverPayment`, `Livewire\Component`?**
  _High betweenness centrality (0.082) - this node is a cross-community bridge._
- **Why does `Route` connect `Route` to `RuntimeException`, `Package`, `web.php`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `PackageService`, `Livewire\Component`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _217 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AllyFinancialService` be split into smaller, more focused modules?**
  _Cohesion score 0.057912457912457915 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._