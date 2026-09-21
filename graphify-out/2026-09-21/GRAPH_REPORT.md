# Graph Report - venexpress  (2026-09-21)

## Corpus Check
- 506 files · ~360,336 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2903 nodes · 7309 edges · 353 communities (236 shown, 117 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 106 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `01ec132d`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AuditLog
- StaffManagerTest
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Incident
- setup
- Venexpress — Project Rules
- HubReceptionService
- logging.php
- Money
- graphify reference: extra exports and benchmark
- README.md
- Ally
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- DriverPayments
- users-manager.blade.php
- layout/navigation.blade.php
- DriverPayment
- RoutesManager
- Warehouse
- update-password-form.blade.php
- AuditLogViewerTest
- Route
- DriverHubDistributionTest
- LogisticsResolutionService
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
- Livewire\Volt\Volt
- DriverRouteReleaseTest
- User
- CityDistance
- WarehousesManager
- require-dev
- .route
- PackageCreate
- DriversApprovalManager
- VenezuelaLocationService
- HubDashboardUxTest
- audit-log-viewer.blade.php
- RemunerationsSummary
- Illuminate\Database\Schema\Blueprint
- ally-finance.blade.php
- Illuminate\Support\Facades\Schema
- GoogleAuthTest
- RateMatrix
- Dashboard
- HubReleaseServiceTest
- venexpress-ui/SKILL.md
- bootstrap/app.php
- Dashboard
- routes-manager.blade.php
- BcvRate
- Illuminate\Bus\Queueable
- package-create.blade.php
- DriverDeliveryController
- ScannerHubOperationUxTest
- AppServiceProvider.php
- User.php
- RoutesManagerStopFilterTest
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- RouteStop
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Driver/dashboard.blade.php
- Illuminate\Database\Eloquent\Relations\HasMany
- client/dashboard.blade.php
- StaffManager
- Package
- Scanner
- OfficeLocator
- UsersManager
- api.php
- commissions.blade.php
- Illuminate\Database\Migrations\Migration
- RateMatrixManager
- SalesCloseoutTest
- Customer
- Driver/package-detail.blade.php
- confirmOperation(
- DriverApiFlowTest
- RemunerationsSummaryTest
- Illuminate\Http\Request
- GeocodePackageDeliveryAddress
- Checklist final antes de operar en real — Venexpress
- Illuminate\Notifications\Messages\MailMessage
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- Checklist de infraestructura para producción
- driver-assignment.blade.php
- DocumentPhotoController
- TestCase
- config
- DriverRouteClaimTest
- ExportsSpreadsheet.php
- bcv-rate-manager.blade.php
- SalesCloseout
- psr-4
- PackageLabelController.php
- price-calculator.blade.php
- PackageCreateFulfillmentModeTest
- sanctum.php
- incidents-manager.blade.php
- PackageReceptionPickupVerificationTest
- SimpleArrayExport
- Driver
- VerifyAccount
- PackageCreatePickupTest
- Controller
- AuditLogViewer
- RoutesManagerListFilterTest
- OfficeLocatorLocationTest
- DocumentPhotoControllerTest
- DashboardBcvRateWarningTest
- Illuminate\Http\JsonResponse
- PackageCreateEmailNotificationTest
- PackageCreated
- Livewire\Component
- PackageStatusUpdated
- PasswordChangeCode
- resend
- venexpress-laravel/SKILL.md
- DistanceApiService
- DashboardClaimRouteTest
- Illuminate\Support\Str
- PackagesVisibilityTest
- RecommendationFormTest
- DriverRouteCompleteTest
- warehouses-manager.blade.php
- RoutesManagerMultistateTest
- DriverRouteActiveHubScanTest
- RouteHistoryTest
- DriverHubDistributionController
- DriverHubReceptionTest
- WarehousesManagerTest
- AlliesManagerDestinationVerificationTest
- DriverRouteController
- DeliveryRouteOrderGeocodingTest
- PackageReceptionScannerTest
- PackageCreateDeliveryCoordinatesTest
- AllyFinancialService
- Dashboard
- PaymentOrder
- SyncBcvRateTest
- VenezuelaLocationServiceCatalogTest
- PaymentWebhookController.php
- Packages
- PriceCalculator
- staff-manager.blade.php
- PackageCreateRegisteredByTest
- PackageServiceDeliveryClaimTest
- UsersManagerToggleStatusTest
- VerifyAccountTest
- sales-closeout.blade.php
- AllyFinanceTest
- PackageService
- ClientMenuStaysInsideDashboardTest
- DriverIncidentApiTest
- PackageCreateTest
- PriceCalculatorCatalogTest
- DriverAssignmentRenderTest
- AlliesManagerLocationTest
- DriverPackagePiiExposureTest
- AdminDocumentLinksRenderTest
- ExampleTest
- Dashboard
- remunerations-summary.blade.php
- profile/show.blade.php
- AlliesManagerApprovalTest
- PendingPayments
- excel.php
- 2026_09_19_000001_add_public_id_to_users_table.php
- 2026_09_19_000002_add_public_id_to_allies_table.php
- 2026_09_19_000003_add_public_id_to_drivers_table.php
- acceptDelivery({{ $package->id }})
- create.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 363 edges
2. `Package` - 296 edges
3. `Route` - 210 edges
4. `TestCase` - 206 edges
5. `Driver` - 193 edges
6. `Warehouse` - 162 edges
7. `Ally` - 126 edges
8. `RouteStop` - 99 edges
9. `WarehouseCoverage` - 76 edges
10. `AuditLog` - 68 edges

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

## Communities (353 total, 117 thin omitted)

### Community 0 - "AuditLog"
Cohesion: 0.10
Nodes (9): AuditLog, PackageHistory, DeliveryAssignmentService, DestinationReceptionService, DriverPaymentService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\QueryException, Illuminate\Support\Facades\DB (+1 more)

### Community 3 - "composer.json"
Cohesion: 0.12
Nodes (16): autoload-dev, psr-4, description, extra, laravel, keywords, dont-discover, license (+8 more)

### Community 4 - "allies-manager.blade.php"
Cohesion: 0.33
Nodes (5): closeDetails, editLocation({{ $ally->id }}), $set(, saveLocation, viewDetails({{ $ally->id }})

### Community 5 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 6 - "devDependencies"
Cohesion: 0.08
Nodes (25): autoprefixer, axios, concurrently, laravel-vite-plugin, devDependencies, autoprefixer, axios, concurrently (+17 more)

### Community 7 - "scripts"
Cohesion: 0.11
Nodes (18): scripts, dev, post-autoload-dump, post-create-project-cmd, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout (+10 more)

### Community 8 - "Incident"
Cohesion: 0.09
Nodes (5): IncidentsManager, Incidents, Incidents, Incident, IncidentService

### Community 9 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.11
Nodes (17): Authentication and Authorization, Backend / API, Business Logic, Changes, Database, Frontend, General Development Rules, Generated Files (+9 more)

### Community 11 - "HubReceptionService"
Cohesion: 0.10
Nodes (4): PackageReception, HubReceptionService, HubReleaseService, PackageDispatchService

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "Money"
Cohesion: 0.12
Nodes (7): Money, BigDecimal, Brick\Math\BigDecimal, Brick\Math\RoundingMode, PHPUnit\Framework\TestCase, ExampleTest, MoneyTest

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 17 - "Ally"
Cohesion: 0.14
Nodes (3): AlliesManager, Ally, createPackage()

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 27 - "Warehouse"
Cohesion: 0.06
Nodes (8): Warehouse, WarehouseCoverage, PackageReceptionHubTest, RoutesManagerHubDistributionTest, WarehouseCoverageManagerTest, WarehouseDispatchTest, HubReceptionServiceTest, LogisticsResolutionServiceTest

### Community 72 - "Route"
Cohesion: 0.11
Nodes (3): Route, RouteService, Illuminate\Database\Eloquent\Collection

### Community 75 - "LogisticsResolutionService"
Cohesion: 0.10
Nodes (9): TrackingController, LogisticsResolutionResult, self, LogisticsResolutionService, Collection, AppLayout, GuestLayout, Illuminate\View\Component (+1 more)

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
Cohesion: 0.15
Nodes (4): RecommendationsManager, RecommendationForm, Create, Recommendation

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 90 - "Livewire\Volt\Volt"
Cohesion: 0.13
Nodes (6): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Notification, Livewire\Volt\Volt, DriversApprovalManagerNotificationTest, PasswordResetTest

### Community 92 - "User"
Cohesion: 0.03
Nodes (19): User, Illuminate\Auth\Events\Verified, Illuminate\Foundation\Auth\User, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, UsersManagerDriverTypeTest, UsersManagerSearchUrlTest, WarehouseStaffRoleTest (+11 more)

### Community 93 - "CityDistance"
Cohesion: 0.09
Nodes (8): CityDistanceManager, self, CityDistance, self, bootHasPublicId(), self, self, static

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 102 - "RemunerationsSummary"
Cohesion: 0.30
Nodes (3): Closure, RemunerationsSummary, Illuminate\Support\Collection

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), exportExcel, $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 106 - "GoogleAuthTest"
Cohesion: 0.21
Nodes (4): Laravel\Socialite\Contracts\Provider, Laravel\Socialite\Facades\Socialite, Mockery, GoogleAuthTest

### Community 107 - "RateMatrix"
Cohesion: 0.10
Nodes (4): RateMatrix, TariffService, InvalidArgumentException, TariffServiceTest

### Community 112 - "venexpress-ui/SKILL.md"
Cohesion: 0.17
Nodes (11): Avoid "AI Generated" Aesthetics, Buttons, Color Discipline, Core Design Philosophy, Layout, Professional Product Design, Spacing, Typography (+3 more)

### Community 113 - "bootstrap/app.php"
Cohesion: 0.18
Nodes (10): EnsureAccountIsApproved, EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+2 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.14
Nodes (13): cancelBuilder, clearFilters, editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}), openCollectionModal({{ $route->id }}, {{ $stop->id }}), registerCollection, $set( (+5 more)

### Community 116 - "BcvRate"
Cohesion: 0.09
Nodes (8): BcvRateManager, Carbon, BcvRate, BcvRateService, Carbon, Carbon\Carbon, BcvRateManagerTest, BcvRateServiceTest

### Community 117 - "Illuminate\Bus\Queueable"
Cohesion: 0.24
Nodes (4): AccountApproved, AccountPendingApproval, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue

### Community 118 - "package-create.blade.php"
Cohesion: 0.20
Nodes (9): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer, selectAllyPickup, selectDelivery (+1 more)

### Community 125 - "AppServiceProvider.php"
Cohesion: 0.16
Nodes (5): PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\Facades\Log, Illuminate\Support\ServiceProvider

### Community 126 - "User.php"
Cohesion: 0.22
Nodes (4): Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash, Laravel\Sanctum\HasApiTokens

### Community 130 - "RouteStop"
Cohesion: 0.16
Nodes (3): RouteStop, RouteDetailTest, ScannerLivewireTest

### Community 134 - "require"
Cohesion: 0.12
Nodes (16): require, barryvdh/laravel-dompdf, brick/math, endroid/qr-code, laravel/framework, laravel/sanctum, laravel/socialite, laravel/tinker (+8 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 136 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.07
Nodes (4): DriverRemunerationRate, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): clearHistoryFilters, livewire.client._pending-row, showHistory, showPending

### Community 141 - "Package"
Cohesion: 0.04
Nodes (12): DriverAssignment, PackageDispatch, PackageDetail, PackagePickup, PackageReception, PackageDetail, HubDistributionPhase, PublicTracking (+4 more)

### Community 142 - "Scanner"
Cohesion: 0.15
Nodes (3): FieldScanner, Scanner, LogisticsScanService

### Community 145 - "api.php"
Cohesion: 0.25
Nodes (3): DriverDashboardController, DriverIncidentController, Illuminate\Support\Facades\Route

### Community 150 - "Customer"
Cohesion: 0.07
Nodes (8): Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, RegistrationTest, ClientDashboardTest, ClientMenuAdditionsTest, ClientPendingPaymentsTest

### Community 156 - "Illuminate\Http\Request"
Cohesion: 0.23
Nodes (6): DriverPaymentResource, IncidentResource, RouteResource, RouteStopResource, Illuminate\Http\Request, Illuminate\Http\Resources\Json\JsonResource

### Community 157 - "GeocodePackageDeliveryAddress"
Cohesion: 0.27
Nodes (5): GeocodePackageDeliveryAddress, GeocodingService, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "Illuminate\Notifications\Messages\MailMessage"
Cohesion: 0.18
Nodes (5): AccountRejected, BcvRateSyncFailed, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.50
Nodes (3): create, edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "TestCase"
Cohesion: 0.06
Nodes (15): Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Queue, Livewire\Livewire, Maatwebsite\Excel\Facades\Excel, AlliesManagerDetailsTest (+7 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 179 - "ExportsSpreadsheet.php"
Cohesion: 0.36
Nodes (3): Commissions, excelDownload(), Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.50
Nodes (3): edit({{ $bcvRate->id }}), cancelEdit, syncNow

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
Cohesion: 0.07
Nodes (8): Driver, DeliveryAssignmentServiceTest, AppDownloadAccessTest, DriverDashboardTest, DriverHubTransferApiTest, DriverRouteCompletePendingPackagesTest, PayoutAccountFormTest, HasPublicIdTest

### Community 199 - "Controller"
Cohesion: 0.23
Nodes (7): GoogleAuthController, VerifyEmailController, Controller, Logout, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Session

### Community 211 - "Illuminate\Http\JsonResponse"
Cohesion: 0.22
Nodes (4): DriverAuthController, DriverPackageController, DriverScanController, Illuminate\Http\JsonResponse

### Community 216 - "Livewire\Component"
Cohesion: 0.06
Nodes (26): Dashboard, DriverRemunerationManager, HelpCenter, RoutesDashboard, Cod, DailyCashCut, HelpCenter, HelpCenter (+18 more)

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 227 - "Illuminate\Support\Str"
Cohesion: 0.07
Nodes (19): CheckProductionReadiness, SyncBcvRate, LoginForm, DriverFactory, UserFactory, WarehouseFactory, Illuminate\Auth\Events\Lockout, Illuminate\Console\Command (+11 more)

### Community 234 - "warehouses-manager.blade.php"
Cohesion: 0.29
Nodes (6): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }}), toggleCoverageActive({{ $coverage->id }}), toggleCoveragePanel({{ $warehouse->id }})

### Community 256 - "AllyFinancialService"
Cohesion: 0.07
Nodes (4): AllyFinance, AllyFinancialTransaction, AllySettlement, AllyFinancialService

### Community 258 - "PaymentOrder"
Cohesion: 0.10
Nodes (5): PaymentOrders, PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Contracts\View\View

### Community 265 - "PriceCalculator"
Cohesion: 0.13
Nodes (3): CreatePackage, PriceCalculator, Livewire\Attributes\Computed

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.50
Nodes (3): edit({{ $member->id }}), cancel, startCreate

### Community 276 - "PackageService"
Cohesion: 0.15
Nodes (3): DriverPackageResource, PackageService, Illuminate\Support\Facades\Storage

### Community 312 - "remunerations-summary.blade.php"
Cohesion: 0.50
Nodes (3): exportAlliesExcel, exportDriversExcel, sync

### Community 318 - "profile/show.blade.php"
Cohesion: 0.50
Nodes (3): profile.payout-account-form, profile.update-password-form, profile.update-profile-information-form

## Knowledge Gaps
- **272 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+267 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **117 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `AuditLog`, `DeliveryClaimFromDestinationAgencyTest`, `StaffManagerTest`, `PackageDetailCodPaymentMethodTest`, `RouteStop`, `SyncBcvRateTest`, `Incident`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Database\Eloquent\Relations\HasMany`, `.homeRouteName`, `StaffManager`, `Package`, `UsersManagerToggleStatusTest`, `UsersManager`, `VerifyAccountTest`, `PackageServiceDeliveryClaimTest`, `AllyFinanceTest`, `Customer`, `ClientMenuStaysInsideDashboardTest`, `DriverPayment`, `DriverApiFlowTest`, `Warehouse`, `RemunerationsSummaryTest`, `DriverIncidentApiTest`, `DriverAssignmentRenderTest`, `AlliesManagerLocationTest`, `DriverPackagePiiExposureTest`, `DocumentPhotoController`, `TestCase`, `AdminDocumentLinksRenderTest`, `DriverRouteClaimTest`, `AlliesManagerApprovalTest`, `Driver`, `VerifyAccount`, `Controller`, `AuditLogViewerTest`, `DriverHubDistributionTest`, `RoutesManagerListFilterTest`, `OfficeLocatorLocationTest`, `DocumentPhotoControllerTest`, `DashboardBcvRateWarningTest`, `Illuminate\Http\JsonResponse`, `Livewire\Component`, `Livewire\Volt\Volt`, `WarehousesManagerTest`, `DriverRouteReleaseTest`, `.route`, `DashboardClaimRouteTest`, `Illuminate\Support\Str`, `HubDashboardUxTest`, `RecommendationFormTest`, `DriverRouteCompleteTest`, `GoogleAuthTest`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `RouteHistoryTest`, `HubReleaseServiceTest`, `DriverHubReceptionTest`, `BcvRate`, `PackageReceptionScannerTest`, `ScannerHubOperationUxTest`, `AlliesManagerDestinationVerificationTest`, `DeliveryRouteOrderGeocodingTest`, `User.php`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.149) - this node is a cross-community bridge._
- **Why does `Package` connect `Package` to `AuditLog`, `Dashboard`, `AllyFinancialService`, `PaymentOrder`, `PackageDetailCodPaymentMethodTest`, `Incident`, `PriceCalculator`, `Packages`, `HubReceptionService`, `StaffManager`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Scanner`, `Illuminate\Database\Eloquent\Relations\HasMany`, `PackageCreateRegisteredByTest`, `api.php`, `Ally`, `PackageServiceDeliveryClaimTest`, `PackageService`, `Customer`, `Warehouse`, `GeocodePackageDeliveryAddress`, `DocumentPhotoController`, `TestCase`, `ExportsSpreadsheet.php`, `Dashboard`, `SalesCloseout`, `PackageLabelController.php`, `PendingPayments`, `Driver`, `Route`, `DriverHubDistributionTest`, `LogisticsResolutionService`, `Illuminate\Http\JsonResponse`, `PackageCreated`, `Livewire\Component`, `PackageStatusUpdated`, `PackageCreate`, `RateMatrix`, `Dashboard`, `DriverHubDistributionController`, `Dashboard`, `BcvRate`, `DriverDeliveryController`, `AppServiceProvider.php`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.135) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `PackageDetailCodPaymentMethodTest`, `DeliveryClaimFromDestinationAgencyTest`, `StaffManagerTest`, `PackageCreateDeliveryCoordinatesTest`, `RouteStop`, `AuditLog`, `VenezuelaLocationServiceCatalogTest`, `SyncBcvRateTest`, `PackageCreateRegisteredByTest`, `Package`, `PackageServiceDeliveryClaimTest`, `UsersManagerToggleStatusTest`, `VerifyAccountTest`, `AllyFinanceTest`, `SalesCloseoutTest`, `Customer`, `ClientMenuStaysInsideDashboardTest`, `DriverPayment`, `DriverApiFlowTest`, `RemunerationsSummaryTest`, `Warehouse`, `DriverIncidentApiTest`, `PackageCreateTest`, `PriceCalculatorCatalogTest`, `DriverAssignmentRenderTest`, `AlliesManagerLocationTest`, `DriverPackagePiiExposureTest`, `AdminDocumentLinksRenderTest`, `ExampleTest`, `DriverRouteClaimTest`, `PackageCreateFulfillmentModeTest`, `AlliesManagerApprovalTest`, `PackageReceptionPickupVerificationTest`, `Driver`, `PackageCreatePickupTest`, `AuditLogViewerTest`, `DriverHubDistributionTest`, `RoutesManagerListFilterTest`, `OfficeLocatorLocationTest`, `DocumentPhotoControllerTest`, `DashboardBcvRateWarningTest`, `PackageCreateEmailNotificationTest`, `Livewire\Volt\Volt`, `DriverRouteReleaseTest`, `User`, `.route`, `DashboardClaimRouteTest`, `HubDashboardUxTest`, `PackagesVisibilityTest`, `RecommendationFormTest`, `DriverRouteCompleteTest`, `GoogleAuthTest`, `RateMatrix`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `RouteHistoryTest`, `HubReleaseServiceTest`, `DriverHubReceptionTest`, `BcvRate`, `WarehousesManagerTest`, `ScannerHubOperationUxTest`, `AlliesManagerDestinationVerificationTest`, `DeliveryRouteOrderGeocodingTest`, `PackageReceptionScannerTest`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _272 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AuditLog` be split into smaller, more focused modules?**
  _Cohesion score 0.10256410256410256 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.11764705882352941 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._