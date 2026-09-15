# Graph Report - venexpress  (2026-09-15)

## Corpus Check
- 437 files · ~341,003 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2416 nodes · 5773 edges · 307 communities (224 shown, 83 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 54 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a366babf`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AuditLog
- RouteStop
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Incident
- setup
- Venexpress — Project Rules
- Illuminate\Database\Eloquent\Relations\BelongsTo
- AllyUser
- logging.php
- Money
- graphify reference: extra exports and benchmark
- README.md
- Ally
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- Illuminate\Http\Request
- users-manager.blade.php
- layout/navigation.blade.php
- DriverPayment
- StaffManager
- Warehouse
- Dashboard
- Route
- WarehouseCoverage
- DriversApprovalManager
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- VenezuelaLocationService
- copilot-instructions.md
- rate-matrix-manager.blade.php
- Actualización automática de tasa BCV
- DriverRouteReleaseTest
- User
- CityDistance
- RoutesManager
- require-dev
- PackageCreate
- Livewire\Component
- Recommendation
- HubDashboardUxTest
- AllyFinancialService
- Illuminate\Support\Facades\Schema
- ally-finance.blade.php
- Illuminate\Database\Migrations\Migration
- UsersManager
- WarehousesManager
- TariffService
- venexpress-ui/SKILL.md
- bootstrap/app.php
- PackageStatusUpdated
- routes-manager.blade.php
- DriverHubReceptionTest
- package-create.blade.php
- DriverDeliveryController
- ScannerLivewireTest
- Illuminate\Database\Schema\Blueprint
- GeocodePackageDeliveryAddress
- HubDistributionPhase
- RoutesManagerStopFilterTest
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- Controller
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Relations\HasMany
- driver/dashboard.blade.php
- PackageReception
- client/dashboard.blade.php
- Illuminate\Console\Command
- Package
- LogisticsResolutionService
- OfficeLocator
- TariffServiceTest
- BcvRate
- RateMatrixManager
- Customer
- package-detail.blade.php
- confirmOperation(
- DriverApiFlowTest
- DriverRouteController
- Almacen/Dashboard.php
- Scanner
- Checklist final antes de operar en real — Venexpress
- api.php
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
- TestCase
- config
- DriverRouteClaimTest
- Dashboard
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PackageLabelController.php
- price-calculator.blade.php
- PackageCreateFulfillmentModeTest
- sanctum.php
- incidents-manager.blade.php
- PackageReceptionPickupVerificationTest
- Driver
- PackageCreateTest
- PackageCreatePickupTest
- HubReleaseService
- Dashboard
- RoutesManagerListFilterTest
- OfficeLocatorLocationTest
- .route
- RecommendationFormTest
- Illuminate\Http\JsonResponse
- EmailVerificationTest.php
- PriceCalculator
- warehouses-manager.blade.php
- resend
- venexpress-laravel/SKILL.md
- DashboardClaimRouteTest
- PackagesVisibilityTest
- DriverRouteCompleteTest
- RoutesManagerMultistateTest
- DriverRouteActiveHubScanTest
- RouteDetailTest
- drivers-approval-manager.blade.php
- AlliesManagerDestinationVerificationTest
- Livewire\Volt\Volt
- DeliveryRouteOrderGeocodingTest
- PackageReceptionScannerTest
- PackageCreateDeliveryCoordinatesTest
- post-create-project-cmd
- PaymentOrder
- DriverHubDistributionTest
- VenezuelaLocationServiceCatalogTest
- DriverAssignment
- staff-manager.blade.php
- PackageCreateRegisteredByTest
- DriverHubDistributionController
- PaymentWebhookController.php
- archive({{ $recommendation->id }})
- assignToDriver({{ $route->id }})
- PriceCalculatorCatalogTest
- AlliesManagerLocationTest
- autoload-dev
- ExampleTest

## God Nodes (most connected - your core abstractions)
1. `Package` - 275 edges
2. `User` - 250 edges
3. `Route` - 191 edges
4. `TestCase` - 138 edges
5. `Warehouse` - 136 edges
6. `Driver` - 132 edges
7. `Ally` - 101 edges
8. `RouteStop` - 96 edges
9. `WarehouseCoverage` - 63 edges
10. `AuditLog` - 59 edges

## Surprising Connections (you probably didn't know these)
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php

## Import Cycles
- None detected.

## Communities (307 total, 83 thin omitted)

### Community 0 - "AuditLog"
Cohesion: 0.12
Nodes (8): AuditLog, PackageHistory, DeliveryAssignmentService, DriverPaymentService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\QueryException, Illuminate\Support\Facades\DB, RuntimeException

### Community 3 - "composer.json"
Cohesion: 0.14
Nodes (13): description, extra, laravel, keywords, dont-discover, license, minimum-stability, name (+5 more)

### Community 4 - "allies-manager.blade.php"
Cohesion: 0.22
Nodes (8): activate({{ $ally->id }}), approve({{ $ally->id }}), editLocation({{ $ally->id }}), reject({{ $ally->id }}), $set(, saveLocation, suspend({{ $ally->id }}), toggleVerifiedDestination({{ $ally->id }})

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
Nodes (6): IncidentsManager, Incidents, Incidents, PublicTracking, Incident, IncidentService

### Community 9 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.11
Nodes (17): Authentication and Authorization, Backend / API, Business Logic, Changes, Database, Frontend, General Development Rules, Generated Files (+9 more)

### Community 11 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.07
Nodes (4): DriverRemunerationRate, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 12 - "AllyUser"
Cohesion: 0.16
Nodes (6): AllyUser, AllyUserService, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "Money"
Cohesion: 0.15
Nodes (4): Money, PHPUnit\Framework\TestCase, ExampleTest, MoneyTest

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

### Community 22 - "Illuminate\Http\Request"
Cohesion: 0.16
Nodes (7): DriverAuthController, DriverPaymentResource, IncidentResource, RouteResource, RouteStopResource, Illuminate\Http\Request, Illuminate\Http\Resources\Json\JsonResource

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 26 - "StaffManager"
Cohesion: 0.05
Nodes (19): StaffManager, LoginForm, AllyStaffService, DistanceApiService, DriverFactory, UserFactory, WarehouseFactory, Illuminate\Auth\Events\Lockout (+11 more)

### Community 27 - "Warehouse"
Cohesion: 0.05
Nodes (9): Warehouse, HubReceptionService, PackageReceptionHubTest, RoutesManagerHubDistributionTest, WarehouseCoverageManagerTest, WarehousesManagerTest, WarehouseDispatchTest, HubReceptionServiceTest (+1 more)

### Community 72 - "Route"
Cohesion: 0.12
Nodes (3): Route, RouteService, Illuminate\Database\Eloquent\Collection

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
Cohesion: 0.05
Nodes (8): User, AllyFinancialServiceTest, AllyFinancialSettlementTest, AuthenticationTest, PasswordConfirmationTest, createAlly(), ProfileTest, UserAuthorizationTest

### Community 93 - "CityDistance"
Cohesion: 0.10
Nodes (7): CityDistanceManager, self, CityDistance, self, self, self, static

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "Livewire\Component"
Cohesion: 0.06
Nodes (23): AuditLogViewer, Dashboard, DriverRemunerationManager, HelpCenter, PaymentOrders, RoutesDashboard, DailyCashCut, HelpCenter (+15 more)

### Community 99 - "Recommendation"
Cohesion: 0.27
Nodes (3): RecommendationsManager, RecommendationForm, Recommendation

### Community 102 - "AllyFinancialService"
Cohesion: 0.07
Nodes (4): AllyFinance, AllyFinancialTransaction, AllySettlement, AllyFinancialService

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.29
Nodes (6): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 111 - "TariffService"
Cohesion: 0.24
Nodes (3): RateMatrix, TariffService, InvalidArgumentException

### Community 112 - "venexpress-ui/SKILL.md"
Cohesion: 0.17
Nodes (11): Avoid "AI Generated" Aesthetics, Buttons, Color Discipline, Core Design Philosophy, Layout, Professional Product Design, Spacing, Typography (+3 more)

### Community 113 - "bootstrap/app.php"
Cohesion: 0.18
Nodes (10): EnsureAccountIsApproved, EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+2 more)

### Community 114 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (4): PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.12
Nodes (15): cancelBuilder, cancelRoute({{ $route->id }}), clearFilters, duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}), openCollectionModal({{ $route->id }}, {{ $stop->id }}) (+7 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.20
Nodes (9): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer, selectAllyPickup, selectDelivery (+1 more)

### Community 125 - "GeocodePackageDeliveryAddress"
Cohesion: 0.10
Nodes (12): GeocodePackageDeliveryAddress, PackageObserver, AppServiceProvider, VoltServiceProvider, GeocodingService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable (+4 more)

### Community 130 - "Controller"
Cohesion: 0.29
Nodes (3): DriverDashboardController, Controller, DriverPackageResource

### Community 134 - "require"
Cohesion: 0.18
Nodes (11): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+3 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 137 - "driver/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): claimRoute({{ $route->id }}), completeRoute, releaseRoute, startRoute

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), clearHistoryFilters, showHistory, showPending

### Community 140 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 141 - "Package"
Cohesion: 0.05
Nodes (10): Cod, PackageDetail, PackagePickup, PackageReception, PackageDetail, Package, PackageService, DateTimeInterface (+2 more)

### Community 142 - "LogisticsResolutionService"
Cohesion: 0.07
Nodes (12): TrackingController, Commissions, SalesCloseout, LogisticsResolutionResult, self, LogisticsResolutionService, Collection, AppLayout (+4 more)

### Community 145 - "BcvRate"
Cohesion: 0.14
Nodes (4): BcvRateManager, BcvRate, BcvRateService, Carbon\Carbon

### Community 150 - "Customer"
Cohesion: 0.14
Nodes (6): Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, ClientDashboardTest, ClientPendingPaymentsTest

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 156 - "Almacen/Dashboard.php"
Cohesion: 0.14
Nodes (4): CreatePackage, Dashboard, DestinationReceptionService, Livewire\Attributes\Computed

### Community 157 - "Scanner"
Cohesion: 0.16
Nodes (3): FieldScanner, Scanner, LogisticsScanService

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "api.php"
Cohesion: 0.23
Nodes (5): DriverIncidentController, VerifyEmailController, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "TestCase"
Cohesion: 0.10
Nodes (10): Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Queue, Livewire\Livewire, DeliveryAssignmentServiceTest, PackageCreateCustomerHistoryTest (+2 more)

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
Nodes (6): PackageLabelController, Barryvdh\DomPDF\Facade\Pdf, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Writer\SvgWriter, Illuminate\Http\Response, Picqer\Barcode\BarcodeGeneratorSVG

### Community 189 - "sanctum.php"
Cohesion: 0.40
Nodes (4): Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Foundation\Http\Middleware\ValidateCsrfToken, Laravel\Sanctum\Http\Middleware\AuthenticateSession, Laravel\Sanctum\Sanctum

### Community 194 - "Driver"
Cohesion: 0.13
Nodes (5): Driver, UsersManagerDriverTypeTest, DriverDashboardTest, DriverHubTransferApiTest, DriverRouteCompletePendingPackagesTest

### Community 199 - "HubReleaseService"
Cohesion: 0.17
Nodes (3): PackageDispatch, HubReleaseService, PackageDispatchService

### Community 209 - ".route"
Cohesion: 0.07
Nodes (7): VerifyAccount, WarehouseStaffRoleTest, SalesCloseoutTest, StaffManagerTest, RegistrationTest, AppDownloadAccessTest, PublicTrackingHubDestinationLabelTest

### Community 211 - "Illuminate\Http\JsonResponse"
Cohesion: 0.28
Nodes (4): DriverPackageController, DriverScanController, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Storage

### Community 212 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 217 - "warehouses-manager.blade.php"
Cohesion: 0.25
Nodes (7): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, startCreating, toggleActive({{ $warehouse->id }}), toggleCoverageActive({{ $coverage->id }}), toggleCoveragePanel({{ $warehouse->id }})

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 244 - "drivers-approval-manager.blade.php"
Cohesion: 0.40
Nodes (4): activate({{ $driver->id }}), approve({{ $driver->id }}), reject({{ $driver->id }}), suspend({{ $driver->id }})

### Community 251 - "Livewire\Volt\Volt"
Cohesion: 0.14
Nodes (6): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, PasswordResetTest, PasswordUpdateTest

### Community 257 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 258 - "PaymentOrder"
Cohesion: 0.12
Nodes (3): PaymentOrder, PaymentReconciliationService, PaymentService

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancel, edit({{ $member->id }}), startCreate, toggleActive({{ $member->id }})

### Community 276 - "PaymentWebhookController.php"
Cohesion: 0.40
Nodes (3): PaymentWebhookController, Illuminate\Support\Facades\Validator, Throwable

### Community 303 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

## Knowledge Gaps
- **274 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+269 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **83 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `AuditLog`, `PackageDetailCodPaymentMethodTest`, `Controller`, `PaymentOrder`, `Incident`, `DriverAssignment`, `PackageReception`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Database\Eloquent\Relations\HasMany`, `PackageCreateRegisteredByTest`, `LogisticsResolutionService`, `DriverHubDistributionController`, `BcvRate`, `Illuminate\Http\Request`, `DriverPayment`, `StaffManager`, `Warehouse`, `Almacen/Dashboard.php`, `Scanner`, `api.php`, `Packages`, `TestCase`, `Dashboard`, `PackageLabelController.php`, `Driver`, `HubReleaseService`, `Route`, `Dashboard`, `Dashboard`, `WarehouseCoverage`, `Illuminate\Http\JsonResponse`, `PriceCalculator`, `PackageCreate`, `Livewire\Component`, `AllyFinancialService`, `TariffService`, `PackageStatusUpdated`, `DriverDeliveryController`, `GeocodePackageDeliveryAddress`, `HubDistributionPhase`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.162) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `AuditLog`, `DeliveryClaimFromDestinationAgencyTest`, `PackageDetailCodPaymentMethodTest`, `DriverHubDistributionTest`, `RouteStop`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Incident`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AllyUser`, `Package`, `Illuminate\Http\Request`, `Customer`, `DriverPayment`, `StaffManager`, `Warehouse`, `DriverApiFlowTest`, `AlliesManagerLocationTest`, `TestCase`, `DriverRouteClaimTest`, `Driver`, `RoutesManagerListFilterTest`, `OfficeLocatorLocationTest`, `.route`, `RecommendationFormTest`, `EmailVerificationTest.php`, `DriverRouteReleaseTest`, `Livewire\Component`, `DashboardClaimRouteTest`, `HubDashboardUxTest`, `DriverRouteCompleteTest`, `UsersManager`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `RouteDetailTest`, `DriverHubReceptionTest`, `ScannerLivewireTest`, `AlliesManagerDestinationVerificationTest`, `Livewire\Volt\Volt`, `DeliveryRouteOrderGeocodingTest`, `PackageReceptionScannerTest`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.083) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `AuditLog`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Incident`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AllyUser`, `Package`, `LogisticsResolutionService`, `OfficeLocator`, `Customer`, `DriverPayment`, `StaffManager`, `Warehouse`, `Almacen/Dashboard.php`, `TestCase`, `DriverRouteClaimTest`, `PackageReceptionPickupVerificationTest`, `Driver`, `HubReleaseService`, `Route`, `OfficeLocatorLocationTest`, `.route`, `DriverRouteReleaseTest`, `User`, `RoutesManager`, `PackageCreate`, `Livewire\Component`, `DashboardClaimRouteTest`, `AllyFinancialService`, `DriverRouteCompleteTest`, `UsersManager`, `bootstrap/app.php`, `DriverHubReceptionTest`, `Livewire\Volt\Volt`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _274 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AuditLog` be split into smaller, more focused modules?**
  _Cohesion score 0.12121212121212122 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._