<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white">

    {{-- Logo --}}
    <div class="flex h-20 items-center border-b border-slate-200 px-5">
        <a href="{{ auth()->user()->isAliado() ? route('ally.dashboard') : route('ally.packages.create') }}"
           wire:navigate
           class="flex items-center gap-3">

            <x-application-logo class="h-10 w-auto fill-current text-slate-800" />

            <div>
                <div class="text-base font-semibold text-slate-800">
                    Venexpress
                </div>

                <div class="text-xs text-slate-500">
                    Agencia Aliada
                </div>
            </div>
        </a>
    </div>


    {{-- Navegación --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5">

        {{-- PRINCIPAL --}}
        @if(auth()->user()->isAliado())

            <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Principal
            </div>

            <a
                href="{{ route('ally.dashboard') }}"
                wire:navigate
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                {{ request()->routeIs('ally.dashboard')
                    ? 'bg-blue-50 text-blue-900'
                    : 'text-slate-600 hover:bg-slate-50' }}"
            >
                <svg class="h-5 w-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M3 12l9-9 9 9M5 10v10h14V10"/>
                </svg>

                <span>Resumen</span>
            </a>

        @endif


        {{-- OPERACIONES --}}
        <div class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
            Operaciones
        </div>


        {{-- Registrar pedido --}}
        <a
            href="{{ route('ally.packages.create') }}"
            wire:navigate
            class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
            {{ request()->routeIs('ally.packages.create')
                ? 'bg-blue-50 text-blue-900'
                : 'text-slate-600 hover:bg-slate-50' }}"
        >
            <svg class="h-5 w-5"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>

            <span>Registrar pedido</span>
        </a>


        {{-- Recepción --}}
        <a
            href="#"
            class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
        >
            <svg class="h-5 w-5"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 7h16M4 7l2 12h12l2-12M9 7V5a3 3 0 016 0v2"/>
            </svg>

            <span>Recepción de paquetes</span>
        </a>


        {{-- Seguimiento --}}
        <a
            href="{{ route('tracking.index') }}"
            class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
        >
            <svg class="h-5 w-5"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
            </svg>

            <span>Seguimiento de envíos</span>
        </a>


        {{-- ADMINISTRACIÓN --}}
        @if(auth()->user()->isAliado())

            <div class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Administración
            </div>


            {{-- Gestión de Taquillas --}}
            <a
                href="#"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.196-2M17 20H7m10 0v-2c0-.73-.195-1.414-.536-2M7 20H2v-2a3 3 0 015.196-2M7 20v-2c0-.73.195-1.414.536-2M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>

                <span>Gestión de Taquillas</span>
            </a>


            {{-- Cobro en destino --}}
            <a
                href="#"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m8-4a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>

                <span>Cobro en destino</span>
            </a>


            {{-- Incidencias --}}
            <a
                href="#"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 9v4m0 4h.01M10.29 3.86l-7.82 13.5A2 2 0 004.2 20h15.6a2 2 0 001.73-2.64l-7.82-13.5a2 2 0 00-3.42 0z"/>
                </svg>

                <span>Incidencias</span>
            </a>

            {{-- Comisiones --}}
            <a
                href="{{ route('ally.commissions') }}"
                wire:navigate
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                {{ request()->routeIs('ally.commissions')
                    ? 'bg-blue-50 text-blue-900'
                    : 'text-slate-600 hover:bg-slate-50' }}"
            >
                <svg class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V4m0 16v-4m8-4a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>

                <span>Comisiones y saldo</span>
            </a>


            {{-- Corte de caja --}}
            <a
                href="{{ route('ally.cash-cut') }}"
                wire:navigate
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                {{ request()->routeIs('ally.cash-cut')
                    ? 'bg-blue-50 text-blue-900'
                    : 'text-slate-600 hover:bg-slate-50' }}"
            >
                <svg class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 10h18M5 10v8m4-8v8m6-8v8m4-8v8M3 18h18M5 6h14l2 4H3l2-4z"/>
                </svg>

                <span>Corte de caja</span>
            </a>


            {{-- Historial financiero --}}
            <a
                href="#"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-8"/>
                </svg>

                <span>Historial financiero</span>
            </a>


            {{-- Configuración --}}
            <a
                href="#"
                class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066 1.724 1.724 0 012.369 2.369 1.724 1.724 0 001.066 2.573 1.724 1.724 0 010 3.35 1.724 1.724 0 00-1.066 2.573 1.724 1.724 0 01-2.369 2.369 1.724 1.724 0 00-2.573 1.066 1.724 1.724 0 01-3.35 0 1.724 1.724 0 00-2.573-1.066 1.724 1.724 0 01-2.369-2.369 1.724 1.724 0 00-1.066-2.573 1.724 1.724 0 010-3.35 1.724 1.724 0 001.066-2.573 1.724 1.724 0 012.369-2.369 1.724 1.724 0 002.573-1.066z"/>
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>

                <span>Configuración</span>
            </a>

        @endif

    </nav>


    {{-- Usuario / Logout --}}
    <div class="border-t border-slate-200 p-4">

        <div class="mb-3 flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-3">

            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-900">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-slate-800">
                    {{ auth()->user()->name }}
                </div>

                <div class="truncate text-xs text-slate-500">
                    {{ auth()->user()->email }}
                </div>
            </div>

        </div>

        <button
            wire:click="logout"
            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-600"
        >
            <svg class="h-5 w-5"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/>
            </svg>

            <span>Cerrar sesión</span>
        </button>

    </div>

</aside>
