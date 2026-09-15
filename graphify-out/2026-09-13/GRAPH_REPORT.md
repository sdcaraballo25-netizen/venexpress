# Graph Report - venexpress  (2026-09-13)

## Corpus Check
- 372 files · ~309,010 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1951 nodes · 4401 edges · 274 communities (205 shown, 69 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 41 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f021b096`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\HasMany
- ScannerHubOperationUxTest
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Incident
- setup
- Venexpress — Project Rules
- DriverHubReceptionTest
- RouteStop
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
- Livewire\WithPagination
- LoginForm.php
- DriverPayment
- Livewire\Volt\Volt
- Route
- AllyUser
- DriverRouteCompleteTest
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
- DriverRouteReleaseTest
- User
- CityDistance
- StaffManager
- require-dev
- PackageCreate
- Livewire\Component
- HubDashboardUxTest
- AuditLog
- ally-finance.blade.php
- PackageStatusUpdated
- TariffService
- venexpress-ui/SKILL.md
- bootstrap/app.php
- PackageServiceCodTest
- routes-manager.blade.php
- PasswordResetTest.php
- package-create.blade.php
- DriverDeliveryController
- PackageService
- Illuminate\Support\Facades\Schema
- PackageService.php
- Packages
- UsersManager
- PackageDetailCodPaymentMethodTest
- DeliveryClaimFromDestinationAgencyTest
- Ally/dashboard.blade.php
- require
- 1. Principio general
- Illuminate\Database\Eloquent\Relations\BelongsTo
- driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- Illuminate\View\View
- VerifyAccount
- TariffServiceTest
- BcvRate
- Illuminate\Database\Schema\Blueprint
- RateMatrixManager
- Illuminate\Console\Command
- package-detail.blade.php
- confirmOperation(
- DriverApiFlowTest
- GeocodePackageDeliveryAddress
- DriverRouteController
- .route
- Scanner
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
- PaymentOrders
- TestCase
- config
- DriverHubDistributionController
- DashboardClaimRouteTest
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PackageLabelController.php
- price-calculator.blade.php
- AllySettlement
- sanctum.php
- incidents-manager.blade.php
- HubDistributionPhase
- Illuminate\Database\Migrations\Migration
- DriverRouteClaimTest
- Dashboard
- SalesCloseout
- SalesCloseoutTest
- DriverHubTransferApiTest
- Illuminate\Http\JsonResponse
- PriceCalculator
- warehouses-manager.blade.php
- resend
- venexpress-laravel/SKILL.md
- PendingPayments
- PackageDetail
- DriverRouteActiveHubScanTest
- AllyFinance
- DeliveryRouteOrderGeocodingTest
- Driver
- PackageCreateDeliveryCoordinatesTest
- DriverDeliveryController.php
- post-create-project-cmd
- PaymentOrder
- Warehouse
- keywords
- Dashboard
- UsersManagerDriverTypeTest
- DriverAssignment
- staff-manager.blade.php
- PackageCreateRegisteredByTest
- StaffManagerTest
- .store
- WarehousesManagerTest

## God Nodes (most connected - your core abstractions)
1. `Package` - 237 edges
2. `User` - 209 edges
3. `Route` - 167 edges
4. `Driver` - 111 edges
5. `RouteStop` - 92 edges
6. `TestCase` - 86 edges
7. `Ally` - 75 edges
8. `AuditLog` - 58 edges
9. `RouteService` - 43 edges
10. `PackageService` - 38 edges

## Surprising Connections (you probably didn't know these)
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php

## Import Cycles
- None detected.

## Communities (274 total, 69 thin omitted)

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
Cohesion: 0.09
Nodes (6): IncidentsManager, Incidents, Incidents, Incident, IncidentService, Illuminate\Auth\Access\AuthorizationException

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
Nodes (3): AlliesManager, OfficeLocator, Ally

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 22 - "Illuminate\Http\Request"
Cohesion: 0.21
Nodes (6): DriverPaymentResource, IncidentResource, RouteResource, RouteStopResource, Illuminate\Http\Request, Illuminate\Http\Resources\Json\JsonResource

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "Livewire\WithPagination"
Cohesion: 0.10
Nodes (10): AuditLogViewer, DriverPayments, DriverRemunerationManager, Cod, DailyCashCut, Packages, RouteDetail, Illuminate\Support\Carbon (+2 more)

### Community 26 - "LoginForm.php"
Cohesion: 0.10
Nodes (12): LoginForm, UserFactory, WarehouseFactory, Illuminate\Auth\Events\Lockout, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Str, Illuminate\Validation\ValidationException (+4 more)

### Community 71 - "Livewire\Volt\Volt"
Cohesion: 0.09
Nodes (5): Livewire\Volt\Volt, AuthenticationTest, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest

### Community 72 - "Route"
Cohesion: 0.13
Nodes (3): Route, RouteService, Illuminate\Database\Eloquent\Collection

### Community 74 - "AllyUser"
Cohesion: 0.18
Nodes (6): AllyUser, AllyUserService, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash, Laravel\Sanctum\HasApiTokens

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
Cohesion: 0.19
Nodes (4): DestinationReceptionService, PackageDispatchService, Illuminate\Support\Facades\DB, RuntimeException

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 92 - "User"
Cohesion: 0.06
Nodes (10): User, Illuminate\Auth\Events\Verified, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, AllyFinancialServiceTest, EmailVerificationTest, createAlly() (+2 more)

### Community 93 - "CityDistance"
Cohesion: 0.11
Nodes (6): CityDistanceManager, CityDistance, self, self, self, static

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 97 - "PackageCreate"
Cohesion: 0.09
Nodes (6): PackageCreate, Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, ClientDashboardTest

### Community 98 - "Livewire\Component"
Cohesion: 0.17
Nodes (7): Dashboard, RoutesDashboard, Illuminate\Support\Facades\Auth, Illuminate\Validation\Rules, Livewire\Attributes\Layout, Livewire\Attributes\Title, Livewire\Component

### Community 102 - "AuditLog"
Cohesion: 0.09
Nodes (5): AllyFinancialTransaction, AuditLog, AllyFinancialService, DeliveryAssignmentService, AllyFinancialSettlementTest

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.29
Nodes (6): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 107 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (4): PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 111 - "TariffService"
Cohesion: 0.24
Nodes (3): RateMatrix, TariffService, InvalidArgumentException

### Community 112 - "venexpress-ui/SKILL.md"
Cohesion: 0.17
Nodes (11): Avoid "AI Generated" Aesthetics, Buttons, Color Discipline, Core Design Philosophy, Layout, Professional Product Design, Spacing, Typography (+3 more)

### Community 113 - "bootstrap/app.php"
Cohesion: 0.21
Nodes (8): EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Symfony\Component\HttpFoundation\Response

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.13
Nodes (14): cancelBuilder, cancelRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}), openCollectionModal({{ $route->id }}, {{ $stop->id }}), registerCollection (+6 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 125 - "PackageService.php"
Cohesion: 0.12
Nodes (9): PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Database\QueryException, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Validator, Illuminate\Support\ServiceProvider (+1 more)

### Community 127 - "UsersManager"
Cohesion: 0.06
Nodes (4): RoutesManager, UsersManager, VenezuelaLocationService, Illuminate\Support\Facades\File

### Community 134 - "require"
Cohesion: 0.18
Nodes (11): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+3 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 136 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.11
Nodes (4): DriverRemunerationRate, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 137 - "driver/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): claimRoute({{ $route->id }}), completeRoute, releaseRoute, startRoute

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.05
Nodes (9): PackageDispatch, PackageReception, PackageDetail, PackagePickup, PackageReception, PublicTracking, Package, DateTimeInterface (+1 more)

### Community 142 - "Illuminate\View\View"
Cohesion: 0.27
Nodes (5): TrackingController, AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 145 - "BcvRate"
Cohesion: 0.13
Nodes (5): BcvRateManager, BcvRate, self, BcvRateService, Carbon\Carbon

### Community 150 - "Illuminate\Console\Command"
Cohesion: 0.38
Nodes (3): CheckProductionReadiness, SyncBcvRate, Illuminate\Console\Command

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 154 - "GeocodePackageDeliveryAddress"
Cohesion: 0.12
Nodes (10): GeocodePackageDeliveryAddress, DistanceApiService, GeocodingService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels (+2 more)

### Community 157 - "Scanner"
Cohesion: 0.14
Nodes (4): FieldScanner, Scanner, HubReceptionService, LogisticsScanService

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "Controller"
Cohesion: 0.18
Nodes (6): DriverAuthController, VerifyEmailController, Controller, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "TestCase"
Cohesion: 0.13
Nodes (9): Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Queue, Livewire\Livewire, DeliveryAssignmentServiceTest, ExampleTest, PublicTrackingIncidentTest (+1 more)

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

### Community 208 - "SalesCloseout"
Cohesion: 0.27
Nodes (3): Commissions, SalesCloseout, Carbon

### Community 211 - "Illuminate\Http\JsonResponse"
Cohesion: 0.28
Nodes (4): DriverPackageController, DriverScanController, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Storage

### Community 216 - "PriceCalculator"
Cohesion: 0.14
Nodes (3): CreatePackage, PriceCalculator, Livewire\Attributes\Computed

### Community 217 - "warehouses-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }})

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 254 - "Driver"
Cohesion: 0.20
Nodes (4): Driver, DriverFactory, DriverDashboardTest, DriverRouteCompletePendingPackagesTest

### Community 257 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 258 - "PaymentOrder"
Cohesion: 0.11
Nodes (4): PaymentWebhookController, PaymentOrder, PaymentReconciliationService, PaymentService

### Community 259 - "Warehouse"
Cohesion: 0.18
Nodes (4): WarehousesManager, Warehouse, RoutesManagerHubDistributionTest, DriverHubDistributionTest

### Community 261 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 266 - "staff-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancel, edit({{ $member->id }}), startCreate, toggleActive({{ $member->id }})

## Knowledge Gaps
- **259 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+254 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **69 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `DriverDeliveryController.php`, `Illuminate\Database\Eloquent\Relations\HasMany`, `PaymentOrder`, `PackageDetailCodPaymentMethodTest`, `Dashboard`, `Incident`, `DriverAssignment`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `RouteStop`, `PackageCreateRegisteredByTest`, `.store`, `Illuminate\View\View`, `BcvRate`, `Illuminate\Http\Request`, `Livewire\WithPagination`, `GeocodePackageDeliveryAddress`, `DriverPayment`, `LoginForm.php`, `Scanner`, `TestCase`, `DriverHubDistributionController`, `PackageLabelController.php`, `HubDistributionPhase`, `Dashboard`, `Route`, `SalesCloseout`, `Illuminate\Http\JsonResponse`, `RuntimeException`, `PriceCalculator`, `StaffManager`, `PendingPayments`, `Livewire\Component`, `PackageDetail`, `AuditLog`, `PackageStatusUpdated`, `TariffService`, `PackageServiceCodTest`, `DriverDeliveryController`, `PackageService`, `Packages`, `PackageService.php`, `Driver`, `PackageCreateDeliveryCoordinatesTest`?**
  _High betweenness centrality (0.184) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Relations\HasMany`, `DeliveryClaimFromDestinationAgencyTest`, `PackageDetailCodPaymentMethodTest`, `Warehouse`, `ScannerHubOperationUxTest`, `UsersManagerDriverTypeTest`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Incident`, `DriverHubReceptionTest`, `RouteStop`, `StaffManagerTest`, `VerifyAccount`, `WarehousesManagerTest`, `DriverApiFlowTest`, `LoginForm.php`, `.route`, `Controller`, `TestCase`, `DashboardClaimRouteTest`, `DriverRouteClaimTest`, `Livewire\Volt\Volt`, `AllyUser`, `DriverRouteCompleteTest`, `DriverHubTransferApiTest`, `RuntimeException`, `DriverRouteReleaseTest`, `StaffManager`, `PackageCreate`, `Livewire\Component`, `HubDashboardUxTest`, `AuditLog`, `DriverRouteActiveHubScanTest`, `PackageServiceCodTest`, `PasswordResetTest.php`, `DeliveryRouteOrderGeocodingTest`, `Driver`, `UsersManager`?**
  _High betweenness centrality (0.111) - this node is a cross-community bridge._
- **Why does `Route` connect `Route` to `ScannerHubOperationUxTest`, `Warehouse`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `DriverAssignment`, `DriverHubReceptionTest`, `RouteStop`, `Illuminate\Http\Request`, `DriverApiFlowTest`, `Livewire\WithPagination`, `DriverRouteController`, `.route`, `Scanner`, `TestCase`, `DashboardClaimRouteTest`, `HubDistributionPhase`, `DriverRouteClaimTest`, `DriverRouteCompleteTest`, `DriverHubTransferApiTest`, `RuntimeException`, `DriverRouteReleaseTest`, `Livewire\Component`, `PackageDetail`, `HubDashboardUxTest`, `AuditLog`, `DriverRouteActiveHubScanTest`, `Driver`, `UsersManager`?**
  _High betweenness centrality (0.058) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _259 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Relations\HasMany` be split into smaller, more focused modules?**
  _Cohesion score 0.13333333333333333 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._