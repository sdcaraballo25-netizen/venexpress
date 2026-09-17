# Graph Report - venexpress  (2026-09-16)

## Corpus Check
- 446 files · ~346,550 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2505 nodes · 6032 edges · 314 communities (220 shown, 94 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 57 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `66ea4ef1`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- RuntimeException
- StaffManagerTest
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Incident
- setup
- Venexpress — Project Rules
- PackageReception
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
- web.php
- users-manager.blade.php
- layout/navigation.blade.php
- DriverPayment
- RoutesManager
- Warehouse
- AuditLogViewerTest
- Route
- DriverHubDistributionTest
- DriverRemunerationRate
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- Recommendation
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
- RouteStop
- audit-log-viewer.blade.php
- AuditLog
- Illuminate\Support\Facades\Schema
- ally-finance.blade.php
- Illuminate\Database\Schema\Blueprint
- TariffService
- DriverIncidentController.php
- HubReleaseServiceTest
- venexpress-ui/SKILL.md
- Illuminate\Http\Request
- Dashboard
- routes-manager.blade.php
- BcvRate
- package-create.blade.php
- DriverDeliveryController.php
- ScannerHubOperationUxTest
- Illuminate\Database\Migrations\Migration
- GeocodePackageDeliveryAddress
- WarehouseDispatchTest
- RoutesManagerStopFilterTest
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- ScannerLivewireTest
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Relations\BelongsTo
- driver/dashboard.blade.php
- WarehouseStaffRoleTest
- client/dashboard.blade.php
- StaffManager
- Package
- Scanner
- OfficeLocator
- UsersManager
- LoginForm.php
- commissions.blade.php
- RateMatrixManager
- Customer
- package-detail.blade.php
- confirmOperation(
- DriverApiFlowTest
- PackageServiceCodTest
- DriverDashboardController.php
- HubDistributionPhase
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
- Packages
- TestCase
- config
- DriverRouteClaimTest
- DriversApprovalManager
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
- Dashboard
- PackageCreatePickupTest
- Controller
- PendingPayments
- RoutesManagerListFilterTest
- OfficeLocatorLocationTest
- .route
- WarehousesManagerTest
- Illuminate\Http\JsonResponse
- autoload-dev
- PriceCalculator
- DriverRouteController.php
- resend
- venexpress-laravel/SKILL.md
- post-autoload-dump
- DashboardClaimRouteTest
- PackagesVisibilityTest
- RecommendationFormTest
- DriverRouteCompleteTest
- warehouses-manager.blade.php
- RoutesManagerMultistateTest
- DriverRouteActiveHubScanTest
- DriverHubDistributionController
- drivers-approval-manager.blade.php
- AlliesManagerDestinationVerificationTest
- DriverRouteController
- DeliveryRouteOrderGeocodingTest
- PackageReceptionScannerTest
- PackageCreateDeliveryCoordinatesTest
- AllySettlement
- Almacen/Dashboard.php
- PaymentOrder
- VenezuelaLocationServiceCatalogTest
- RegistrationTest.php
- staff-manager.blade.php
- PackageCreateRegisteredByTest
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
5. `Driver` - 141 edges
6. `Warehouse` - 136 edges
7. `Ally` - 102 edges
8. `RouteStop` - 96 edges
9. `AuditLog` - 67 edges
10. `WarehouseCoverage` - 63 edges

## Surprising Connections (you probably didn't know these)
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php

## Import Cycles
- None detected.

## Communities (314 total, 94 thin omitted)

### Community 0 - "RuntimeException"
Cohesion: 0.14
Nodes (6): PackageHistory, HubReleaseService, PackageDispatchService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Support\Facades\DB, RuntimeException

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
Cohesion: 0.13
Nodes (15): scripts, dev, post-create-project-cmd, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::prePackageUninstall (+7 more)

### Community 8 - "Incident"
Cohesion: 0.13
Nodes (3): IncidentsManager, Incidents, Incident

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
Cohesion: 0.08
Nodes (4): AlliesManager, Ally, Illuminate\Database\Eloquent\Relations\HasMany, createPackage()

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 22 - "web.php"
Cohesion: 0.09
Nodes (11): AuditLogViewer, Commissions, SalesCloseout, excelDownload(), AppDownload, Packages, HelpCenter, Carbon (+3 more)

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "DriverPayment"
Cohesion: 0.13
Nodes (4): DriverPayments, DriverPayment, DriverPaymentService, DriverPaymentsTest

### Community 27 - "Warehouse"
Cohesion: 0.08
Nodes (7): Warehouse, WarehouseCoverage, PackageReceptionHubTest, WarehouseCoverageManagerTest, PublicTrackingHubDestinationLabelTest, HubReceptionServiceTest, LogisticsResolutionServiceTest

### Community 72 - "Route"
Cohesion: 0.13
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

### Community 84 - "Recommendation"
Cohesion: 0.27
Nodes (3): RecommendationsManager, RecommendationForm, Recommendation

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 92 - "User"
Cohesion: 0.04
Nodes (18): User, Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL, Livewire\Volt\Volt, UsersManagerDriverTypeTest (+10 more)

### Community 93 - "CityDistance"
Cohesion: 0.10
Nodes (7): CityDistanceManager, self, CityDistance, self, self, self, static

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "Livewire\Component"
Cohesion: 0.08
Nodes (19): Dashboard, HelpCenter, RoutesDashboard, Cod, DailyCashCut, HelpCenter, Incidents, HelpCenter (+11 more)

### Community 102 - "AuditLog"
Cohesion: 0.09
Nodes (4): AllyFinancialTransaction, AuditLog, AllyFinancialService, AllyFinancialSettlementTest

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), exportExcel, $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 107 - "TariffService"
Cohesion: 0.11
Nodes (4): RateMatrix, TariffService, InvalidArgumentException, TariffServiceTest

### Community 112 - "venexpress-ui/SKILL.md"
Cohesion: 0.17
Nodes (11): Avoid "AI Generated" Aesthetics, Buttons, Color Discipline, Core Design Philosophy, Layout, Professional Product Design, Spacing, Typography (+3 more)

### Community 113 - "Illuminate\Http\Request"
Cohesion: 0.20
Nodes (11): EnsureAccountIsApproved, EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+3 more)

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

### Community 136 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.09
Nodes (3): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 137 - "driver/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): claimRoute({{ $route->id }}), completeRoute, releaseRoute, startRoute

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), clearHistoryFilters, showHistory, showPending

### Community 141 - "Package"
Cohesion: 0.04
Nodes (10): DriverAssignment, PackageDispatch, PackageDetail, PackagePickup, PackageReception, PackageDetail, PublicTracking, Package (+2 more)

### Community 142 - "Scanner"
Cohesion: 0.06
Nodes (13): TrackingController, FieldScanner, Scanner, HubReceptionService, LogisticsResolutionResult, self, LogisticsResolutionService, Collection (+5 more)

### Community 145 - "LoginForm.php"
Cohesion: 0.08
Nodes (15): LoginForm, DistanceApiService, DriverFactory, UserFactory, WarehouseFactory, Illuminate\Auth\Events\Lockout, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Cache (+7 more)

### Community 150 - "Customer"
Cohesion: 0.09
Nodes (7): Dashboard, Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, ClientDashboardTest, ClientPendingPaymentsTest

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (4): PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 161 - "driver-payments.blade.php"
Cohesion: 0.40
Nodes (4): cancelPayment({{ $payment->id }}), markPaid({{ $payment->id }}), exportExcel, toggleDriver({{ $row->driver_id }})

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "TestCase"
Cohesion: 0.08
Nodes (13): Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Queue, Livewire\Livewire, Maatwebsite\Excel\Facades\Excel, DeliveryAssignmentServiceTest (+5 more)

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

### Community 193 - "SimpleArrayExport"
Cohesion: 0.23
Nodes (8): SimpleArrayExport, Generator, Maatwebsite\Excel\Concerns\FromGenerator, Maatwebsite\Excel\Concerns\ShouldAutoSize, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithStyles, PhpOffice\PhpSpreadsheet\Style\Fill, PhpOffice\PhpSpreadsheet\Worksheet\Worksheet

### Community 194 - "Driver"
Cohesion: 0.10
Nodes (6): Driver, AppDownloadAccessTest, DriverDashboardTest, DriverHubReceptionTest, DriverHubTransferApiTest, DriverRouteCompletePendingPackagesTest

### Community 199 - "Controller"
Cohesion: 0.18
Nodes (6): DriverAuthController, VerifyEmailController, Controller, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 211 - "Illuminate\Http\JsonResponse"
Cohesion: 0.31
Nodes (3): DriverPackageController, DriverScanController, Illuminate\Http\JsonResponse

### Community 212 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 216 - "PriceCalculator"
Cohesion: 0.14
Nodes (3): CreatePackage, PriceCalculator, Livewire\Attributes\Computed

### Community 217 - "DriverRouteController.php"
Cohesion: 0.31
Nodes (3): RouteResource, RouteStopResource, Illuminate\Http\Resources\Json\JsonResource

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 225 - "post-autoload-dump"
Cohesion: 0.67
Nodes (3): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan package:discover --ansi

### Community 234 - "warehouses-manager.blade.php"
Cohesion: 0.29
Nodes (6): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }}), toggleCoverageActive({{ $coverage->id }}), toggleCoveragePanel({{ $warehouse->id }})

### Community 244 - "drivers-approval-manager.blade.php"
Cohesion: 0.40
Nodes (4): activate({{ $driver->id }}), approve({{ $driver->id }}), reject({{ $driver->id }}), suspend({{ $driver->id }})

### Community 257 - "Almacen/Dashboard.php"
Cohesion: 0.20
Nodes (3): Dashboard, DeliveryAssignmentService, DestinationReceptionService

### Community 258 - "PaymentOrder"
Cohesion: 0.10
Nodes (5): PaymentOrders, PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Contracts\View\View

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancel, edit({{ $member->id }}), startCreate, toggleActive({{ $member->id }})

### Community 272 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 320 - "PaymentWebhookController.php"
Cohesion: 0.40
Nodes (3): PaymentWebhookController, Illuminate\Support\Facades\Validator, Throwable

## Knowledge Gaps
- **280 isolated node(s):** `assignToDriver({{ $route->id }})`, `Authentication and Authorization`, `Backend / API`, `Business Logic`, `Changes` (+275 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **94 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `RuntimeException`, `DeliveryClaimFromDestinationAgencyTest`, `StaffManagerTest`, `PackageDetailCodPaymentMethodTest`, `ScannerLivewireTest`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `RegistrationTest.php`, `WarehouseStaffRoleTest`, `AllyUser`, `Package`, `StaffManager`, `UsersManager`, `LoginForm.php`, `Ally`, `AllyFinanceTest`, `PackageService`, `Customer`, `DriverPayment`, `DriverApiFlowTest`, `Warehouse`, `PackageServiceCodTest`, `DriverAssignmentRenderTest`, `AlliesManagerLocationTest`, `RoutesManagerHubDistributionTest`, `TestCase`, `DriverRouteClaimTest`, `Driver`, `Controller`, `AuditLogViewerTest`, `DriverHubDistributionTest`, `RoutesManagerListFilterTest`, `OfficeLocatorLocationTest`, `.route`, `WarehousesManagerTest`, `DriverRouteReleaseTest`, `Livewire\Component`, `WarehouseDispatchTest`, `DashboardClaimRouteTest`, `RouteStop`, `AuditLog`, `RecommendationFormTest`, `DriverRouteCompleteTest`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `ScannerHubOperationUxTest`, `AlliesManagerDestinationVerificationTest`, `DeliveryRouteOrderGeocodingTest`, `PackageReceptionScannerTest`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.144) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `RuntimeException`, `Almacen/Dashboard.php`, `PaymentOrder`, `PackageDetailCodPaymentMethodTest`, `Incident`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `PackageReception`, `StaffManager`, `PackageCreateRegisteredByTest`, `Scanner`, `Ally`, `PackageService`, `web.php`, `Customer`, `DriverPayment`, `PackageServiceCodTest`, `DriverDashboardController.php`, `HubDistributionPhase`, `Warehouse`, `PackageStatusUpdated`, `Packages`, `TestCase`, `PackageLabelController.php`, `Driver`, `Dashboard`, `PendingPayments`, `Route`, `Illuminate\Http\JsonResponse`, `PriceCalculator`, `PackageCreate`, `Livewire\Component`, `AuditLog`, `TariffService`, `DriverIncidentController.php`, `DriverHubDistributionController`, `Dashboard`, `BcvRate`, `DriverDeliveryController.php`, `GeocodePackageDeliveryAddress`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.139) - this node is a cross-community bridge._
- **Why does `Route` connect `Route` to `RuntimeException`, `Almacen/Dashboard.php`, `ScannerLivewireTest`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Package`, `Scanner`, `DriverApiFlowTest`, `RoutesManager`, `Warehouse`, `HubDistributionPhase`, `RoutesManagerHubDistributionTest`, `TestCase`, `DriverRouteClaimTest`, `Driver`, `DriverHubDistributionTest`, `RoutesManagerListFilterTest`, `.route`, `DriverRouteController.php`, `DriverRouteReleaseTest`, `Livewire\Component`, `VenezuelaLocationService`, `RouteStop`, `DashboardClaimRouteTest`, `DriverRouteCompleteTest`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `Dashboard`, `ScannerHubOperationUxTest`, `DriverRouteController`, `WarehouseDispatchTest`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **What connects `assignToDriver({{ $route->id }})`, `Authentication and Authorization`, `Backend / API` to the rest of the system?**
  _280 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `RuntimeException` be split into smaller, more focused modules?**
  _Cohesion score 0.14039408866995073 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._