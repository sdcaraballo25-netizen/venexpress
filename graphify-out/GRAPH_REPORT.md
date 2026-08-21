# Graph Report - venexpress  (2026-08-21)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 474 nodes · 641 edges · 76 communities (70 shown, 6 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 4 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d4adc134`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12
- Community 13
- Community 14
- Community 15
- Community 16
- Community 17
- Community 18
- Community 19
- Community 20
- Community 21
- Community 23
- Community 24

## God Nodes (most connected - your core abstractions)
1. `User` - 41 edges
2. `TestCase` - 18 edges
3. `Package` - 18 edges
4. `BcvRate` - 16 edges
5. `CityDistance` - 14 edges
6. `TariffService` - 12 edges
7. `BcvRateManager` - 12 edges
8. `CityDistanceManager` - 12 edges
9. `RateMatrix` - 11 edges
10. `PackageService` - 10 edges

## Surprising Connections (you probably didn't know these)
- `PackageService` --references--> `TariffService`  [EXTRACTED]
  app/Services/PackageService.php → app/Services/TariffService.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php
- `EmailVerificationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/EmailVerificationTest.php → tests/TestCase.php
- `PasswordConfirmationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/PasswordConfirmationTest.php → tests/TestCase.php
- `PasswordResetTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/PasswordResetTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (76 total, 6 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.06
Nodes (20): User, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Event (+12 more)

### Community 1 - "Community 1"
Cohesion: 0.07
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 2 - "Community 2"
Cohesion: 0.09
Nodes (12): CreatePackage, Ally, Driver, Package, PackageHistory, RateMatrix, PackageService, Illuminate\Database\Eloquent\Factories\HasFactory (+4 more)

### Community 3 - "Community 3"
Cohesion: 0.05
Nodes (43): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+35 more)

### Community 4 - "Community 4"
Cohesion: 0.12
Nodes (10): BcvRateManager, Dashboard, RateMatrixManager, BcvRate, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Route, Livewire\Attributes\Layout, Livewire\Attributes\Title (+2 more)

### Community 5 - "Community 5"
Cohesion: 0.11
Nodes (9): CityDistanceManager, self, CityDistance, self, self, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash (+1 more)

### Community 6 - "Community 6"
Cohesion: 0.08
Nodes (25): autoprefixer, axios, concurrently, laravel-vite-plugin, devDependencies, autoprefixer, axios, concurrently (+17 more)

### Community 7 - "Community 7"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Community 8"
Cohesion: 0.13
Nodes (13): VerifyEmailController, Controller, DriverScanController, EnsureUserHasRole, Closure, Illuminate\Auth\Events\Verified, Illuminate\Foundation\Application, Illuminate\Foundation\Auth\EmailVerificationRequest (+5 more)

### Community 9 - "Community 9"
Cohesion: 0.11
Nodes (11): Logout, LoginForm, Illuminate\Auth\Events\Lockout, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Session, Illuminate\Support\Str, Illuminate\Validation\ValidationException (+3 more)

### Community 10 - "Community 10"
Cohesion: 0.18
Nodes (5): BcvRateService, TariffService, Carbon, Carbon\Carbon, RuntimeException

### Community 11 - "Community 11"
Cohesion: 0.28
Nodes (3): AppServiceProvider, VoltServiceProvider, Illuminate\Support\ServiceProvider

### Community 12 - "Community 12"
Cohesion: 0.43
Nodes (4): AppLayout, GuestLayout, Illuminate\View\Component, Illuminate\View\View

### Community 13 - "Community 13"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 14 - "Community 14"
Cohesion: 0.40
Nodes (4): create, delete({{ $distance->id }}), edit({{ $distance->id }}), cancelEdit

### Community 15 - "Community 15"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 16 - "Community 16"
Cohesion: 0.50
Nodes (3): delete({{ $item->id }}), edit({{ $item->id }}), cancelEdit

### Community 18 - "Community 18"
Cohesion: 0.50
Nodes (3): profile.delete-user-form, profile.update-password-form, profile.update-profile-information-form

## Knowledge Gaps
- **77 isolated node(s):** `create`, `delete({{ $distance->id }})`, `edit({{ $distance->id }})`, `cancelEdit`, `delete({{ $item->id }})` (+72 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **6 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Community 0` to `Community 2`, `Community 5`, `Community 15`?**
  _High betweenness centrality (0.128) - this node is a cross-community bridge._
- **Why does `Package` connect `Community 2` to `Community 4`?**
  _High betweenness centrality (0.035) - this node is a cross-community bridge._
- **Why does `BcvRate` connect `Community 4` to `Community 2`, `Community 10`, `Community 5`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **What connects `create`, `delete({{ $distance->id }})`, `edit({{ $distance->id }})` to the rest of the system?**
  _77 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.059395801331285206 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.06648936170212766 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.09090909090909091 - nodes in this community are weakly interconnected._