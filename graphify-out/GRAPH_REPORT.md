# Graph Report - venexpress  (2026-09-18)

## Corpus Check
- 456 files · ~355,481 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2616 nodes · 6462 edges · 330 communities (229 shown, 101 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 74 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `59e5183a`
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
- PackageReception
- WarehouseCoverage
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
- PackageCreate
- Livewire\WithPagination
- VenezuelaLocationService
- HubDashboardUxTest
- audit-log-viewer.blade.php
- PackageReceptionHubTest
- Illuminate\Database\Schema\Blueprint
- ally-finance.blade.php
- Illuminate\Support\Facades\Schema
- RateMatrix
- Dashboard
- PackageHistory
- venexpress-ui/SKILL.md
- bootstrap/app.php
- Dashboard
- routes-manager.blade.php
- BcvRate
- package-create.blade.php
- DriverDeliveryController
- ScannerHubOperationUxTest
- Illuminate\Database\Migrations\Migration
- AppServiceProvider.php
- Illuminate\Database\Eloquent\Factories\Factory
- RoutesManagerStopFilterTest
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- RouteStop
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Relations\BelongsTo
- driver/dashboard.blade.php
- Illuminate\Database\Eloquent\Relations\HasMany
- client/dashboard.blade.php
- StaffManager
- Package
- Scanner
- OfficeLocator
- UsersManager
- LoginForm
- commissions.blade.php
- RateMatrixManager
- Customer
- package-detail.blade.php
- confirmOperation(
- DriverApiFlowTest.php
- PackageServiceCodTest
- DriverPackageResource
- GeocodePackageDeliveryAddress
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
- DocumentPhotoController
- TestCase
- config
- DriverRouteClaimTest
- WarehouseCoverageManagerTest
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
- Controller
- AuditLogViewer
- RoutesManagerListFilterTest
- OfficeLocatorLocationTest
- .route
- HubReceptionService
- Illuminate\Http\Request
- Illuminate\View\View
- Livewire\Component
- WarehouseDispatchTest
- resend
- venexpress-laravel/SKILL.md
- DistanceApiService
- DashboardClaimRouteTest
- LoginForm.php
- PackagesVisibilityTest
- RecommendationFormTest
- DriverRouteCompleteTest
- warehouses-manager.blade.php
- RoutesManagerMultistateTest
- DriverRouteActiveHubScanTest
- RouteHistoryTest
- DriverHubDistributionController
- DriverHubReceptionTest
- drivers-approval-manager.blade.php
- WarehousesManagerTest
- AlliesManagerDestinationVerificationTest
- HubDistributionPhase
- DeliveryRouteOrderGeocodingTest
- PackageReceptionScannerTest
- PackageCreateDeliveryCoordinatesTest
- AllyFinancialService
- Dashboard
- PaymentOrder
- PaymentOrders
- VenezuelaLocationServiceCatalogTest
- PriceCalculator
- staff-manager.blade.php
- PackageCreateRegisteredByTest
- PackageServiceDeliveryClaimTest
- DriverAssignment
- Illuminate\Console\Command
- sales-closeout.blade.php
- AllyFinanceTest
- PackageService
- PackageDetail
- archive({{ $recommendation->id }})
- assignToDriver({{ $route->id }})
- PackageCreateTest
- PriceCalculatorCatalogTest
- DriverAssignmentRenderTest
- AlliesManagerLocationTest
- AllyStaffService
- AdminDocumentLinksRenderTest
- ExampleTest
- RoutesManagerHubDistributionTest
- autoload-dev
- post-autoload-dump
- excel.php

## God Nodes (most connected - your core abstractions)
1. `Package` - 288 edges
2. `User` - 287 edges
3. `Route` - 206 edges
4. `TestCase` - 166 edges
5. `Driver` - 163 edges
6. `Warehouse` - 157 edges
7. `Ally` - 106 edges
8. `RouteStop` - 97 edges
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

## Communities (330 total, 101 thin omitted)

### Community 0 - "AuditLog"
Cohesion: 0.08
Nodes (10): AuditLog, DeliveryAssignmentService, DestinationReceptionService, IncidentService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\QueryException, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\DB (+2 more)

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
Cohesion: 0.12
Nodes (3): IncidentsManager, Incidents, Incident

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
Cohesion: 0.15
Nodes (4): Money, PHPUnit\Framework\TestCase, ExampleTest, MoneyTest

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 17 - "Ally"
Cohesion: 0.16
Nodes (3): AlliesManager, Ally, createPackage()

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 22 - "DriverPayments"
Cohesion: 0.12
Nodes (7): DriverPayments, Commissions, SalesCloseout, excelDownload(), Illuminate\Database\Eloquent\Builder, Illuminate\Support\Carbon, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "DriverPayment"
Cohesion: 0.22
Nodes (3): DriverPayment, DriverPaymentService, DriverPaymentsTest

### Community 72 - "Route"
Cohesion: 0.12
Nodes (3): Route, RouteService, Illuminate\Database\Eloquent\Collection

### Community 75 - "LogisticsResolutionService"
Cohesion: 0.13
Nodes (5): TrackingController, HubReleaseService, LogisticsResolutionService, Collection, PackageDispatchService

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
Cohesion: 0.25
Nodes (3): RecommendationsManager, RecommendationForm, Recommendation

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 90 - "Livewire\Volt\Volt"
Cohesion: 0.09
Nodes (8): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Storage, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordResetTest, PasswordUpdateTest

### Community 92 - "User"
Cohesion: 0.05
Nodes (13): User, DriverFactory, Illuminate\Foundation\Auth\User, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, UsersManagerSearchUrlTest, WarehouseStaffRoleTest, AllyFinancialSettlementTest (+5 more)

### Community 93 - "CityDistance"
Cohesion: 0.09
Nodes (8): CityDistanceManager, self, CityDistance, self, self, self, static, CityDistanceTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 98 - "Livewire\WithPagination"
Cohesion: 0.08
Nodes (10): DriverRemunerationManager, DriversApprovalManager, Cod, DailyCashCut, Incidents, Packages, Packages, RouteHistory (+2 more)

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), exportExcel, $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 107 - "RateMatrix"
Cohesion: 0.12
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
Cohesion: 0.12
Nodes (7): BcvRateManager, Carbon, BcvRate, BcvRateService, Carbon, Carbon\Carbon, BcvRateManagerTest

### Community 118 - "package-create.blade.php"
Cohesion: 0.20
Nodes (9): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer, selectAllyPickup, selectDelivery (+1 more)

### Community 125 - "AppServiceProvider.php"
Cohesion: 0.16
Nodes (5): PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\Facades\Log, Illuminate\Support\ServiceProvider

### Community 126 - "Illuminate\Database\Eloquent\Factories\Factory"
Cohesion: 0.24
Nodes (4): UserFactory, WarehouseFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash

### Community 130 - "RouteStop"
Cohesion: 0.14
Nodes (3): RouteStop, RouteDetailTest, ScannerLivewireTest

### Community 134 - "require"
Cohesion: 0.17
Nodes (12): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+4 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 136 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.07
Nodes (4): DriverRemunerationRate, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 137 - "driver/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): claimRoute({{ $route->id }}), completeRoute, releaseRoute, startRoute

### Community 138 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.09
Nodes (4): Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), clearHistoryFilters, showHistory, showPending

### Community 141 - "Package"
Cohesion: 0.05
Nodes (8): PackageDispatch, PackageDetail, PackagePickup, PackageReception, PublicTracking, Package, DateTimeInterface, PackageSecurityHashTest

### Community 142 - "Scanner"
Cohesion: 0.16
Nodes (3): FieldScanner, Scanner, LogisticsScanService

### Community 150 - "Customer"
Cohesion: 0.12
Nodes (7): PendingPayments, Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, ClientDashboardTest, ClientPendingPaymentsTest

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 156 - "DriverPackageResource"
Cohesion: 0.18
Nodes (6): DriverDashboardController, DriverPackageResource, DriverPaymentResource, RouteResource, RouteStopResource, Illuminate\Http\Resources\Json\JsonResource

### Community 157 - "GeocodePackageDeliveryAddress"
Cohesion: 0.24
Nodes (7): GeocodePackageDeliveryAddress, GeocodingService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels

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
Cohesion: 0.10
Nodes (7): Driver, DeliveryAssignmentServiceTest, UsersManagerDriverTypeTest, AppDownloadAccessTest, DriverDashboardTest, DriverHubTransferApiTest, DriverRouteCompletePendingPackagesTest

### Community 199 - "Controller"
Cohesion: 0.15
Nodes (8): DriverIncidentController, VerifyEmailController, Controller, IncidentResource, Illuminate\Auth\Events\Verified, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 209 - ".route"
Cohesion: 0.07
Nodes (6): VerifyAccount, SalesCloseoutTest, RegistrationTest, VerifyAccountTest, DocumentPhotoControllerTest, PublicTrackingHubDestinationLabelTest

### Community 210 - "HubReceptionService"
Cohesion: 0.18
Nodes (3): HubReceptionService, LogisticsResolutionResult, self

### Community 211 - "Illuminate\Http\Request"
Cohesion: 0.23
Nodes (6): DriverAuthController, DriverPackageController, DriverScanController, Illuminate\Http\JsonResponse, Illuminate\Http\Request, Illuminate\Validation\ValidationException

### Community 212 - "Illuminate\View\View"
Cohesion: 0.43
Nodes (4): AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 216 - "Livewire\Component"
Cohesion: 0.10
Nodes (14): Dashboard, HelpCenter, RoutesDashboard, Dashboard, HelpCenter, HelpCenter, AppDownload, HelpCenter (+6 more)

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 227 - "LoginForm.php"
Cohesion: 0.17
Nodes (8): Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\Password, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Str, Livewire\Attributes\Locked, Livewire\Attributes\Validate, Livewire\Form, Pdo\Mysql

### Community 234 - "warehouses-manager.blade.php"
Cohesion: 0.29
Nodes (6): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }}), toggleCoverageActive({{ $coverage->id }}), toggleCoveragePanel({{ $warehouse->id }})

### Community 244 - "drivers-approval-manager.blade.php"
Cohesion: 0.40
Nodes (4): activate({{ $driver->id }}), approve({{ $driver->id }}), reject({{ $driver->id }}), suspend({{ $driver->id }})

### Community 256 - "AllyFinancialService"
Cohesion: 0.07
Nodes (4): AllyFinance, AllyFinancialTransaction, AllySettlement, AllyFinancialService

### Community 258 - "PaymentOrder"
Cohesion: 0.11
Nodes (6): PaymentWebhookController, PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Support\Facades\Validator, Throwable

### Community 265 - "PriceCalculator"
Cohesion: 0.13
Nodes (3): CreatePackage, PriceCalculator, Livewire\Attributes\Computed

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancel, edit({{ $member->id }}), startCreate, toggleActive({{ $member->id }})

### Community 272 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 322 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 323 - "post-autoload-dump"
Cohesion: 0.67
Nodes (3): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan package:discover --ansi

## Knowledge Gaps
- **280 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+275 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **101 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `AuditLog`, `Dashboard`, `RouteStop`, `AllyFinancialService`, `PaymentOrder`, `DeliveryClaimFromDestinationAgencyTest`, `PackageDetailCodPaymentMethodTest`, `Incident`, `PriceCalculator`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `PackageReception`, `StaffManager`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Scanner`, `DriverAssignment`, `PackageCreateRegisteredByTest`, `Ally`, `PackageServiceDeliveryClaimTest`, `WarehouseCoverage`, `PackageService`, `DriverPayments`, `Customer`, `DriverPayment`, `PackageServiceCodTest`, `DriverPackageResource`, `GeocodePackageDeliveryAddress`, `PackageDetail`, `PackageStatusUpdated`, `DocumentPhotoController`, `TestCase`, `PackageLabelController.php`, `Driver`, `Controller`, `Route`, `DriverHubDistributionTest`, `LogisticsResolutionService`, `HubReceptionService`, `Illuminate\Http\Request`, `Livewire\Component`, `PackageCreate`, `Livewire\WithPagination`, `RateMatrix`, `Dashboard`, `PackageHistory`, `DriverHubDistributionController`, `Dashboard`, `DriverDeliveryController`, `HubDistributionPhase`, `AppServiceProvider.php`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.155) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `AuditLog`, `DeliveryClaimFromDestinationAgencyTest`, `StaffManagerTest`, `PackageDetailCodPaymentMethodTest`, `RouteStop`, `Incident`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Database\Eloquent\Relations\HasMany`, `PackageServiceDeliveryClaimTest`, `UsersManager`, `LoginForm`, `AllyFinanceTest`, `Customer`, `DriverPayment`, `DriverApiFlowTest.php`, `PackageServiceCodTest`, `DriverAssignmentRenderTest`, `AlliesManagerLocationTest`, `AllyStaffService`, `DocumentPhotoController`, `TestCase`, `AdminDocumentLinksRenderTest`, `DriverRouteClaimTest`, `WarehouseCoverageManagerTest`, `RoutesManagerHubDistributionTest`, `Driver`, `AllyFinancialServiceTest`, `AuditLogViewerTest`, `DriverHubDistributionTest`, `RoutesManagerListFilterTest`, `OfficeLocatorLocationTest`, `.route`, `Illuminate\Http\Request`, `Livewire\Component`, `WarehouseDispatchTest`, `Livewire\Volt\Volt`, `WarehousesManagerTest`, `DriverRouteReleaseTest`, `Livewire\WithPagination`, `LoginForm.php`, `DashboardClaimRouteTest`, `HubDashboardUxTest`, `PackageReceptionHubTest`, `RecommendationFormTest`, `DriverRouteCompleteTest`, `RoutesManagerMultistateTest`, `DriverRouteActiveHubScanTest`, `RouteHistoryTest`, `PackageHistory`, `DriverHubReceptionTest`, `BcvRate`, `PackageReceptionScannerTest`, `ScannerHubOperationUxTest`, `AlliesManagerDestinationVerificationTest`, `DeliveryRouteOrderGeocodingTest`, `Illuminate\Database\Eloquent\Factories\Factory`, `RoutesManagerStopFilterTest`?**
  _High betweenness centrality (0.082) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `AllyFinancialService`, `AuditLog`, `RouteStop`, `Incident`, `PriceCalculator`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Database\Eloquent\Relations\HasMany`, `StaffManager`, `Package`, `OfficeLocator`, `UsersManager`, `PackageService`, `DriverPayments`, `Customer`, `RoutesManager`, `AllyStaffService`, `DocumentPhotoController`, `TestCase`, `DriverRouteClaimTest`, `PackageReceptionPickupVerificationTest`, `Driver`, `LogisticsResolutionService`, `OfficeLocatorLocationTest`, `.route`, `HubReceptionService`, `Livewire\Component`, `Livewire\Volt\Volt`, `DriverRouteReleaseTest`, `User`, `PackageCreate`, `Livewire\WithPagination`, `DashboardClaimRouteTest`, `DriverRouteCompleteTest`, `PackageHistory`, `bootstrap/app.php`, `DriverHubReceptionTest`, `Illuminate\Database\Eloquent\Factories\Factory`?**
  _High betweenness centrality (0.057) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _280 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AuditLog` be split into smaller, more focused modules?**
  _Cohesion score 0.0821256038647343 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._