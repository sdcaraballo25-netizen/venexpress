# Graph Report - venexpress  (2026-09-13)

## Corpus Check
- 347 files · ~288,673 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 30 file(s) not represented in the graph (top: (none) 17, .patch 7, .graphify-bak 1)

## Summary
- 1731 nodes · 3716 edges · 252 communities (70 shown, 60 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 36 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `991c2951`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AuditLog
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- package.json
- scripts
- Incident
- PaymentOrder
- Venexpress — Project Rules
- RoutesManager
- AllySettlement
- logging.php
- Money
- graphify reference: extra exports and benchmark
- README.md
- Illuminate\Http\JsonResponse
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- Warehouse
- users-manager.blade.php
- layout/navigation.blade.php
- Ally
- static
- DriverPayment
- AllyFinancialServiceTest
- Route
- RouteStop
- Driver
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
- CreatePackage.php
- EmailVerificationTest.php
- require-dev
- PackageCreate
- Livewire\Component
- CityDistance
- UsersManager
- ally-finance.blade.php
- PackageStatusUpdated
- TariffService
- venexpress-ui/SKILL.md
- LoginForm.php
- routes-manager.blade.php
- AllyUser
- package-create.blade.php
- AllyFinance
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\Support\Facades\Schema
- Illuminate\Http\Request
- web.php
- DriverRouteClaimTest
- DriverRemunerationRate
- .route
- Ally/dashboard.blade.php
- require
- 1. Principio general
- PasswordResetTest.php
- driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- BcvRate
- PackageServiceCodTest
- TariffServiceTest
- UsersManagerDriverTypeTest
- Illuminate\Database\Schema\Blueprint
- RateMatrixManager
- PackageLabelController.php
- package-detail.blade.php
- DriverApiFlowTest
- DashboardClaimRouteTest
- DriverHubDistributionController.php
- DistanceApiService
- DriverPackageResource
- Checklist final antes de operar en real — Venexpress
- GeocodePackageDeliveryAddress
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
- Dashboard
- api.php
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PriceCalculator
- price-calculator.blade.php
- DriverAssignment
- sanctum.php
- incidents-manager.blade.php
- PackageReception
- Illuminate\Database\Migrations\Migration
- OfficeLocator
- AuthenticationTest
- RuntimeException
- DriverRouteController
- PackageSecurityHashTest
- warehouses-manager.blade.php
- resend
- venexpress-laravel/SKILL.md
- WarehousesManagerTest
- autoload-dev
- extra
- assignDriver
- completeRoute({{ $route->id }})
- openAssignModal({{ $route->id }})
- startCreating
- startRoute({{ $route->id }})
- toggleStop({{ $allyId }})

## God Nodes (most connected - your core abstractions)
1. `Package` - 190 edges
2. `User` - 163 edges
3. `Route` - 102 edges
4. `Driver` - 63 edges
5. `Ally` - 62 edges
6. `AuditLog` - 58 edges
7. `TestCase` - 56 edges
8. `RouteStop` - 49 edges
9. `PackageService` - 37 edges
10. `AllyFinancialService` - 35 edges

## Surprising Connections (you probably didn't know these)
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php
- `DriverDeliveryController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/DriverDeliveryController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (252 total, 60 thin omitted)

### Community 2 - "AuditLog"
Cohesion: 0.15
Nodes (3): AllyFinancialTransaction, AuditLog, AllyFinancialService

### Community 3 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 4 - "allies-manager.blade.php"
Cohesion: 0.25
Nodes (7): activate({{ $ally->id }}), approve({{ $ally->id }}), editLocation({{ $ally->id }}), reject({{ $ally->id }}), $set(, saveLocation, suspend({{ $ally->id }})

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
Cohesion: 0.11
Nodes (4): IncidentsManager, Incidents, Incident, IncidentService

### Community 9 - "PaymentOrder"
Cohesion: 0.10
Nodes (5): PaymentOrders, PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Contracts\View\View

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.11
Nodes (17): Authentication and Authorization, Backend / API, Business Logic, Changes, Database, Frontend, General Development Rules, Generated Files (+9 more)

### Community 11 - "RoutesManager"
Cohesion: 0.11
Nodes (3): RoutesManager, VenezuelaLocationService, Illuminate\Support\Facades\File

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

### Community 17 - "Illuminate\Http\JsonResponse"
Cohesion: 0.16
Nodes (8): DriverAuthController, DriverDashboardController, Controller, DriverScanController, PaymentWebhookController, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Validator, Throwable

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 22 - "Warehouse"
Cohesion: 0.17
Nodes (4): WarehousesManager, Warehouse, RoutesManagerHubDistributionTest, DriverHubDistributionTest

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "Ally"
Cohesion: 0.14
Nodes (3): AlliesManager, Ally, Illuminate\Database\Eloquent\Relations\HasMany

### Community 26 - "static"
Cohesion: 0.18
Nodes (5): self, self, self, self, static

### Community 72 - "Route"
Cohesion: 0.12
Nodes (4): RouteDetail, Route, RouteService, Illuminate\Database\Eloquent\Collection

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

### Community 84 - "Scanner"
Cohesion: 0.16
Nodes (4): FieldScanner, Scanner, LogisticsScanService, PackageDispatchService

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 91 - "bootstrap/app.php"
Cohesion: 0.21
Nodes (8): EnsureAccountIsVerified, EnsureUserHasRole, Closure, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Symfony\Component\HttpFoundation\Response

### Community 92 - "User"
Cohesion: 0.07
Nodes (5): User, AllyStaffService, AllyFinancialSettlementTest, ProfileTest, UserAuthorizationTest

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 97 - "PackageCreate"
Cohesion: 0.09
Nodes (6): PackageCreate, Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, ClientDashboardTest

### Community 98 - "Livewire\Component"
Cohesion: 0.13
Nodes (11): Dashboard, Incidents, Illuminate\Support\Carbon, Illuminate\Support\Facades\Auth, Illuminate\Validation\Rule, Illuminate\Validation\Rules, Livewire\Attributes\Computed, Livewire\Attributes\Layout (+3 more)

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.29
Nodes (6): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }})

### Community 107 - "PackageStatusUpdated"
Cohesion: 0.14
Nodes (7): VerifyAccount, PackageStatusUpdated, MailMessage, MailMessage, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 111 - "TariffService"
Cohesion: 0.25
Nodes (3): RateMatrix, TariffService, InvalidArgumentException

### Community 112 - "venexpress-ui/SKILL.md"
Cohesion: 0.17
Nodes (11): Avoid "AI Generated" Aesthetics, Buttons, Color Discipline, Core Design Philosophy, Layout, Professional Product Design, Spacing, Typography (+3 more)

### Community 113 - "LoginForm.php"
Cohesion: 0.09
Nodes (12): LoginForm, DriverFactory, UserFactory, WarehouseFactory, Illuminate\Auth\Events\Lockout, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Str (+4 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.13
Nodes (14): cancelBuilder, cancelRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}), openCollectionModal({{ $route->id }}, {{ $stop->id }}), registerCollection (+6 more)

### Community 116 - "AllyUser"
Cohesion: 0.16
Nodes (7): AllyUser, AllyUserService, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Hash, Laravel\Sanctum\HasApiTokens

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 121 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.17
Nodes (3): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 125 - "Illuminate\Http\Request"
Cohesion: 0.19
Nodes (6): DriverIncidentController, DriverPaymentResource, IncidentResource, RouteStopResource, Illuminate\Http\Request, Illuminate\Http\Resources\Json\JsonResource

### Community 126 - "web.php"
Cohesion: 0.09
Nodes (7): AuditLogViewer, RoutesDashboard, Cod, Dashboard, Dashboard, PendingPayments, Packages

### Community 134 - "require"
Cohesion: 0.18
Nodes (11): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/sanctum, laravel/tinker, livewire/livewire (+3 more)

### Community 135 - "1. Principio general"
Cohesion: 0.22
Nodes (8): 1. Principio general, 2. Laravel, Cambio de backend, Cambio de base de datos, Cambio de flujo completo, Cambio pequeño, Objetivo, Venexpress Testing

### Community 136 - "PasswordResetTest.php"
Cohesion: 0.25
Nodes (3): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, PasswordResetTest

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.05
Nodes (8): PackageDispatch, PackageReception, PackagePickup, PackageDetail, PublicTracking, Package, DriverPaymentService, DateTimeInterface

### Community 142 - "BcvRate"
Cohesion: 0.07
Nodes (14): CheckProductionReadiness, SyncBcvRate, TrackingController, BcvRateManager, Commissions, BcvRate, BcvRateService, AppLayout (+6 more)

### Community 150 - "PackageLabelController.php"
Cohesion: 0.28
Nodes (6): PackageLabelController, Barryvdh\DomPDF\Facade\Pdf, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Writer\SvgWriter, Illuminate\Http\Response, Picqer\Barcode\BarcodeGeneratorSVG

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 157 - "DriverPackageResource"
Cohesion: 0.26
Nodes (4): DriverDeliveryController, DriverPackageController, DriverPackageResource, Illuminate\Support\Facades\Storage

### Community 158 - "Checklist final antes de operar en real — Venexpress"
Cohesion: 0.40
Nodes (4): App del repartidor (Flutter), Backend (Laravel), Checklist final antes de operar en real — Venexpress, QA (Fase 4)

### Community 159 - "GeocodePackageDeliveryAddress"
Cohesion: 0.09
Nodes (14): GeocodePackageDeliveryAddress, PackageObserver, AppServiceProvider, VoltServiceProvider, GeocodingService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable (+6 more)

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 168 - "Checklist de infraestructura para producción"
Cohesion: 0.40
Nodes (4): 1. Cron del scheduler (necesario para `bcv:sync`), 2. Worker de colas (necesario para que los correos se envíen), 3. Correo real (además de lo anterior), Checklist de infraestructura para producción

### Community 175 - "TestCase"
Cohesion: 0.10
Nodes (12): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Livewire\Livewire, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, CreatesTestPackages (+4 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 179 - "api.php"
Cohesion: 0.32
Nodes (4): VerifyEmailController, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

### Community 183 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 189 - "sanctum.php"
Cohesion: 0.40
Nodes (4): Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Foundation\Http\Middleware\ValidateCsrfToken, Laravel\Sanctum\Http\Middleware\AuthenticateSession, Laravel\Sanctum\Sanctum

### Community 209 - "RuntimeException"
Cohesion: 0.11
Nodes (9): PackageHistory, DeliveryAssignmentService, DestinationReceptionService, HubReceptionService, PackageService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\QueryException, Illuminate\Support\Facades\DB (+1 more)

### Community 217 - "warehouses-manager.blade.php"
Cohesion: 0.40
Nodes (4): cancelForm, editWarehouse({{ $warehouse->id }}), startCreating, toggleActive({{ $warehouse->id }})

### Community 224 - "venexpress-laravel/SKILL.md"
Cohesion: 0.40
Nodes (4): Core Principle, Investigation Strategy, Technology Stack, Venexpress Laravel Development

### Community 238 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 239 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **254 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+249 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 677 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **60 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `AuditLog`, `Incident`, `PaymentOrder`, `BcvRate`, `PackageServiceCodTest`, `Illuminate\Http\JsonResponse`, `PackageLabelController.php`, `Ally`, `DriverPayment`, `DriverHubDistributionController.php`, `DriverPackageResource`, `GeocodePackageDeliveryAddress`, `Packages`, `TestCase`, `Dashboard`, `DriverAssignment`, `PackageReception`, `Route`, `RuntimeException`, `Scanner`, `PackageSecurityHashTest`, `CreatePackage.php`, `Livewire\Component`, `CityDistance`, `PackageStatusUpdated`, `TariffService`, `LoginForm.php`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Illuminate\Http\Request`, `web.php`?**
  _High betweenness centrality (0.158) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `.isAdmin`, `.route`, `AuditLog`, `Incident`, `PasswordResetTest.php`, `PackageServiceCodTest`, `Illuminate\Http\JsonResponse`, `UsersManagerDriverTypeTest`, `Warehouse`, `Ally`, `DashboardClaimRouteTest`, `DriverPayment`, `DriverApiFlowTest`, `TestCase`, `AllyFinancialServiceTest`, `AuthenticationTest`, `RouteStop`, `Driver`, `RuntimeException`, `EmailVerificationTest.php`, `PackageCreate`, `Livewire\Component`, `WarehousesManagerTest`, `UsersManager`, `PackageStatusUpdated`, `LoginForm.php`, `AllyUser`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `DriverRouteClaimTest`?**
  _High betweenness centrality (0.102) - this node is a cross-community bridge._
- **Why does `Route` connect `Route` to `.route`, `Livewire\Component`, `DriverApiFlowTest`, `RouteStop`, `RoutesManager`, `Driver`, `Package`, `TestCase`, `RuntimeException`, `Dashboard`, `DriverRouteController`, `Scanner`, `Warehouse`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `DashboardClaimRouteTest`, `DriverAssignment`, `web.php`, `DriverRouteClaimTest`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _254 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AuditLog` be split into smaller, more focused modules?**
  _Cohesion score 0.14666666666666667 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.06666666666666667 - nodes in this community are weakly interconnected._