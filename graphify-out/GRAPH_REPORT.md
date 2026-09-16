# Graph Report - venexpress  (2026-09-16)

## Corpus Check
- 445 files · ~350,142 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 30 file(s) not represented in the graph (top: (none) 17, .patch 7, .graphify-bak 1)

## Summary
- 2513 nodes · 6260 edges · 311 communities (80 shown, 93 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 64 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `66ea4ef1`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- LogisticsResolutionService
- StaffManagerTest
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- package.json
- scripts
- Incident
- web.php
- Venexpress — Project Rules
- TariffServiceTest
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
- DriverHubDistributionTest
- Route
- WarehouseCoverage
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
- HubDashboardUxTest
- audit-log-viewer.blade.php
- AllyFinancialService
- Illuminate\Support\Facades\Schema
- ally-finance.blade.php
- Illuminate\Database\Schema\Blueprint
- TariffService
- PackageService
- PackageHistory
- venexpress-ui/SKILL.md
- Illuminate\Http\Request
- Dashboard
- routes-manager.blade.php
- BcvRate
- package-create.blade.php
- Customer
- RouteStop
- Illuminate\Database\Migrations\Migration
- GeocodePackageDeliveryAddress
- DatabaseSeeder.php
- RoutesManagerStopFilterTest
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- ScannerLivewireTest
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Relations\BelongsTo
- driver/dashboard.blade.php
- WarehouseCoverageManagerTest
- client/dashboard.blade.php
- StaffManager
- Package
- TrackingController
- OfficeLocator
- UsersManager
- LoginForm.php
- commissions.blade.php
- RateMatrixManager
- Dashboard
- package-detail.blade.php
- confirmOperation(
- DriverApiFlowTest
- PackageServiceCodTest
- DriverDashboardController.php
- Scanner
- Checklist final antes de operar en real — Venexpress
- .route
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
- WarehouseDispatchTest
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
- AllyFinancialServiceTest
- PackageCreatePickupTest
- api.php
- PackageCreateTest
- RoutesManagerListFilterTest
- OfficeLocatorLocationTest
- RouteDetailTest
- WarehousesManagerTest
- DriverPackageResource
- ClientPendingPaymentsTest
- PriceCalculator
- Illuminate\Http\Resources\Json\JsonResource
- resend
- venexpress-laravel/SKILL.md
- UsersManagerDriverTypeTest
- DashboardClaimRouteTest
- DriverHubReceptionTest
- PackagesVisibilityTest
- DriverHubDistributionController.php
- DriverRouteCompleteTest
- warehouses-manager.blade.php
- RoutesManagerMultistateTest
- DriverRouteActiveHubScanTest
- RecommendationFormTest
- autoload-dev
- drivers-approval-manager.blade.php
- extra
- ExampleTest
- AlliesManagerDestinationVerificationTest
- DriverRouteController.php
- DeliveryRouteOrderGeocodingTest
- PackageReceptionScannerTest
- PackageCreateDeliveryCoordinatesTest
- PaymentOrder
- RoutesManagerHubDistributionTest
- Livewire\Volt\Volt
- staff-manager.blade.php
- PackageCreateRegisteredByTest
- Illuminate\Console\Command
- sales-closeout.blade.php
- AllyFinanceTest
- RuntimeException
- archive({{ $recommendation->id }})
- assignToDriver({{ $route->id }})
- PriceCalculatorCatalogTest
- DriverAssignmentRenderTest
- AlliesManagerLocationTest
- Illuminate\Http\JsonResponse
- excel.php

## God Nodes (most connected - your core abstractions)
1. `Package` - 278 edges
2. `User` - 264 edges
3. `Route` - 191 edges
4. `Warehouse` - 152 edges
5. `TestCase` - 150 edges
6. `Driver` - 142 edges
7. `Ally` - 103 edges
8. `CreatesTestPackages` - 103 edges
9. `RouteStop` - 96 edges
10. `WarehouseCoverage` - 76 edges

## Surprising Connections (you probably didn't know these)
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php
- `DriverDashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/DriverDashboardController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (311 total, 93 thin omitted)

### Community 0 - "LogisticsResolutionService"
Cohesion: 0.09
Nodes (7): HubReceptionService, HubReleaseService, LogisticsResolutionResult, self, LogisticsResolutionService, Collection, PackageDispatchService

### Community 3 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 4 - "allies-manager.blade.php"
Cohesion: 0.22
Nodes (8): activate({{ $ally->id }}), approve({{ $ally->id }}), editLocation({{ $ally->id }}), reject({{ $ally->id }}), $set(, saveLocation, suspend({{ $ally->id }}), toggleVerifiedDestination({{ $ally->id }})

### Community 5 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 6 - "package.json"
Cohesion: 0.07
Nodes (25): devDependencies, autoprefixer, axios, concurrently, laravel-vite-plugin, postcss, tailwindcss, @tailwindcss/forms (+17 more)

### Community 7 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 8 - "Incident"
Cohesion: 0.09
Nodes (6): Dashboard, IncidentsManager, Incidents, Incidents, Incident, IncidentService

### Community 9 - "web.php"
Cohesion: 0.09
Nodes (8): DriverAssignment, Cod, Dashboard, Packages, PendingPayments, AppDownload, Packages, HelpCenter

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.11
Nodes (17): Authentication and Authorization, Backend / API, Business Logic, Changes, Database, Frontend, General Development Rules, Generated Files (+9 more)

### Community 12 - "AllyUser"
Cohesion: 0.16
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
Nodes (3): AlliesManager, Ally, Illuminate\Database\Eloquent\Relations\HasMany

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 22 - "AuditLogViewer"
Cohesion: 0.11
Nodes (8): AuditLogViewer, Commissions, SalesCloseout, ExportsSpreadsheet, Carbon, Illuminate\Database\Eloquent\Builder, Illuminate\Support\Carbon, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "DriverPayment"
Cohesion: 0.17
Nodes (3): DriverPayments, DriverPayment, DriverPaymentsTest

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
Nodes (12): User, DriverFactory, Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, WarehouseStaffRoleTest, AllyFinancialSettlementTest, AuthenticationTest (+4 more)

### Community 93 - "CityDistance"
Cohesion: 0.11
Nodes (6): CityDistanceManager, CityDistance, self, self, self, static

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "Livewire\Component"
Cohesion: 0.09
Nodes (15): HelpCenter, RoutesDashboard, DailyCashCut, HelpCenter, HelpCenter, HelpCenter, Illuminate\Support\Facades\Auth, Illuminate\Validation\Rule (+7 more)

### Community 99 - "VenezuelaLocationService"
Cohesion: 0.12
Nodes (3): VenezuelaLocationService, Illuminate\Support\Facades\File, VenezuelaLocationServiceCatalogTest

### Community 102 - "AllyFinancialService"
Cohesion: 0.08
Nodes (4): AllyFinance, AllyFinancialTransaction, AllySettlement, AllyFinancialService

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), exportExcel, $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 107 - "TariffService"
Cohesion: 0.28
Nodes (3): RateMatrix, TariffService, InvalidArgumentException

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
Cohesion: 0.12
Nodes (5): BcvRateManager, BcvRate, self, BcvRateService, Carbon\Carbon

### Community 118 - "package-create.blade.php"
Cohesion: 0.20
Nodes (9): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer, selectAllyPickup, selectDelivery (+1 more)

### Community 125 - "GeocodePackageDeliveryAddress"
Cohesion: 0.10
Nodes (12): GeocodePackageDeliveryAddress, PackageObserver, AppServiceProvider, VoltServiceProvider, GeocodingService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable (+4 more)

### Community 126 - "DatabaseSeeder.php"
Cohesion: 0.33
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 134 - "require"
Cohesion: 0.17
Nodes (12): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+4 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 136 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.08
Nodes (3): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 137 - "driver/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): claimRoute({{ $route->id }}), completeRoute, releaseRoute, startRoute

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), clearHistoryFilters, showHistory, showPending

### Community 141 - "Package"
Cohesion: 0.05
Nodes (11): PackageDispatch, PackageReception, PackageDetail, PackagePickup, PackageReception, Dashboard, PublicTracking, Package (+3 more)

### Community 142 - "TrackingController"
Cohesion: 0.24
Nodes (5): TrackingController, AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 145 - "LoginForm.php"
Cohesion: 0.08
Nodes (14): LoginForm, DistanceApiService, UserFactory, WarehouseFactory, Illuminate\Auth\Events\Lockout, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Password (+6 more)

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 157 - "Scanner"
Cohesion: 0.11
Nodes (5): FieldScanner, RouteDetail, Scanner, HubDistributionPhase, LogisticsScanService

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - ".route"
Cohesion: 0.06
Nodes (11): VerifyAccount, PackageStatusUpdated, MailMessage, MailMessage, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification, AuditLogViewerTest (+3 more)

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
Cohesion: 0.09
Nodes (12): Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Queue, Livewire\Livewire, Maatwebsite\Excel\Facades\Excel, CommissionsTest (+4 more)

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
Cohesion: 0.11
Nodes (7): DriversApprovalManager, Driver, DeliveryAssignmentServiceTest, AppDownloadAccessTest, DriverDashboardTest, DriverHubTransferApiTest, DriverRouteCompletePendingPackagesTest

### Community 199 - "api.php"
Cohesion: 0.32
Nodes (4): VerifyEmailController, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 211 - "DriverPackageResource"
Cohesion: 0.36
Nodes (3): DriverPackageController, DriverPackageResource, Illuminate\Support\Facades\Storage

### Community 217 - "Illuminate\Http\Resources\Json\JsonResource"
Cohesion: 0.19
Nodes (4): DriverIncidentController, IncidentResource, RouteStopResource, Illuminate\Http\Resources\Json\JsonResource

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 234 - "warehouses-manager.blade.php"
Cohesion: 0.29
Nodes (6): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }}), toggleCoverageActive({{ $coverage->id }}), toggleCoveragePanel({{ $warehouse->id }})

### Community 241 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 244 - "drivers-approval-manager.blade.php"
Cohesion: 0.40
Nodes (4): activate({{ $driver->id }}), approve({{ $driver->id }}), reject({{ $driver->id }}), suspend({{ $driver->id }})

### Community 245 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 258 - "PaymentOrder"
Cohesion: 0.10
Nodes (5): PaymentOrders, PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Contracts\View\View

### Community 265 - "Livewire\Volt\Volt"
Cohesion: 0.13
Nodes (6): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, PasswordResetTest, PasswordUpdateTest

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancel, edit({{ $member->id }}), startCreate, toggleActive({{ $member->id }})

### Community 272 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 276 - "RuntimeException"
Cohesion: 0.09
Nodes (9): PackageDetail, AuditLog, DeliveryAssignmentService, DriverPaymentService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\QueryException, Illuminate\Support\Facades\DB, RuntimeException (+1 more)

### Community 320 - "Illuminate\Http\JsonResponse"
Cohesion: 0.17
Nodes (7): DriverAuthController, DriverDeliveryController, Controller, DriverScanController, PaymentWebhookController, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Validator

## Knowledge Gaps
- **276 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+271 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 810 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **93 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `LogisticsResolutionService`, `PackageDetailCodPaymentMethodTest`, `PaymentOrder`, `Incident`, `web.php`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `StaffManager`, `PackageCreateRegisteredByTest`, `TrackingController`, `Ally`, `RuntimeException`, `AuditLogViewer`, `Dashboard`, `PackageServiceCodTest`, `DriverDashboardController.php`, `Scanner`, `.route`, `TestCase`, `PackageLabelController.php`, `Illuminate\Http\JsonResponse`, `Driver`, `Route`, `WarehouseCoverage`, `DriverPackageResource`, `Illuminate\Http\Resources\Json\JsonResource`, `User`, `PackageCreate`, `Livewire\Component`, `AllyFinancialService`, `DriverHubDistributionController.php`, `TariffService`, `PackageService`, `Dashboard`, `BcvRate`, `GeocodePackageDeliveryAddress`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.176) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `PackageDetailCodPaymentMethodTest`, `DeliveryClaimFromDestinationAgencyTest`, `StaffManagerTest`, `RoutesManagerHubDistributionTest`, `ScannerLivewireTest`, `Incident`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `WarehouseCoverageManagerTest`, `Livewire\Volt\Volt`, `AllyUser`, `StaffManager`, `UsersManager`, `LoginForm.php`, `Ally`, `AllyFinanceTest`, `RuntimeException`, `DriverPayment`, `DriverApiFlowTest`, `PackageServiceCodTest`, `Scanner`, `.route`, `DriverAssignmentRenderTest`, `AlliesManagerLocationTest`, `PackageReceptionHubTest`, `TestCase`, `DriverRouteClaimTest`, `WarehouseDispatchTest`, `Illuminate\Http\JsonResponse`, `Driver`, `AllyFinancialServiceTest`, `DriverHubDistributionTest`, `RoutesManagerListFilterTest`, `OfficeLocatorLocationTest`, `RouteDetailTest`, `WarehousesManagerTest`, `ClientPendingPaymentsTest`, `DriverRouteReleaseTest`, `UsersManagerDriverTypeTest`, `Livewire\Component`, `DashboardClaimRouteTest`, `DriverHubReceptionTest`, `HubDashboardUxTest`, `DriverRouteCompleteTest`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `RecommendationFormTest`, `PackageHistory`, `PackageReceptionScannerTest`, `Customer`, `RouteStop`, `AlliesManagerDestinationVerificationTest`, `DeliveryRouteOrderGeocodingTest`, `DatabaseSeeder.php`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.120) - this node is a cross-community bridge._
- **Why does `Warehouse` connect `Warehouse` to `LogisticsResolutionService`, `ScannerLivewireTest`, `RoutesManagerHubDistributionTest`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `WarehouseCoverageManagerTest`, `Package`, `UsersManager`, `Ally`, `LoginForm.php`, `RuntimeException`, `RoutesManager`, `.route`, `PackageReceptionHubTest`, `TestCase`, `WarehouseDispatchTest`, `Driver`, `DriverHubDistributionTest`, `WarehouseCoverage`, `RouteDetailTest`, `WarehousesManagerTest`, `User`, `WarehousesManager`, `Livewire\Component`, `HubDashboardUxTest`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `PackageHistory`, `RouteStop`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.057) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _276 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `LogisticsResolutionService` be split into smaller, more focused modules?**
  _Cohesion score 0.09309309309309309 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.06666666666666667 - nodes in this community are weakly interconnected._