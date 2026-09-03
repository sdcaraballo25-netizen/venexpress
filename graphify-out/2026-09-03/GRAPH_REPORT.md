# Graph Report - venexpress  (2026-09-03)

## Corpus Check
- 243 files · ~233,922 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1091 nodes · 1794 edges · 188 communities (147 shown, 41 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 19 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `16e266eb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- Ally
- composer.json
- allies-manager.blade.php
- What You Must Do When Invoked
- devDependencies
- scripts
- bootstrap/app.php
- LoginForm.php
- Venexpress — Project Rules
- AppServiceProvider
- Illuminate\View\Component
- logging.php
- Illuminate\Database\Eloquent\Relations\BelongsTo
- graphify reference: extra exports and benchmark
- README.md
- ExampleTest
- profile.blade.php
- console.php
- verify-email.blade.php
- app.blade.php
- users-manager.blade.php
- layout/navigation.blade.php
- RuntimeException
- Route
- Livewire\WithPagination
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- Dashboard
- copilot-instructions.md
- rate-matrix-manager.blade.php
- Actualización automática de tasa BCV
- BcvRate
- User
- PackageService
- EmailVerificationTest.php
- App\Models\Package
- PackageCreate
- AllyUser
- Illuminate\Database\Migrations\Migration
- DatabaseSeeder.php
- UsersManager
- Illuminate\Database\Schema\Blueprint
- Scanner
- Ally/navigation.blade.php
- RouteService
- routes-manager.blade.php
- package-create.blade.php
- ProfileTest
- Livewire\Attributes\Layout
- CreatePackage
- Ally/dashboard.blade.php
- Livewire\Component
- TariffService
- Illuminate\Database\Eloquent\Factories\HasFactory
- Driver/dashboard.blade.php
- client/dashboard.blade.php
- Package
- Commissions
- PasswordUpdateTest.php
- Illuminate\Support\Facades\Schema
- package-detail.blade.php
- liquidate({{ $package->id }})
- markAsPaid({{ $payment->id }})
- receive
- package-dispatch.blade.php
- Admin/package-reception.blade.php
- packages.blade.php
- TariffService
- Driver
- Driver
- PackageDispatch
- PackageReception
- App\Models\Incident
- AuditLog
- PasswordResetTest
- App\Livewire\Ally\CreatePackage
- App\Livewire\Admin\RoutesManager
- App\Livewire\Ally\PackageReception
- PriceCalculator
- price-calculator.blade.php

## God Nodes (most connected - your core abstractions)
1. `Package` - 96 edges
2. `User` - 76 edges
3. `Ally` - 34 edges
4. `RouteService` - 29 edges
5. `PackageService` - 26 edges
6. `Driver` - 25 edges
7. `PackageHistory` - 23 edges
8. `RoutesManager` - 22 edges
9. `TestCase` - 20 edges
10. `PackageCreate` - 19 edges

## Surprising Connections (you probably didn't know these)
- `PasswordUpdateTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/PasswordUpdateTest.php → tests/TestCase.php
- `EmailVerificationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/EmailVerificationTest.php → tests/TestCase.php
- `PasswordResetTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/PasswordResetTest.php → tests/TestCase.php
- `ProfileTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/ProfileTest.php → tests/TestCase.php
- `UserAuthorizationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Unit/UserAuthorizationTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (188 total, 41 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.11
Nodes (11): Illuminate\Auth\Notifications\ResetPassword, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Route, Livewire\Volt\Volt, AuthenticationTest, PasswordConfirmationTest (+3 more)

### Community 3 - "composer.json"
Cohesion: 0.05
Nodes (43): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+35 more)

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
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "bootstrap/app.php"
Cohesion: 0.16
Nodes (11): DriverScanController, EnsureUserHasRole, Closure, Controller, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+3 more)

### Community 9 - "LoginForm.php"
Cohesion: 0.17
Nodes (8): LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Str, Illuminate\Validation\ValidationException, Livewire\Attributes\Validate, Livewire\Form, Pdo\Mysql

### Community 10 - "Venexpress — Project Rules"
Cohesion: 0.13
Nodes (14): API / Backend, Architecture Navigation, Authentication and Authorization, Changes, Database, Frontend, Generated Files, graphify (+6 more)

### Community 11 - "AppServiceProvider"
Cohesion: 0.28
Nodes (3): AppServiceProvider, VoltServiceProvider, Illuminate\Support\ServiceProvider

### Community 12 - "Illuminate\View\Component"
Cohesion: 0.43
Nodes (4): AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 13 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.13
Nodes (3): DriverPayment, Incident, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 15 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 16 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 18 - "profile.blade.php"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

### Community 23 - "users-manager.blade.php"
Cohesion: 0.25
Nodes (7): closeCreateModal, createUser, deleteUser, openCreateModal, requestDelete({{ $user->id }}), $set(, toggleStatus({{ $user->id }})

### Community 25 - "RuntimeException"
Cohesion: 0.20
Nodes (7): App\Models\AuditLog, PackageHistory, PackageDispatchService, App\Services\PackageService, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\File, RuntimeException

### Community 71 - "Route"
Cohesion: 0.13
Nodes (4): App\Livewire\Admin\RoutesDashboard, RoutesDashboard, Route, RouteStop

### Community 72 - "Livewire\WithPagination"
Cohesion: 0.13
Nodes (6): App\Livewire\Admin\DriverPayments, DriverPayments, IncidentsManager, Cod, Incidents, Livewire\WithPagination

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

### Community 91 - "BcvRate"
Cohesion: 0.11
Nodes (10): SyncBcvRate, App\Livewire\Admin\BcvRateManager, BcvRateManager, BcvRate, BcvRateService, Carbon, Carbon\Carbon, Illuminate\Console\Command (+2 more)

### Community 94 - "EmailVerificationTest.php"
Cohesion: 0.14
Nodes (8): VerifyEmailController, Controller, Illuminate\Auth\Events\Verified, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL, EmailVerificationTest

### Community 95 - "App\Models\Package"
Cohesion: 0.21
Nodes (4): Dashboard, PackageDetail, App\Models\Package, Package

### Community 98 - "AllyUser"
Cohesion: 0.17
Nodes (5): AllyUser, AllyUserService, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable

### Community 101 - "DatabaseSeeder.php"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 113 - "RouteService"
Cohesion: 0.10
Nodes (8): RoutesManager, App\Models\Route, App\Models\RouteStop, Route, RouteService, VenezuelaLocationService, Illuminate\Database\Eloquent\Collection, RouteStop

### Community 115 - "routes-manager.blade.php"
Cohesion: 0.11
Nodes (17): assignDriver, cancelBuilder, cancelRoute({{ $route->id }}), completeRoute({{ $route->id }}), duplicateRoute({{ $route->id }}), editRoute({{ $route->id }}), moveStopDown({{ $index }}), moveStopUp({{ $index }}) (+9 more)

### Community 118 - "package-create.blade.php"
Cohesion: 0.29
Nodes (6): openRecipientCustomerModal, openSenderCustomerModal, registerAnother, saveRecipientCustomer, saveSenderCustomer, $set(

### Community 126 - "Livewire\Attributes\Layout"
Cohesion: 0.25
Nodes (7): App\Livewire\Admin\CityDistanceManager, App\Livewire\Admin\UsersManager, Illuminate\Support\Carbon, Illuminate\Validation\Rule, Illuminate\Validation\Rules, Livewire\Attributes\Layout, Livewire\Attributes\Title

### Community 134 - "Livewire\Component"
Cohesion: 0.31
Nodes (3): Dashboard, Dashboard, Livewire\Component

### Community 135 - "TariffService"
Cohesion: 0.06
Nodes (14): CityDistanceManager, App\Livewire\Admin\RateMatrixManager, RateMatrixManager, self, CityDistance, self, self, RateMatrix (+6 more)

### Community 136 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.46
Nodes (4): App\Models\Customer, Customer, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model

### Community 139 - "client/dashboard.blade.php"
Cohesion: 0.40
Nodes (4): acceptDelivery({{ $package->id }}), cancelRejectDelivery, rejectDelivery, startRejectDelivery({{ $package->id }})

### Community 141 - "Package"
Cohesion: 0.10
Nodes (7): App\Livewire\Admin\DriverAssignment, DriverAssignment, PackagePickup, Package, DeliveryAssignmentService, Route, DateTimeInterface

### Community 144 - "PasswordUpdateTest.php"
Cohesion: 0.20
Nodes (3): AllyStaffService, Illuminate\Support\Facades\Hash, PasswordUpdateTest

### Community 151 - "package-detail.blade.php"
Cohesion: 0.50
Nodes (3): collectCod, completeDelivery, startDelivery

### Community 177 - "App\Models\Incident"
Cohesion: 0.43
Nodes (4): App\Livewire\Admin\IncidentsManager, App\Models\Incident, IncidentService, Incident

### Community 181 - "App\Livewire\Ally\CreatePackage"
Cohesion: 0.28
Nodes (3): App\Livewire\Ally\CreatePackage, OfficeLocator, Livewire\Attributes\Computed

### Community 182 - "App\Livewire\Admin\RoutesManager"
Cohesion: 0.29
Nodes (4): Logout, App\Livewire\Admin\RoutesManager, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\Session

### Community 183 - "App\Livewire\Ally\PackageReception"
Cohesion: 0.29
Nodes (3): App\Livewire\Ally\PackageReception, PackageReception, DestinationReceptionService

## Knowledge Gaps
- **195 isolated node(s):** `editLocation({{ $ally->id }})`, `approve({{ $ally->id }})`, `reject({{ $ally->id }})`, `suspend({{ $ally->id }})`, `activate({{ $ally->id }})` (+190 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **41 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Package` connect `Package` to `Livewire\Component`, `TariffService`, `bootstrap/app.php`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Commissions`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `RuntimeException`, `Driver`, `PackageDispatch`, `PackageReception`, `AuditLog`, `App\Livewire\Ally\CreatePackage`, `App\Livewire\Admin\RoutesManager`, `App\Livewire\Ally\PackageReception`, `Route`, `Livewire\WithPagination`, `Dashboard`, `PackageService`, `App\Models\Package`, `Scanner`, `RouteService`, `Livewire\Attributes\Layout`?**
  _High betweenness centrality (0.108) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `TestCase`, `AllyUser`, `DatabaseSeeder.php`, `UsersManager`, `Livewire\Component`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `TariffService`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Driver`, `PasswordUpdateTest.php`, `AuditLog`, `PasswordResetTest`, `EmailVerificationTest.php`, `ProfileTest`, `Livewire\Attributes\Layout`?**
  _High betweenness centrality (0.095) - this node is a cross-community bridge._
- **Why does `PackageCreate` connect `PackageCreate` to `Livewire\Component`, `Livewire\Attributes\Layout`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Package` (e.g. with `.monthlyBreakdown()` and `.render()`) actually correct?**
  _`Package` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `editLocation({{ $ally->id }})`, `approve({{ $ally->id }})`, `reject({{ $ally->id }})` to the rest of the system?**
  _195 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.1111111111111111 - nodes in this community are weakly interconnected._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.045454545454545456 - nodes in this community are weakly interconnected._