# Graph Report - venexpress  (2026-09-14)

## Corpus Check
- 374 files · ~310,024 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1976 nodes · 4427 edges · 262 communities (208 shown, 54 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 63 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `dc33d377`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- RoutesManager
- ScannerHubOperationUxTest
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Incident
- setup
- Venexpress — Project Rules
- VenezuelaLocationService
- ScannerLivewireTest
- logging.php
- Money
- graphify reference: extra exports and benchmark
- README.md
- Ally
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- DriverRouteController.php
- users-manager.blade.php
- layout/navigation.blade.php
- DriverPayment
- App\Models\Ally
- App\Http\Controllers\Api\DriverIncidentController
- Dashboard
- RouteService
- CreatePackage.php
- Packages
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- RuntimeException
- copilot-instructions.md
- rate-matrix-manager.blade.php
- Actualización automática de tasa BCV
- Route
- User
- CityDistance
- DatabaseSeeder.php
- require-dev
- PackageCreate
- Livewire\Component
- RouteStop
- AllyFinancialService
- ally-finance.blade.php
- PackageStatusUpdated
- TariffService
- venexpress-ui/SKILL.md
- bootstrap/app.php
- PackageServiceCodTest
- routes-manager.blade.php
- static
- package-create.blade.php
- DriverDeliveryController.php
- PackageService
- Illuminate\Support\Facades\Schema
- Illuminate\Database\Migrations\Migration
- GeocodePackageDeliveryAddress
- Dashboard
- UsersManager
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Factories\HasFactory
- driver/dashboard.blade.php
- client/dashboard.blade.php
- Packages
- Package
- App\Http\Controllers\TrackingController
- keywords
- TariffServiceTest
- BcvRate
- Illuminate\Database\Schema\Blueprint
- RateMatrixManager
- Illuminate\Console\Command
- package-detail.blade.php
- confirmOperation(
- App\Models\Driver
- DriverRouteController
- Driver
- Checklist final antes de operar en real — Venexpress
- Controller
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- Checklist de infraestructura para producción
- driver-assignment.blade.php
- Illuminate\Foundation\Testing\RefreshDatabase
- config
- DriverHubDistributionController
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PackageLabelController.php
- price-calculator.blade.php
- Illuminate\Database\Eloquent\Relations\BelongsTo
- sanctum.php
- incidents-manager.blade.php
- HubDistributionPhase
- Commissions
- SalesCloseoutTest
- Illuminate\Http\Request
- PriceCalculator
- warehouses-manager.blade.php
- resend
- venexpress-laravel/SKILL.md
- PendingPayments
- PackagesVisibilityTest
- AuditLogViewer
- DriverRouteActiveHubScanTest
- DeliveryRouteOrderGeocodingTest
- PackageCreateDeliveryCoordinatesTest
- post-create-project-cmd
- PaymentOrder
- Warehouse
- AuditLog
- DriverAssignment
- staff-manager.blade.php
- PackageCreateRegisteredByTest
- StaffManagerTest

## God Nodes (most connected - your core abstractions)
1. `Package` - 237 edges
2. `User` - 209 edges
3. `Route` - 145 edges
4. `RouteStop` - 88 edges
5. `Driver` - 80 edges
6. `TestCase` - 62 edges
7. `Ally` - 61 edges
8. `AuditLog` - 55 edges
9. `PackageService` - 38 edges
10. `RouteService` - 38 edges

## Surprising Connections (you probably didn't know these)
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php

## Import Cycles
- None detected.

## Communities (262 total, 54 thin omitted)

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
Cohesion: 0.10
Nodes (4): IncidentsManager, Incidents, Incident, IncidentService

### Community 9 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.11
Nodes (17): Authentication and Authorization, Backend / API, Business Logic, Changes, Database, Frontend, General Development Rules, Generated Files (+9 more)

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "Money"
Cohesion: 0.16
Nodes (4): Money, PHPUnit\Framework\TestCase, ExampleTest, MoneyTest

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 17 - "Ally"
Cohesion: 0.05
Nodes (11): AlliesManager, OfficeLocator, Ally, AllyUser, AllyUserService, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable (+3 more)

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 22 - "DriverRouteController.php"
Cohesion: 0.16
Nodes (8): App\Http\Controllers\Api\DriverDashboardController, DriverDashboardController, DriverPackageResource, DriverPaymentResource, App\Http\Resources\RouteResource, RouteResource, RouteStopResource, Illuminate\Http\Resources\Json\JsonResource

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "DriverPayment"
Cohesion: 0.18
Nodes (3): DriverPayments, DriverPayment, DriverPaymentService

### Community 26 - "App\Models\Ally"
Cohesion: 0.06
Nodes (17): SalesCloseout, StaffManager, LoginForm, App\Models\Ally, AllyStaffService, DriverFactory, UserFactory, WarehouseFactory (+9 more)

### Community 27 - "App\Http\Controllers\Api\DriverIncidentController"
Cohesion: 0.36
Nodes (3): App\Http\Controllers\Api\DriverIncidentController, DriverIncidentController, IncidentResource

### Community 72 - "RouteService"
Cohesion: 0.16
Nodes (3): App\Services\RouteService, RouteService, Illuminate\Database\Eloquent\Collection

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

### Community 84 - "RuntimeException"
Cohesion: 0.11
Nodes (11): App\Livewire\Admin\PackageDispatch, App\Livewire\Ally\PackageReception, PackageHistory, DeliveryAssignmentService, Route, DestinationReceptionService, HubReceptionService, PackageDispatchService (+3 more)

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 91 - "Route"
Cohesion: 0.10
Nodes (5): Dashboard, Route, DashboardClaimRouteTest, DriverRouteCompleteTest, DriverRouteReleaseTest

### Community 92 - "User"
Cohesion: 0.04
Nodes (13): Ally, User, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, AllyFinancialServiceTest, AuthenticationTest, EmailVerificationTest (+5 more)

### Community 93 - "CityDistance"
Cohesion: 0.17
Nodes (3): CityDistanceManager, CityDistance, self

### Community 94 - "DatabaseSeeder.php"
Cohesion: 0.33
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "Livewire\Component"
Cohesion: 0.15
Nodes (32): App\Livewire\Admin\AlliesManager, App\Livewire\Admin\AllyFinance, App\Livewire\Admin\AuditLogViewer, App\Livewire\Admin\BcvRateManager, App\Livewire\Admin\CityDistanceManager, Dashboard, App\Livewire\Admin\DriverAssignment, App\Livewire\Admin\DriverPayments (+24 more)

### Community 100 - "RouteStop"
Cohesion: 0.15
Nodes (3): RouteStop, HubDashboardUxTest, RouteDetailTest

### Community 102 - "AllyFinancialService"
Cohesion: 0.09
Nodes (4): AllyFinance, DailyCashCut, AllyFinancialTransaction, AllyFinancialService

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.29
Nodes (6): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 107 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (5): App\Notifications\PackageStatusUpdated, PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 111 - "TariffService"
Cohesion: 0.24
Nodes (4): RateMatrix, App\Services\TariffService, TariffService, InvalidArgumentException

### Community 112 - "venexpress-ui/SKILL.md"
Cohesion: 0.17
Nodes (11): Avoid "AI Generated" Aesthetics, Buttons, Color Discipline, Core Design Philosophy, Layout, Professional Product Design, Spacing, Typography (+3 more)

### Community 113 - "bootstrap/app.php"
Cohesion: 0.21
Nodes (8): EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Symfony\Component\HttpFoundation\Response

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.13
Nodes (14): cancelBuilder, cancelRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}), openCollectionModal({{ $route->id }}, {{ $stop->id }}), registerCollection (+6 more)

### Community 116 - "static"
Cohesion: 0.20
Nodes (4): self, self, self, static

### Community 118 - "package-create.blade.php"
Cohesion: 0.25
Nodes (7): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer, $set(

### Community 120 - "DriverDeliveryController.php"
Cohesion: 0.36
Nodes (3): DriverDeliveryController, Driver, App\Http\Controllers\Controller

### Community 121 - "PackageService"
Cohesion: 0.21
Nodes (5): App\Models\PackageHistory, PackageService, Driver, TariffService, PackageHistory

### Community 125 - "GeocodePackageDeliveryAddress"
Cohesion: 0.07
Nodes (15): GeocodePackageDeliveryAddress, PackageObserver, AppServiceProvider, VoltServiceProvider, DistanceApiService, GeocodingService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue (+7 more)

### Community 134 - "require"
Cohesion: 0.18
Nodes (11): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+3 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 136 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.12
Nodes (14): DriverRemunerationManager, App\Models\AuditLog, App\Models\BcvRate, App\Models\CityDistance, App\Models\Customer, DriverRemunerationRate, App\Models\RateMatrix, App\Models\Warehouse (+6 more)

### Community 137 - "driver/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): claimRoute({{ $route->id }}), completeRoute, releaseRoute, startRoute

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.05
Nodes (11): PackageDispatch, PackageReception, Cod, PackageDetail, PackagePickup, PackageReception, PackageDetail, PublicTracking (+3 more)

### Community 142 - "App\Http\Controllers\TrackingController"
Cohesion: 0.26
Nodes (6): App\Http\Controllers\TrackingController, TrackingController, AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 143 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 145 - "BcvRate"
Cohesion: 0.15
Nodes (4): BcvRateManager, BcvRate, BcvRateService, Carbon\Carbon

### Community 150 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 153 - "App\Models\Driver"
Cohesion: 0.13
Nodes (8): App\Models\Driver, App\Models\Route, App\Models\RouteStop, Illuminate\Support\Facades\Queue, DeliveryAssignmentServiceTest, DriverApiFlowTest, DriverHubTransferApiTest, Tests\TestCase

### Community 155 - "DriverRouteController"
Cohesion: 0.40
Nodes (4): DriverRouteController, Driver, Route, RouteService

### Community 157 - "Driver"
Cohesion: 0.09
Nodes (8): FieldScanner, Scanner, Driver, App\Services\LogisticsScanService, LogisticsScanService, UsersManagerDriverTypeTest, DriverHubReceptionTest, DriverRouteCompletePendingPackagesTest

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "Controller"
Cohesion: 0.25
Nodes (7): App\Http\Controllers\Api\DriverHubDistributionController, VerifyEmailController, Controller, Illuminate\Auth\Events\Verified, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "Illuminate\Foundation\Testing\RefreshDatabase"
Cohesion: 0.11
Nodes (13): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Livewire\Livewire, Livewire\Volt\Volt, PasswordUpdateTest, RegistrationTest (+5 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

### Community 183 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 184 - "PackageLabelController.php"
Cohesion: 0.28
Nodes (7): App\Http\Controllers\PackageLabelController, PackageLabelController, Barryvdh\DomPDF\Facade\Pdf, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Writer\SvgWriter, Illuminate\Http\Response, Picqer\Barcode\BarcodeGeneratorSVG

### Community 189 - "sanctum.php"
Cohesion: 0.40
Nodes (4): Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Foundation\Http\Middleware\ValidateCsrfToken, Laravel\Sanctum\Http\Middleware\AuthenticateSession, Laravel\Sanctum\Sanctum

### Community 194 - "HubDistributionPhase"
Cohesion: 0.22
Nodes (3): RouteDetail, App\Livewire\Driver\Support\HubDistributionPhase, HubDistributionPhase

### Community 211 - "Illuminate\Http\Request"
Cohesion: 0.19
Nodes (11): App\Http\Controllers\Api\DriverAuthController, DriverAuthController, DriverPackageController, App\Http\Controllers\DriverScanController, DriverScanController, App\Http\Controllers\PaymentWebhookController, PaymentWebhookController, Illuminate\Http\JsonResponse (+3 more)

### Community 217 - "warehouses-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }})

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 257 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 258 - "PaymentOrder"
Cohesion: 0.10
Nodes (5): PaymentOrders, PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Contracts\View\View

### Community 259 - "Warehouse"
Cohesion: 0.15
Nodes (5): WarehousesManager, Warehouse, RoutesManagerHubDistributionTest, WarehousesManagerTest, DriverHubDistributionTest

### Community 262 - "AuditLog"
Cohesion: 0.18
Nodes (4): AuditLog, Customer, AllyFinancialSettlementTest, ClientDashboardTest

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancel, edit({{ $member->id }}), startCreate, toggleActive({{ $member->id }})

## Knowledge Gaps
- **260 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+255 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **54 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `PackageDetailCodPaymentMethodTest`, `PaymentOrder`, `AuditLog`, `Incident`, `DriverAssignment`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Packages`, `PackageCreateRegisteredByTest`, `App\Http\Controllers\TrackingController`, `BcvRate`, `Ally`, `DriverRouteController.php`, `DriverPayment`, `App\Models\Ally`, `App\Http\Controllers\Api\DriverIncidentController`, `App\Models\Driver`, `Driver`, `Controller`, `Illuminate\Foundation\Testing\RefreshDatabase`, `DriverHubDistributionController`, `PackageLabelController.php`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `HubDistributionPhase`, `Dashboard`, `RouteService`, `CreatePackage.php`, `Packages`, `Commissions`, `Illuminate\Http\Request`, `RuntimeException`, `Route`, `PendingPayments`, `Livewire\Component`, `AllyFinancialService`, `PackageStatusUpdated`, `TariffService`, `PackageServiceCodTest`, `DriverDeliveryController.php`, `PackageService`, `GeocodePackageDeliveryAddress`, `Dashboard`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.173) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `PackageDetailCodPaymentMethodTest`, `DeliveryClaimFromDestinationAgencyTest`, `ScannerHubOperationUxTest`, `Warehouse`, `AuditLog`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Incident`, `ScannerLivewireTest`, `StaffManagerTest`, `BcvRate`, `Ally`, `App\Models\Driver`, `App\Models\Ally`, `Driver`, `Illuminate\Foundation\Testing\RefreshDatabase`, `HubDistributionPhase`, `SalesCloseoutTest`, `Illuminate\Http\Request`, `RuntimeException`, `Route`, `DatabaseSeeder.php`, `Livewire\Component`, `RouteStop`, `DriverRouteActiveHubScanTest`, `PackageServiceCodTest`, `DeliveryRouteOrderGeocodingTest`, `UsersManager`?**
  _High betweenness centrality (0.113) - this node is a cross-community bridge._
- **Why does `Route` connect `Route` to `RoutesManager`, `Livewire\Component`, `HubDistributionPhase`, `RouteStop`, `Warehouse`, `ScannerHubOperationUxTest`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `DriverAssignment`, `RouteService`, `VenezuelaLocationService`, `ScannerLivewireTest`, `Illuminate\Foundation\Testing\RefreshDatabase`, `Ally`, `App\Models\Driver`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Driver`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Are the 5 inferred relationships involving `RouteStop` (e.g. with `.test_full_delivery_flow_through_the_real_api()` and `.test_hub_driver_completes_collection_and_hub_reception_through_the_real_api()`) actually correct?**
  _`RouteStop` has 5 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _260 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._