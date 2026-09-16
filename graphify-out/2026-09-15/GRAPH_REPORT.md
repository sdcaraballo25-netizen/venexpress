# Graph Report - venexpress  (2026-09-15)

## Corpus Check
- 446 files · ~345,811 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2497 nodes · 6002 edges · 317 communities (223 shown, 94 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 57 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `417839dc`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- RuntimeException
- WarehouseCoverage
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
- AuditLogViewer
- users-manager.blade.php
- layout/navigation.blade.php
- DriverPayment
- RoutesManager
- Warehouse
- AuditLogViewerTest
- Route
- DriverHubDistributionTest
- Illuminate\Database\Eloquent\Relations\HasMany
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- HubReleaseService
- copilot-instructions.md
- rate-matrix-manager.blade.php
- Actualización automática de tasa BCV
- DriverRouteReleaseTest
- User
- CityDistance
- WarehousesManager
- require-dev
- PackageCreate
- Livewire\Component
- VenezuelaLocationService
- HubDashboardUxTest
- audit-log-viewer.blade.php
- AuditLog
- Illuminate\Support\Facades\Schema
- ally-finance.blade.php
- Illuminate\Database\Migrations\Migration
- TariffService
- WarehouseCoverageManagerTest
- HubReleaseServiceTest
- venexpress-ui/SKILL.md
- bootstrap/app.php
- Dashboard
- routes-manager.blade.php
- BcvRate
- package-create.blade.php
- DriverDeliveryController
- RouteStop
- Illuminate\Database\Schema\Blueprint
- GeocodePackageDeliveryAddress
- WarehouseDispatchTest
- RoutesManagerStopFilterTest
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- ScannerLivewireTest
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Factories\HasFactory
- driver/dashboard.blade.php
- PackageReception
- client/dashboard.blade.php
- StaffManager
- Package
- LogisticsResolutionService
- OfficeLocator
- UsersManager
- LoginForm.php
- commissions.blade.php
- RateMatrixManager
- Customer
- package-detail.blade.php
- confirmOperation(
- DriverApiFlowTest.php
- PackageServiceCodTest
- EmailVerificationTest.php
- Scanner
- Checklist final antes de operar en real — Venexpress
- PackageStatusUpdated
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- Checklist de infraestructura para producción
- driver-assignment.blade.php
- PackageReceptionHubTest
- TestCase
- config
- DriverRouteClaimTest
- extra
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PackageLabelController.php
- price-calculator.blade.php
- PackageCreateFulfillmentModeTest
- sanctum.php
- incidents-manager.blade.php
- PackageReceptionPickupVerificationTest
- SimpleArrayExport
- Driver
- post-autoload-dump
- PackageCreatePickupTest
- api.php
- RoutesManagerListFilterTest
- OfficeLocatorLocationTest
- .route
- WarehousesManagerTest
- Illuminate\Http\JsonResponse
- UsersManagerSearchUrlTest
- PriceCalculator
- Illuminate\Http\Request
- resend
- venexpress-laravel/SKILL.md
- Controller
- DashboardClaimRouteTest
- PackagesVisibilityTest
- RecommendationFormTest
- DriverRouteCompleteTest
- warehouses-manager.blade.php
- RoutesManagerMultistateTest
- DriverRouteActiveHubScanTest
- AllyFinancialServiceTest
- DriverHubDistributionController
- drivers-approval-manager.blade.php
- DriverHubReceptionTest
- AlliesManagerDestinationVerificationTest
- DriverRouteController
- DeliveryRouteOrderGeocodingTest
- PackageReceptionScannerTest
- PackageCreateDeliveryCoordinatesTest
- RouteDetailTest
- PaymentOrder
- StaffManagerTest
- VenezuelaLocationServiceCatalogTest
- RegistrationTest.php
- staff-manager.blade.php
- PackageCreateRegisteredByTest
- ExampleTest
- SalesCloseoutTest
- Illuminate\Console\Command
- sales-closeout.blade.php
- AllyFinanceTest
- PackageService
- archive({{ $recommendation->id }})
- assignToDriver({{ $route->id }})
- PriceCalculatorCatalogTest
- DriverAssignmentRenderTest
- AlliesManagerLocationTest
- RoutesManagerHubDistributionTest
- PaymentWebhookController.php
- excel.php

## God Nodes (most connected - your core abstractions)
1. `Package` - 276 edges
2. `User` - 261 edges
3. `Route` - 191 edges
4. `TestCase` - 150 edges
5. `Warehouse` - 136 edges
6. `Driver` - 134 edges
7. `Ally` - 102 edges
8. `RouteStop` - 96 edges
9. `AuditLog` - 67 edges
10. `WarehouseCoverage` - 63 edges

## Surprising Connections (you probably didn't know these)
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php

## Import Cycles
- None detected.

## Communities (317 total, 94 thin omitted)

### Community 0 - "RuntimeException"
Cohesion: 0.14
Nodes (5): PackageHistory, DestinationReceptionService, HubReceptionService, Illuminate\Support\Facades\DB, RuntimeException

### Community 3 - "composer.json"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

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
Cohesion: 0.13
Nodes (15): scripts, dev, post-create-project-cmd, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::prePackageUninstall (+7 more)

### Community 8 - "Incident"
Cohesion: 0.09
Nodes (6): IncidentsManager, Incidents, Incidents, Incident, IncidentService, Illuminate\Auth\Access\AuthorizationException

### Community 9 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.11
Nodes (17): Authentication and Authorization, Backend / API, Business Logic, Changes, Database, Frontend, General Development Rules, Generated Files (+9 more)

### Community 12 - "AllyUser"
Cohesion: 0.15
Nodes (7): AllyUser, AllyUserService, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash, Laravel\Sanctum\HasApiTokens

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

### Community 17 - "Ally"
Cohesion: 0.15
Nodes (3): AlliesManager, Ally, createPackage()

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 22 - "AuditLogViewer"
Cohesion: 0.08
Nodes (10): AuditLogViewer, DriverPayments, Commissions, Dashboard, SalesCloseout, excelDownload(), Carbon, Illuminate\Database\Eloquent\Builder (+2 more)

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "DriverPayment"
Cohesion: 0.21
Nodes (3): DriverPayment, DriverPaymentService, DriverPaymentsTest

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

### Community 84 - "HubReleaseService"
Cohesion: 0.17
Nodes (3): PackageDispatch, HubReleaseService, PackageDispatchService

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 92 - "User"
Cohesion: 0.04
Nodes (13): User, DriverFactory, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, AllyFinancialSettlementTest, AuthenticationTest, PasswordConfirmationTest (+5 more)

### Community 93 - "CityDistance"
Cohesion: 0.10
Nodes (7): CityDistanceManager, self, CityDistance, self, self, self, static

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "Livewire\Component"
Cohesion: 0.05
Nodes (29): Dashboard, DriverRemunerationManager, HelpCenter, PaymentOrders, RecommendationsManager, RoutesDashboard, Cod, DailyCashCut (+21 more)

### Community 102 - "AuditLog"
Cohesion: 0.09
Nodes (4): AllyFinance, AllyFinancialTransaction, AuditLog, AllyFinancialService

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), exportExcel, $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 107 - "TariffService"
Cohesion: 0.11
Nodes (4): RateMatrix, TariffService, InvalidArgumentException, TariffServiceTest

### Community 112 - "venexpress-ui/SKILL.md"
Cohesion: 0.17
Nodes (11): Avoid "AI Generated" Aesthetics, Buttons, Color Discipline, Core Design Philosophy, Layout, Professional Product Design, Spacing, Typography (+3 more)

### Community 113 - "bootstrap/app.php"
Cohesion: 0.18
Nodes (10): EnsureAccountIsApproved, EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+2 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.12
Nodes (15): cancelBuilder, cancelRoute({{ $route->id }}), clearFilters, duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}), openCollectionModal({{ $route->id }}, {{ $stop->id }}) (+7 more)

### Community 116 - "BcvRate"
Cohesion: 0.11
Nodes (5): BcvRateManager, BcvRate, BcvRateService, Carbon\Carbon, PackageCreateTest

### Community 118 - "package-create.blade.php"
Cohesion: 0.20
Nodes (9): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer, selectAllyPickup, selectDelivery (+1 more)

### Community 125 - "GeocodePackageDeliveryAddress"
Cohesion: 0.10
Nodes (12): GeocodePackageDeliveryAddress, PackageObserver, AppServiceProvider, VoltServiceProvider, GeocodingService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable (+4 more)

### Community 134 - "require"
Cohesion: 0.17
Nodes (12): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+4 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 136 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.13
Nodes (3): DriverRemunerationRate, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model

### Community 137 - "driver/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): claimRoute({{ $route->id }}), completeRoute, releaseRoute, startRoute

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), clearHistoryFilters, showHistory, showPending

### Community 141 - "Package"
Cohesion: 0.05
Nodes (11): DriverAssignment, PackageDetail, PackagePickup, PackageReception, Dashboard, PackageDetail, PublicTracking, Package (+3 more)

### Community 142 - "LogisticsResolutionService"
Cohesion: 0.10
Nodes (9): TrackingController, LogisticsResolutionResult, self, LogisticsResolutionService, Collection, AppLayout, GuestLayout, Illuminate\View\Component (+1 more)

### Community 145 - "LoginForm.php"
Cohesion: 0.08
Nodes (14): LoginForm, DistanceApiService, UserFactory, WarehouseFactory, Illuminate\Auth\Events\Lockout, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Password (+6 more)

### Community 150 - "Customer"
Cohesion: 0.09
Nodes (7): Dashboard, Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, ClientDashboardTest, ClientPendingPaymentsTest

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 156 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 157 - "Scanner"
Cohesion: 0.12
Nodes (4): FieldScanner, Scanner, HubDistributionPhase, LogisticsScanService

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (4): PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 161 - "driver-payments.blade.php"
Cohesion: 0.50
Nodes (3): cancelPayment({{ $payment->id }}), markPaid({{ $payment->id }}), exportExcel

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "TestCase"
Cohesion: 0.10
Nodes (11): Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Queue, Livewire\Livewire, Maatwebsite\Excel\Facades\Excel, CommissionsTest (+3 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 179 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

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

### Community 193 - "SimpleArrayExport"
Cohesion: 0.23
Nodes (8): SimpleArrayExport, Generator, Maatwebsite\Excel\Concerns\FromGenerator, Maatwebsite\Excel\Concerns\ShouldAutoSize, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithStyles, PhpOffice\PhpSpreadsheet\Style\Fill, PhpOffice\PhpSpreadsheet\Worksheet\Worksheet

### Community 194 - "Driver"
Cohesion: 0.10
Nodes (7): Driver, DeliveryAssignmentServiceTest, UsersManagerDriverTypeTest, AppDownloadAccessTest, DriverDashboardTest, DriverHubTransferApiTest, DriverRouteCompletePendingPackagesTest

### Community 196 - "post-autoload-dump"
Cohesion: 0.67
Nodes (3): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan package:discover --ansi

### Community 199 - "api.php"
Cohesion: 0.23
Nodes (5): DriverIncidentController, VerifyEmailController, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 209 - ".route"
Cohesion: 0.17
Nodes (3): VerifyAccount, WarehouseStaffRoleTest, PublicTrackingHubDestinationLabelTest

### Community 211 - "Illuminate\Http\JsonResponse"
Cohesion: 0.28
Nodes (4): DriverPackageController, DriverScanController, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Storage

### Community 217 - "Illuminate\Http\Request"
Cohesion: 0.16
Nodes (7): DriverAuthController, DriverPaymentResource, IncidentResource, RouteResource, RouteStopResource, Illuminate\Http\Request, Illuminate\Http\Resources\Json\JsonResource

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 225 - "Controller"
Cohesion: 0.29
Nodes (3): DriverDashboardController, Controller, DriverPackageResource

### Community 234 - "warehouses-manager.blade.php"
Cohesion: 0.29
Nodes (6): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }}), toggleCoverageActive({{ $coverage->id }}), toggleCoveragePanel({{ $warehouse->id }})

### Community 244 - "drivers-approval-manager.blade.php"
Cohesion: 0.40
Nodes (4): activate({{ $driver->id }}), approve({{ $driver->id }}), reject({{ $driver->id }}), suspend({{ $driver->id }})

### Community 258 - "PaymentOrder"
Cohesion: 0.12
Nodes (3): PaymentOrder, PaymentReconciliationService, PaymentService

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancel, edit({{ $member->id }}), startCreate, toggleActive({{ $member->id }})

### Community 272 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 276 - "PackageService"
Cohesion: 0.11
Nodes (3): CreatePackage, PackageService, Livewire\Attributes\Computed

### Community 320 - "PaymentWebhookController.php"
Cohesion: 0.40
Nodes (3): PaymentWebhookController, Illuminate\Support\Facades\Validator, Throwable

## Knowledge Gaps
- **279 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+274 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **94 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `RuntimeException`, `DeliveryClaimFromDestinationAgencyTest`, `PaymentOrder`, `PackageDetailCodPaymentMethodTest`, `WarehouseCoverage`, `Incident`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `PackageReception`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `StaffManager`, `PackageCreateRegisteredByTest`, `LogisticsResolutionService`, `Ally`, `PackageService`, `AuditLogViewer`, `Customer`, `DriverPayment`, `PackageServiceCodTest`, `Scanner`, `PackageStatusUpdated`, `TestCase`, `PackageLabelController.php`, `Driver`, `api.php`, `Route`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Illuminate\Http\JsonResponse`, `HubReleaseService`, `PriceCalculator`, `Illuminate\Http\Request`, `Controller`, `Livewire\Component`, `PackageCreate`, `AuditLog`, `TariffService`, `DriverHubDistributionController`, `Dashboard`, `BcvRate`, `DriverDeliveryController`, `GeocodePackageDeliveryAddress`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.173) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `RuntimeException`, `DeliveryClaimFromDestinationAgencyTest`, `PackageDetailCodPaymentMethodTest`, `StaffManagerTest`, `RouteDetailTest`, `ScannerLivewireTest`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Incident`, `RegistrationTest.php`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AllyUser`, `StaffManager`, `UsersManager`, `LoginForm.php`, `Ally`, `AllyFinanceTest`, `Customer`, `DriverPayment`, `DriverApiFlowTest.php`, `PackageServiceCodTest`, `EmailVerificationTest.php`, `Scanner`, `DriverAssignmentRenderTest`, `AlliesManagerLocationTest`, `RoutesManagerHubDistributionTest`, `PackageReceptionHubTest`, `TestCase`, `DriverRouteClaimTest`, `Driver`, `AuditLogViewerTest`, `DriverHubDistributionTest`, `Illuminate\Database\Eloquent\Relations\HasMany`, `RoutesManagerListFilterTest`, `OfficeLocatorLocationTest`, `.route`, `WarehousesManagerTest`, `UsersManagerSearchUrlTest`, `Illuminate\Http\Request`, `DriverRouteReleaseTest`, `Livewire\Component`, `WarehouseDispatchTest`, `DashboardClaimRouteTest`, `HubDashboardUxTest`, `RecommendationFormTest`, `DriverRouteCompleteTest`, `RoutesManagerMultistateTest`, `WarehouseCoverageManagerTest`, `DriverRouteActiveHubScanTest`, `AllyFinancialServiceTest`, `DriverHubReceptionTest`, `RouteStop`, `AlliesManagerDestinationVerificationTest`, `DeliveryRouteOrderGeocodingTest`, `PackageReceptionScannerTest`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.100) - this node is a cross-community bridge._
- **Why does `Warehouse` connect `Warehouse` to `RuntimeException`, `RouteDetailTest`, `ScannerLivewireTest`, `WarehouseCoverage`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `PackageReception`, `Package`, `UsersManager`, `LoginForm.php`, `RoutesManagerHubDistributionTest`, `PackageReceptionHubTest`, `TestCase`, `Driver`, `Route`, `DriverHubDistributionTest`, `Illuminate\Database\Eloquent\Relations\HasMany`, `.route`, `WarehousesManagerTest`, `WarehousesManager`, `Livewire\Component`, `HubDashboardUxTest`, `RoutesManagerMultistateTest`, `WarehouseCoverageManagerTest`, `DriverRouteActiveHubScanTest`, `HubReleaseServiceTest`, `RouteStop`, `WarehouseDispatchTest`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _279 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `RuntimeException` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._