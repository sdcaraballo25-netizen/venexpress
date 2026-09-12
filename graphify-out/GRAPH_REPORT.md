# Graph Report - venexpress  (2026-09-12)

## Corpus Check
- 324 files · ~278,105 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1592 nodes · 3064 edges · 239 communities (190 shown, 49 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 59 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0ef44fcc`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- .isAdmin
- AuditLog
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Incident
- PaymentOrder
- Venexpress — Project Rules
- App\Http\Controllers\Controller
- Illuminate\Database\Eloquent\Relations\BelongsTo
- logging.php
- Money
- graphify reference: extra exports and benchmark
- README.md
- Illuminate\Http\Request
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- Ally
- BcvRate
- DriverPayment
- Tests\Feature\Concerns\CreatesTestPackages
- Route
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
- bootstrap/app.php
- User
- PackageService
- EmailVerificationTest.php
- require-dev
- PackageCreate
- Livewire\Component
- CityDistance
- UsersManager
- ally-finance.blade.php
- PackageStatusUpdated
- TariffService
- IncidentsManager
- Illuminate\Support\Str
- routes-manager.blade.php
- App\Models\User
- package-create.blade.php
- AllyFinance
- Illuminate\Database\Eloquent\Factories\HasFactory
- Illuminate\Database\Schema\Blueprint
- Illuminate\Http\Resources\Json\JsonResource
- AuditLogViewer
- DriverRemunerationRate
- LoginForm.php
- Ally/dashboard.blade.php
- require
- setup
- PackageDispatch
- Driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- Illuminate\View\View
- PackageServiceCodTest
- TariffServiceTest
- App\Models\DriverPayment
- Illuminate\Database\Migrations\Migration
- RateMatrixManager
- PackageLabelController.php
- package-detail.blade.php
- DriverApiFlowTest
- DriverIncidentController.php
- BcvRateService
- DistanceApiService
- DriverDeliveryController
- Checklist final antes de operar en real — Venexpress
- App\Http\Controllers\TrackingController
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- Checklist de infraestructura para producción
- driver-assignment.blade.php
- Packages
- Illuminate\Foundation\Testing\RefreshDatabase
- config
- Dashboard
- Controller
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PriceCalculator
- price-calculator.blade.php
- keywords
- sanctum.php
- incidents-manager.blade.php
- Ally/Dashboard.php
- Illuminate\Support\Facades\Schema
- post-create-project-cmd
- PackagePickup.php
- RuntimeException
- DriverRouteController
- Packages
- resend
- PendingPayments
- Package

## God Nodes (most connected - your core abstractions)
1. `Package` - 164 edges
2. `User` - 123 edges
3. `AuditLog` - 51 edges
4. `Ally` - 49 edges
5. `AllyFinancialService` - 35 edges
6. `PackageService` - 33 edges
7. `TestCase` - 30 edges
8. `RouteService` - 29 edges
9. `Route` - 29 edges
10. `PaymentOrder` - 29 edges

## Surprising Connections (you probably didn't know these)
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php

## Import Cycles
- None detected.

## Communities (239 total, 49 thin omitted)

### Community 2 - "AuditLog"
Cohesion: 0.09
Nodes (5): DailyCashCut, AllyFinancialTransaction, AuditLog, AllyFinancialService, AllyFinancialSettlementTest

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
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 8 - "Incident"
Cohesion: 0.08
Nodes (8): Dashboard, Incidents, VerifyAccount, Customer, Incident, Livewire\Livewire, ClientDashboardTest, PublicTrackingIncidentTest

### Community 9 - "PaymentOrder"
Cohesion: 0.06
Nodes (14): App\Http\Controllers\PaymentWebhookController, PaymentWebhookController, PaymentOrders, PaymentOrder, PackageObserver, AppServiceProvider, VoltServiceProvider, PaymentReconciliationService (+6 more)

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "App\Http\Controllers\Controller"
Cohesion: 0.19
Nodes (8): App\Http\Controllers\Api\DriverDashboardController, DriverDashboardController, App\Http\Controllers\Api\DriverPackageController, App\Http\Controllers\Controller, App\Http\Resources\DriverPackageResource, DriverPackageResource, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\Storage

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "Money"
Cohesion: 0.15
Nodes (5): App\Support\Money, Money, PHPUnit\Framework\TestCase, ExampleTest, MoneyTest

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 17 - "Illuminate\Http\Request"
Cohesion: 0.25
Nodes (6): App\Http\Controllers\Api\DriverAuthController, DriverAuthController, DriverPackageController, DriverScanController, Illuminate\Http\JsonResponse, Illuminate\Http\Request

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
Cohesion: 0.07
Nodes (8): AlliesManager, OfficeLocator, Ally, Driver, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Database\Seeder

### Community 26 - "BcvRate"
Cohesion: 0.22
Nodes (3): BcvRateManager, BcvRate, self

### Community 71 - "Tests\Feature\Concerns\CreatesTestPackages"
Cohesion: 0.10
Nodes (5): AllyFinancialServiceTest, Tests\Feature\Concerns\CreatesTestPackages, createAlly(), createPackage(), PackageSecurityHashTest

### Community 72 - "Route"
Cohesion: 0.05
Nodes (12): RoutesManager, App\Models\Route, Route, App\Models\RouteStop, RouteStop, Route, RouteService, VenezuelaLocationService (+4 more)

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

### Community 91 - "bootstrap/app.php"
Cohesion: 0.21
Nodes (10): App\Http\Middleware\EnsureAccountIsVerified, EnsureAccountIsVerified, App\Http\Middleware\EnsureUserHasRole, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions (+2 more)

### Community 92 - "User"
Cohesion: 0.08
Nodes (5): User, AllyStaffService, AuthenticationTest, ProfileTest, UserAuthorizationTest

### Community 93 - "PackageService"
Cohesion: 0.14
Nodes (7): CreatePackage, App\Models\Driver, LogisticsScanService, PackageService, Driver, PackageHistory, TariffService

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "Livewire\Component"
Cohesion: 0.16
Nodes (26): App\Livewire\Admin\AlliesManager, App\Livewire\Admin\AllyFinance, App\Livewire\Admin\AuditLogViewer, App\Livewire\Admin\BcvRateManager, App\Livewire\Admin\CityDistanceManager, Dashboard, App\Livewire\Admin\DriverPayments, App\Livewire\Admin\IncidentsManager (+18 more)

### Community 100 - "CityDistance"
Cohesion: 0.15
Nodes (4): CityDistanceManager, CityDistance, self, static

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 107 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (5): App\Notifications\PackageStatusUpdated, PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 111 - "TariffService"
Cohesion: 0.20
Nodes (4): self, RateMatrix, TariffService, InvalidArgumentException

### Community 113 - "Illuminate\Support\Str"
Cohesion: 0.20
Nodes (5): DriverFactory, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, Pdo\Mysql

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

### Community 116 - "App\Models\User"
Cohesion: 0.17
Nodes (7): AllyUser, App\Models\User, AllyUserService, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash, Laravel\Sanctum\HasApiTokens

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 121 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.15
Nodes (12): App\Models\Ally, App\Models\AuditLog, App\Models\Customer, App\Models\Incident, App\Models\PackageHistory, IncidentService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\Eloquent\Factories\HasFactory (+4 more)

### Community 125 - "Illuminate\Http\Resources\Json\JsonResource"
Cohesion: 0.25
Nodes (4): DriverPaymentResource, RouteResource, RouteStopResource, Illuminate\Http\Resources\Json\JsonResource

### Community 128 - "DriverRemunerationRate"
Cohesion: 0.24
Nodes (3): DriverRemunerationManager, DriverRemunerationRate, self

### Community 129 - "LoginForm.php"
Cohesion: 0.29
Nodes (6): LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\RateLimiter, Illuminate\Validation\ValidationException, Livewire\Attributes\Validate, Livewire\Form

### Community 134 - "require"
Cohesion: 0.18
Nodes (11): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+3 more)

### Community 135 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.06
Nodes (8): DriverAssignment, PackageReception, Cod, PackageReception, PackageDetail, PublicTracking, Package, DateTimeInterface

### Community 142 - "Illuminate\View\View"
Cohesion: 0.18
Nodes (7): TrackingController, Commissions, AppLayout, GuestLayout, Carbon, Illuminate\View\Component, Illuminate\View\View

### Community 145 - "App\Models\DriverPayment"
Cohesion: 0.44
Nodes (4): App\Models\DriverPayment, DriverPaymentService, Package, DriverPayment

### Community 150 - "PackageLabelController.php"
Cohesion: 0.28
Nodes (6): PackageLabelController, Barryvdh\DomPDF\Facade\Pdf, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Writer\SvgWriter, Illuminate\Http\Response, Picqer\Barcode\BarcodeGeneratorSVG

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 155 - "BcvRateService"
Cohesion: 0.20
Nodes (4): CheckProductionReadiness, SyncBcvRate, BcvRateService, Illuminate\Console\Command

### Community 157 - "DriverDeliveryController"
Cohesion: 0.43
Nodes (3): DriverDeliveryController, Driver, Request

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "App\Http\Controllers\TrackingController"
Cohesion: 0.29
Nodes (4): App\Http\Controllers\TrackingController, Carbon\Carbon, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "Illuminate\Foundation\Testing\RefreshDatabase"
Cohesion: 0.10
Nodes (13): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordResetTest, PasswordUpdateTest (+5 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 179 - "Controller"
Cohesion: 0.53
Nodes (4): VerifyEmailController, Controller, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

### Community 183 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 188 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 189 - "sanctum.php"
Cohesion: 0.40
Nodes (4): Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Foundation\Http\Middleware\ValidateCsrfToken, Laravel\Sanctum\Http\Middleware\AuthenticateSession, Laravel\Sanctum\Sanctum

### Community 198 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 209 - "RuntimeException"
Cohesion: 0.12
Nodes (11): App\Livewire\Admin\DriverAssignment, App\Livewire\Ally\PackageReception, PackageHistory, DeliveryAssignmentService, Route, DestinationReceptionService, HubReceptionService, App\Services\LogisticsScanService (+3 more)

## Knowledge Gaps
- **227 isolated node(s):** `API / Backend`, `Architecture Navigation`, `Authentication and Authorization`, `Changes`, `Database` (+222 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **49 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `AuditLog`, `PackageDispatch`, `Incident`, `PaymentOrder`, `App\Http\Controllers\Controller`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\View\View`, `PackageServiceCodTest`, `Illuminate\Http\Request`, `PackageLabelController.php`, `Ally`, `DriverIncidentController.php`, `DriverPayment`, `DriverDeliveryController`, `App\Http\Controllers\TrackingController`, `Packages`, `Dashboard`, `Ally/Dashboard.php`, `Tests\Feature\Concerns\CreatesTestPackages`, `Route`, `PackagePickup.php`, `RuntimeException`, `Scanner`, `Packages`, `PackageService`, `PendingPayments`, `Livewire\Component`, `PackageStatusUpdated`, `TariffService`, `Illuminate\Database\Eloquent\Factories\HasFactory`?**
  _High betweenness centrality (0.144) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `.isAdmin`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Livewire\Component`, `AuditLog`, `DriverApiFlowTest`, `UsersManager`, `Tests\Feature\Concerns\CreatesTestPackages`, `Incident`, `Illuminate\Foundation\Testing\RefreshDatabase`, `PackageServiceCodTest`, `Illuminate\Http\Request`, `Illuminate\Support\Str`, `App\Models\User`, `Ally`, `DriverPayment`, `EmailVerificationTest.php`?**
  _High betweenness centrality (0.116) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `Livewire\Component`, `AuditLog`, `UsersManager`, `Tests\Feature\Concerns\CreatesTestPackages`, `Route`, `PaymentOrder`, `App\Models\User`, `AllyFinance`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `DriverPayment`, `User`, `PackageService`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Are the 6 inferred relationships involving `Package` (e.g. with `.index()` and `.store()`) actually correct?**
  _`Package` has 6 INFERRED edges - model-reasoned connections that need verification._
- **Are the 5 inferred relationships involving `User` (e.g. with `.definition()` and `.test_new_users_can_register()`) actually correct?**
  _`User` has 5 INFERRED edges - model-reasoned connections that need verification._
- **What connects `API / Backend`, `Architecture Navigation`, `Authentication and Authorization` to the rest of the system?**
  _227 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AuditLog` be split into smaller, more focused modules?**
  _Cohesion score 0.0915915915915916 - nodes in this community are weakly interconnected._