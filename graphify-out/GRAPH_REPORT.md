# Graph Report - venexpress  (2026-09-06)

## Corpus Check
- 296 files · ~270,759 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1451 nodes · 2722 edges · 210 communities (169 shown, 41 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 29 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `304852cd`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AuthenticationTest
- Illuminate\Support\Facades\Schema
- AllyFinancialService
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Commissions
- PaymentOrder
- Venexpress — Project Rules
- AppServiceProvider.php
- AllySettlement
- logging.php
- PackageDispatch
- graphify reference: extra exports and benchmark
- README.md
- Illuminate\Http\Request
- profile.blade.php
- console.php
- verify-email.blade.php
- layout.navigation
- users-manager.blade.php
- layout/navigation.blade.php
- Driver
- BcvRate
- AuditLog
- AllyFinancialServiceTest
- ProfileTest
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
- Ally
- User
- PackageService
- EmailVerificationTest.php
- require-dev
- Incident
- RuntimeException
- CityDistance
- UsersManager
- ally-finance.blade.php
- TariffService
- IncidentsManager
- Route
- routes-manager.blade.php
- Illuminate\Support\Facades\DB
- package-create.blade.php
- AllyFinancialSettlementTest
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\Database\Schema\Blueprint
- .route
- Livewire\Component
- AllyStaffService
- Ally/dashboard.blade.php
- require
- setup
- Driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- Dashboard
- PackageServiceCodTest
- AllyUser
- package-detail.blade.php
- CreatePackage.php
- Illuminate\Database\Migrations\Migration
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- OfficeLocator
- TestCase
- config
- PasswordResetTest.php
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PriceCalculator
- price-calculator.blade.php
- keywords
- dev
- incidents-manager.blade.php
- PackageDetail
- resend

## God Nodes (most connected - your core abstractions)
1. `Package` - 152 edges
2. `User` - 115 edges
3. `Ally` - 53 edges
4. `AuditLog` - 52 edges
5. `Route` - 51 edges
6. `AllyFinancialService` - 35 edges
7. `TestCase` - 32 edges
8. `PackageService` - 32 edges
9. `RouteService` - 29 edges
10. `PaymentOrder` - 29 edges

## Surprising Connections (you probably didn't know these)
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php
- `createAlly()` --references_constant--> `Ally`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Ally.php

## Import Cycles
- None detected.

## Communities (210 total, 41 thin omitted)

### Community 2 - "AllyFinancialService"
Cohesion: 0.11
Nodes (4): AllyFinance, AllyFinancialTransaction, AllyFinancialService, Livewire\Attributes\Title

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
Cohesion: 0.13
Nodes (15): scripts, post-autoload-dump, post-create-project-cmd, post-update-cmd, pre-package-uninstall, test, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, Illuminate\\Foundation\\ComposerScripts::prePackageUninstall (+7 more)

### Community 9 - "PaymentOrder"
Cohesion: 0.06
Nodes (13): PaymentOrders, LoginForm, PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Auth\Events\Lockout, Illuminate\Contracts\View\View, Illuminate\Support\Facades\RateLimiter (+5 more)

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider.php"
Cohesion: 0.18
Nodes (5): PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\Facades\Log, Illuminate\Support\ServiceProvider

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 17 - "Illuminate\Http\Request"
Cohesion: 0.06
Nodes (29): VerifyEmailController, Controller, DriverScanController, PackageLabelController, PaymentWebhookController, TrackingController, EnsureAccountIsVerified, EnsureUserHasRole (+21 more)

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 25 - "Driver"
Cohesion: 0.10
Nodes (3): Dashboard, Driver, Illuminate\Database\Eloquent\Relations\HasMany

### Community 26 - "BcvRate"
Cohesion: 0.09
Nodes (9): CheckProductionReadiness, SyncBcvRate, BcvRateManager, BcvRate, BcvRateService, DatabaseSeeder, Illuminate\Console\Command, Illuminate\Database\Console\Seeds\WithoutModelEvents (+1 more)

### Community 27 - "AuditLog"
Cohesion: 0.14
Nodes (4): DriverPayments, AuditLog, DriverPayment, DriverPaymentService

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
Cohesion: 0.20
Nodes (3): FieldScanner, Scanner, LogisticsScanService

### Community 86 - "rate-matrix-manager.blade.php"
Cohesion: 0.50
Nodes (3): cancelEditing, resetSimulation, startEditing

### Community 87 - "Actualización automática de tasa BCV"
Cohesion: 0.33
Nodes (5): Actualización automática de tasa BCV, En desarrollo local, Funcionamiento, Prueba manual, URL configurable

### Community 92 - "User"
Cohesion: 0.10
Nodes (3): User, Illuminate\Database\Eloquent\Relations\HasOne, UserAuthorizationTest

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 97 - "Incident"
Cohesion: 0.07
Nodes (7): PackageCreate, Dashboard, Incidents, Customer, Incident, Livewire\Livewire, ClientDashboardTest

### Community 98 - "RuntimeException"
Cohesion: 0.15
Nodes (6): DeliveryAssignmentService, DestinationReceptionService, HubReceptionService, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Http, RuntimeException

### Community 100 - "CityDistance"
Cohesion: 0.09
Nodes (8): CityDistanceManager, self, CityDistance, self, self, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 111 - "TariffService"
Cohesion: 0.05
Nodes (10): RateMatrixManager, RateMatrix, DistanceApiService, TariffService, Money, InvalidArgumentException, PHPUnit\Framework\TestCase, TariffServiceTest (+2 more)

### Community 113 - "Route"
Cohesion: 0.05
Nodes (8): RoutesManager, Dashboard, Route, RouteStop, RouteService, VenezuelaLocationService, Illuminate\Database\Eloquent\Collection, Illuminate\Support\Facades\File

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

### Community 116 - "Illuminate\Support\Facades\DB"
Cohesion: 0.18
Nodes (5): Illuminate\Database\QueryException, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Hash, Illuminate\Validation\Rules, Throwable

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 121 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.13
Nodes (13): App\Models\Ally, App\Models\AuditLog, App\Models\Incident, App\Models\PackageHistory, App\Models\User, IncidentService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Database\Eloquent\Factories\HasFactory (+5 more)

### Community 125 - ".route"
Cohesion: 0.15
Nodes (5): VerifyAccount, PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 126 - "Livewire\Component"
Cohesion: 0.08
Nodes (15): AuditLogViewer, RoutesDashboard, Cod, DailyCashCut, Incidents, Packages, PendingPayments, Packages (+7 more)

### Community 134 - "require"
Cohesion: 0.20
Nodes (10): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/tinker, livewire/livewire, livewire/volt (+2 more)

### Community 135 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.07
Nodes (7): DriverAssignment, PackageReception, PackagePickup, PackageReception, PublicTracking, Package, DateTimeInterface

### Community 144 - "AllyUser"
Cohesion: 0.29
Nodes (3): AllyUser, AllyUserService, Illuminate\Foundation\Auth\User

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "TestCase"
Cohesion: 0.10
Nodes (12): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Route, Livewire\Volt\Volt, PasswordConfirmationTest, PasswordUpdateTest, RegistrationTest, createAlly() (+4 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 179 - "PasswordResetTest.php"
Cohesion: 0.25
Nodes (3): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Support\Facades\Notification, PasswordResetTest

### Community 181 - "bcv-rate-manager.blade.php"
Cohesion: 0.40
Nodes (4): delete({{ $bcvRate->id }}), edit({{ $bcvRate->id }}), cancelEdit, syncNow

### Community 183 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 188 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 189 - "dev"
Cohesion: 0.67
Nodes (3): dev, Composer\\Config::disableProcessTimeout, npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others

## Knowledge Gaps
- **219 isolated node(s):** `cancelResolution`, `confirmResolution`, `API / Backend`, `Architecture Navigation`, `Authentication and Authorization` (+214 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **41 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `AllyFinancialService`, `Commissions`, `PaymentOrder`, `AppServiceProvider.php`, `PackageDispatch`, `Dashboard`, `PackageServiceCodTest`, `Illuminate\Http\Request`, `Driver`, `AuditLog`, `CreatePackage.php`, `TestCase`, `PackageDetail`, `Scanner`, `PackageService`, `Incident`, `RuntimeException`, `TariffService`, `Route`, `Illuminate\Support\Facades\DB`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `.route`, `Livewire\Component`?**
  _High betweenness centrality (0.148) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `AllyStaffService`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AuthenticationTest`, `Incident`, `CityDistance`, `UsersManager`, `AllyFinancialServiceTest`, `ProfileTest`, `TestCase`, `AllyUser`, `PackageServiceCodTest`, `PasswordResetTest.php`, `Illuminate\Support\Facades\DB`, `AllyFinancialSettlementTest`, `Driver`, `BcvRate`, `.route`, `EmailVerificationTest.php`?**
  _High betweenness centrality (0.094) - this node is a cross-community bridge._
- **Why does `Ally` connect `Ally` to `AllyStaffService`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `AllyFinancialService`, `UsersManager`, `OfficeLocator`, `PaymentOrder`, `TestCase`, `AllyUser`, `Route`, `Illuminate\Support\Facades\DB`, `Driver`, `BcvRate`, `AuditLog`, `User`, `PackageService`, `Livewire\Component`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **What connects `cancelResolution`, `confirmResolution`, `API / Backend` to the rest of the system?**
  _219 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AllyFinancialService` be split into smaller, more focused modules?**
  _Cohesion score 0.11182795698924732 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `What You Must Do When Invoked` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._