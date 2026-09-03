```blade
<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Panel Administrativo' }} — Venexpress</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        .font-display {
            font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
        }

        .font-tracking {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-[#F3F5F7] text-[#0B1220] antialiased">

    <div class="min-h-screen flex">

        {{-- ============================================================
             SIDEBAR
        ============================================================= --}}

        <aside
            class="w-64 border-r border-[#E2E8F0] px-5 py-8 flex flex-col justify-between hidden md:flex bg-white h-screen sticky top-0"
        >

            <div>

                {{-- Logo --}}
                <div class="flex items-center gap-3 px-2 mb-10">

                    <div class="bg-blue-900 text-white p-2 rounded-xl">

                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>

                    </div>

                    <span class="font-display font-bold text-xl text-[#0F172A]">
                        Venexpress
                    </span>

                </div>


                {{-- ====================================================
                     NAVEGACIÓN
                ===================================================== --}}

                <nav class="space-y-1">

                    {{-- PRINCIPAL --}}
                    <p class="px-2 text-xs font-semibold text-[#94A3B8] uppercase tracking-wider mb-3">
                        Principal
                    </p>


                    {{-- Dashboard --}}
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.dashboard')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-blue-700' : '' }}"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                            />
                        </svg>

                        Resumen

                    </a>


                    {{-- Aliados --}}
                    <a
                        href="{{ route('admin.allies') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.allies')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            />
                        </svg>

                        Aliados

                    </a>


                    {{-- Gestión de tarifas --}}
                    <a
                        href="{{ route('admin.rate-matrices') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.rate-matrices')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5 {{ request()->routeIs('admin.rate-matrices') ? 'text-blue-700' : '' }}"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 0 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>

                        Gestión de tarifas

                    </a>


                    {{-- Recepción --}}
                    <a
                        href="{{ route('admin.packages.reception') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.packages.reception')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 7h18M5 7v12h14V7M8 11h8M8 15h5"
                            />
                        </svg>

                        Recepción de paquetes

                    </a>


                    {{-- Asignar reparto --}}
                    <a
                        href="{{ route('admin.packages.assignment') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.packages.assignment')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                        Asignar a reparto

                    </a>


                    {{-- Despacho --}}
                    <a
                        href="{{ route('admin.packages.dispatch') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.packages.dispatch')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 12h13m0 0-4-4m4 4-4 4M17 5h4v14h-4"
                            />
                        </svg>

                        Despacho a tránsito

                    </a>


                    {{-- Control de rutas --}}
                    <a
                        href="{{ route('admin.routes') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.routes')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7l6-2.572 5.447 2.724A1 1 0 0121 8.053v10.764a1 1 0 01-1.447.894L15 17l-6 2.572zM9 7v13M15 4v13"
                            />
                        </svg>

                        Control de rutas

                    </a>


                    {{-- Dashboard de rutas --}}
                    <a
                        href="{{ route('admin.routes.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.routes.dashboard')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6m-9 0h10a2 2 0 002-2V9.5a1 1 0 00-.4-.8l-5-3.75a1 1 0 00-1.2 0l-5 3.75a1 1 0 00-.4.8V17a2 2 0 002 2z"
                            />
                        </svg>

                        Dashboard de rutas

                    </a>


                    {{-- =================================================
                         ADMINISTRACIÓN
                    ================================================== --}}

                    <p class="px-2 text-xs font-semibold text-[#94A3B8] uppercase tracking-wider mb-3 mt-6">
                        Administración
                    </p>


                    {{-- Gestión de usuarios --}}
                    <a
                        href="{{ route('admin.users') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.users')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                            />
                        </svg>

                        Gestión de usuarios

                    </a>


                    {{-- =================================================
                         FINANZAS DE ALIADOS
                    ================================================== --}}

                    <a
                        href="{{ route('admin.ally-finance') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.ally-finance')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5 {{ request()->routeIs('admin.ally-finance') ? 'text-blue-700' : '' }}"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H7"
                            />
                        </svg>

                        Finanzas de aliados

                    </a>


                    {{-- Remuneraciones --}}
                    <a
                        href="{{ route('admin.driver-payments') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.driver-payments')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>

                        Remuneraciones

                    </a>


                    {{-- Incidencias --}}
                    <a
                        href="{{ route('admin.incidents') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('admin.incidents')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16a2 2 0 001.73 3z"
                            />
                        </svg>

                        Incidencias

                    </a>


                    {{-- Auditoría --}}
                    @if (Auth::user()?->isAdminPrincipal())

                        <a
                            href="{{ route('admin.audit-log') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                            {{ request()->routeIs('admin.audit-log')
                                ? 'bg-blue-50 text-blue-900'
                                : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
                        >

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"
                                />
                            </svg>

                            Bitácora de auditoría

                        </a>

                    @endif

                </nav>

            </div>


            {{-- ============================================================
                 PERFIL ADMINISTRADOR
            ============================================================= --}}

            <div class="mt-auto pt-8 border-t border-[#E2E8F0]">

                <div class="flex items-center gap-3 px-2">

                    <div class="w-10 h-10 rounded-full bg-blue-900 flex items-center justify-center text-white font-bold uppercase">

                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}

                    </div>

                    <div class="overflow-hidden">

                        <p class="text-sm font-semibold text-[#0F172A] truncate">

                            {{ Auth::user()->name ?? 'Administrador' }}

                        </p>

                        <p class="text-xs text-[#64748B] truncate">

                            {{ Auth::user()->email ?? 'admin@venexpress.com' }}

                        </p>

                    </div>

                </div>


                {{-- Cerrar sesión --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="w-full mt-4"
                >

                    @csrf

                    <button
                        type="submit"
                        class="w-full text-left px-2 text-sm text-red-500 font-medium hover:text-red-700 transition-colors"
                    >
                        Cerrar Sesión
                    </button>

                </form>

            </div>

        </aside>


        {{-- ============================================================
             OVERLAY MÓVIL
        ============================================================= --}}

        <div
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-20 lg:hidden"
            x-cloak
        ></div>


        {{-- ============================================================
             CONTENIDO PRINCIPAL
        ============================================================= --}}

        <div class="flex-1 lg:ml-0 min-w-0">

            {{-- Header --}}
            <header
                class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-4 lg:px-8"
            >

                {{-- Botón menú móvil --}}
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden text-[#0B1220] text-xl"
                    type="button"
                    aria-label="Abrir menú"
                >
                    ☰
                </button>


                <div class="flex items-center gap-4 ml-auto">

                    <span class="text-sm text-[#64748B]">

                        {{ auth()->user()->name ?? 'Admin' }}

                    </span>


                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="text-sm font-medium text-[#64748B] hover:text-[#FF6A1A] transition-colors"
                        >
                            Salir
                        </button>

                    </form>

                </div>

            </header>


            {{-- Contenido --}}
            <main class="p-4 lg:p-8 max-w-7xl">

                {{ $slot ?? '' }}

                @yield('content')

            </main>

        </div>

    </div>


    @livewireScripts

</body>

</html>
```
