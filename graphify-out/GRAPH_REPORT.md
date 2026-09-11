# Graph Report - venexpress  (2026-09-06)

## Corpus Check
- 300 files · ~271,518 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1471 nodes · 2772 edges · 228 communities (176 shown, 52 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 34 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2dde4692`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- AuthenticationTest
- AuditLog
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- Incident
- PaymentOrder
- Venexpress — Project Rules
- AppServiceProvider.php
- Illuminate\Database\Eloquent\Relations\BelongsTo
- logging.php
- Money
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
- Illuminate\Support\Facades\DB
- AllyFinancialServiceTest
- AllyFinance
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
- Illuminate\Support\Facades\Schema
- Ally
- User
- PackageService
- EmailVerificationTest.php
- require-dev
- Customer
- Livewire\Attributes\Layout
- CityDistance
- UsersManager
- ally-finance.blade.php
- static
- TariffService
- IncidentsManager
- RouteService
- routes-manager.blade.php
- PackageService.php
- package-create.blade.php
- Illuminate\Database\Eloquent\Factories\HasFactory
- Illuminate\Database\Schema\Blueprint
- PackageStatusUpdated
- web.php
- AllyStaffService
- App\Models\User
- Ally/dashboard.blade.php
- require
- setup
- LoginForm.php
- Driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- Commissions.php
- PackageServiceCodTest
- TariffServiceTest
- PaymentOrders
- RateMatrixManager
- Illuminate\Database\Migrations\Migration
- package-detail.blade.php
- Dashboard
- UserFactory.php
- Livewire\Component
- DriverAssignment
- liquidate({{ $package->id }})
- driver-payments.blade.php
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- city-distance-manager.blade.php
- DriverPayments
- driver-assignment.blade.php
- Packages
- TestCase
- config
- Driver/Dashboard.php
- PasswordResetTest
- bcv-rate-manager.blade.php
- Logout.php
- psr-4
- PriceCalculator
- price-calculator.blade.php
- keywords
- dev
- incidents-manager.blade.php
- PackageReception
- Driver/Packages.php
- PublicTracking
- .assign
- PasswordConfirmationTest
- PackageDetail
- resend

## God Nodes (most connected - your core abstractions)
1. `Package` - 154 edges
2. `User` - 118 edges
3. `Ally` - 53 edges
4. `AuditLog` - 51 edges
5. `AllyFinancialService` - 35 edges
6. `TestCase` - 30 edges
7. `PackageService` - 30 edges
8. `RouteService` - 29 edges
9. `PaymentOrder` - 29 edges
10. `TariffService` - 27 edges

## Surprising Connections (you probably didn't know these)
- `createPackage()` --references_constant--> `Package`  [EXTRACTED]
  tests/Feature/Concerns/CreatesTestPackages.php → app/Models/Package.php
- `TariffServiceTest` --references--> `TariffService`  [EXTRACTED]
  tests/Feature/TariffServiceTest.php → app/Services/TariffService.php
- `AllyFinancialSettlementTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialSettlementTest.php → app/Services/AllyFinancialService.php
- `PackageServiceCodTest` --references--> `PackageService`  [EXTRACTED]
  tests/Feature/PackageServiceCodTest.php → app/Services/PackageService.php
- `AllyFinancialServiceTest` --references--> `AllyFinancialService`  [EXTRACTED]
  tests/Feature/AllyFinancialServiceTest.php → app/Services/AllyFinancialService.php

## Import Cycles
- None detected.

## Communities (228 total, 52 thin omitted)

### Community 2 - "AuditLog"
Cohesion: 0.13
Nodes (3): AllyFinancialTransaction, AuditLog, AllyFinancialService

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

### Community 8 - "Incident"
Cohesion: 0.14
Nodes (3): Incidents, VerifyAccount, Incident

### Community 9 - "PaymentOrder"
Cohesion: 0.11
Nodes (5): PaymentOrder, PaymentReconciliationService, PaymentService, Illuminate\Support\Str, Pdo\Mysql

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider.php"
Cohesion: 0.19
Nodes (4): PackageObserver, AppServiceProvider, VoltServiceProvider, Illuminate\Support\ServiceProvider

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

### Community 17 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (22): VerifyEmailController, Controller, DriverScanController, PackageLabelController, PaymentWebhookController, EnsureAccountIsVerified, EnsureUserHasRole, Barryvdh\DomPDF\Facade\Pdf (+14 more)

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 19 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 23 - "users-manager.blade.php"
Cohesion: 0.20
Nodes (9): closeCreateModal, closeEditModal, createUser, deleteUser, openCreateModal, openEditModal({{ $user->id }}), requestDelete({{ $user->id }}), $set( (+1 more)

### Community 26 - "BcvRate"
Cohesion: 0.06
Nodes (17): CheckProductionReadiness, SyncBcvRate, TrackingController, BcvRateManager, Commissions, BcvRate, BcvRateService, DistanceApiService (+9 more)

### Community 27 - "Illuminate\Support\Facades\DB"
Cohesion: 0.17
Nodes (3): DriverPayment, DriverPaymentService, Illuminate\Support\Facades\DB

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

### Community 91 - "Ally"
Cohesion: 0.06
Nodes (10): AlliesManager, OfficeLocator, Ally, AllyUser, AllyUserService, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User, Illuminate\Support\Facades\Hash (+2 more)

### Community 92 - "User"
Cohesion: 0.07
Nodes (5): User, Illuminate\Database\Eloquent\Relations\HasOne, AllyFinancialSettlementTest, ProfileTest, UserAuthorizationTest

### Community 93 - "PackageService"
Cohesion: 0.12
Nodes (5): PackageHistory, DestinationReceptionService, HubReceptionService, PackageDispatchService, PackageService

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.25
Nodes (4): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, doctrine/dbal, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 97 - "Customer"
Cohesion: 0.08
Nodes (7): PackageCreate, PendingPayments, Customer, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, ClientDashboardTest

### Community 98 - "Livewire\Attributes\Layout"
Cohesion: 0.18
Nodes (7): Incidents, Illuminate\Support\Facades\Auth, Illuminate\Validation\Rules, Livewire\Attributes\Layout, Livewire\Attributes\Title, Livewire\WithPagination, RuntimeException

### Community 104 - "ally-finance.blade.php"
Cohesion: 0.25
Nodes (7): cancelSettlement({{ $settlement->id }}), markPaid({{ $settlement->id }}), openReversal({{ $settlement->id }}), $set(, reverseSettlement, selectAlly({{ $ally->id }}), $set(

### Community 107 - "static"
Cohesion: 0.20
Nodes (4): self, self, self, static

### Community 111 - "TariffService"
Cohesion: 0.22
Nodes (3): RateMatrix, TariffService, InvalidArgumentException

### Community 113 - "RouteService"
Cohesion: 0.05
Nodes (11): RoutesManager, App\Models\Route, Route, RouteStop, Route, RouteService, VenezuelaLocationService, Driver (+3 more)

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

### Community 116 - "PackageService.php"
Cohesion: 0.33
Nodes (5): Illuminate\Database\QueryException, Illuminate\Support\Facades\Log, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Validator, Throwable

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, $set(, saveRecipientCustomer, saveSenderCustomer

### Community 121 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.22
Nodes (7): App\Models\AuditLog, App\Models\Driver, App\Models\PackageHistory, App\Models\RouteStop, Illuminate\Database\Eloquent\Collection, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model

### Community 125 - "PackageStatusUpdated"
Cohesion: 0.22
Nodes (4): PackageStatusUpdated, WelcomeVerificationToken, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 126 - "web.php"
Cohesion: 0.15
Nodes (5): AuditLogViewer, RoutesDashboard, DailyCashCut, Illuminate\Support\Facades\Route, Illuminate\Validation\Rule

### Community 129 - "App\Models\User"
Cohesion: 0.29
Nodes (7): App\Models\Ally, App\Models\Incident, App\Models\User, IncidentService, Illuminate\Auth\Access\AuthorizationException, Illuminate\Notifications\Notifiable, Incident

### Community 134 - "require"
Cohesion: 0.20
Nodes (10): require, barryvdh/laravel-dompdf, endroid/qr-code, ext-bcmath, laravel/framework, laravel/tinker, livewire/livewire, livewire/volt (+2 more)

### Community 135 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 136 - "LoginForm.php"
Cohesion: 0.29
Nodes (6): LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\RateLimiter, Illuminate\Validation\ValidationException, Livewire\Attributes\Validate, Livewire\Form

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.08
Nodes (5): PackageDispatch, PackagePickup, PackageReception, Package, DateTimeInterface

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 156 - "UserFactory.php"
Cohesion: 0.32
Nodes (3): DriverFactory, UserFactory, Illuminate\Database\Eloquent\Factories\Factory

### Community 157 - "Livewire\Component"
Cohesion: 0.18
Nodes (5): Dashboard, Cod, CreatePackage, Livewire\Attributes\Computed, Livewire\Component

### Community 166 - "city-distance-manager.blade.php"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 175 - "TestCase"
Cohesion: 0.14
Nodes (12): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Livewire\Livewire, Livewire\Volt\Volt, PasswordUpdateTest, RegistrationTest, DriverDashboardTest (+4 more)

### Community 176 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

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
- **220 isolated node(s):** `unassign`, `API / Backend`, `Architecture Navigation`, `Authentication and Authorization`, `Changes` (+215 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **52 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `AuditLog`, `Incident`, `PaymentOrder`, `AppServiceProvider.php`, `Commissions.php`, `PackageServiceCodTest`, `Illuminate\Http\Request`, `Driver`, `BcvRate`, `Dashboard`, `Illuminate\Support\Facades\DB`, `Livewire\Component`, `DriverAssignment`, `Packages`, `TestCase`, `Driver/Dashboard.php`, `PackageReception`, `Driver/Packages.php`, `PublicTracking`, `.assign`, `PackageDetail`, `Scanner`, `Ally`, `PackageService`, `Customer`, `Livewire\Attributes\Layout`, `TariffService`, `RouteService`, `PackageService.php`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `PackageStatusUpdated`, `web.php`?**
  _High betweenness centrality (0.169) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `AllyStaffService`, `App\Models\User`, `AuthenticationTest`, `Incident`, `PackageServiceCodTest`, `Illuminate\Support\Facades\DB`, `UserFactory.php`, `Livewire\Component`, `TestCase`, `PasswordResetTest`, `AllyFinancialServiceTest`, `PasswordConfirmationTest`, `Ally`, `EmailVerificationTest.php`, `Customer`, `Livewire\Attributes\Layout`, `UsersManager`, `.isAdmin`, `Illuminate\Database\Eloquent\Factories\HasFactory`?**
  _High betweenness centrality (0.117) - this node is a cross-community bridge._
- **Why does `Money` connect `Money` to `TariffService`, `PackageService.php`, `BcvRate`, `Ally`, `PackageService`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 3 inferred relationships involving `User` (e.g. with `.definition()` and `.test_new_users_can_register()`) actually correct?**
  _`User` has 3 INFERRED edges - model-reasoned connections that need verification._
- **What connects `unassign`, `API / Backend`, `Architecture Navigation` to the rest of the system?**
  _220 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `AuditLog` be split into smaller, more focused modules?**
  _Cohesion score 0.12615384615384614 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._