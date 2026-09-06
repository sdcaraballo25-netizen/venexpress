<div class="min-h-full bg-[#F8FAFC] px-4 py-6 sm:px-6 lg:px-8">

    <div class="mx-auto max-w-7xl">

        {{-- Encabezado --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                        Próximamente
                    </span>

                    <span class="text-xs font-medium text-slate-400">
                        Módulo administrativo
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Órdenes de pago
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Consulta y conciliación de pagos recibidos por VenExpress.
                </p>
            </div>

            <a
                href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>

                Volver al resumen
            </a>

        </div>


        {{-- Aviso principal --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm">

            <div class="flex flex-col gap-6 p-6 sm:flex-row sm:items-center sm:p-8">

                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                    <svg
                        class="h-8 w-8"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-bold text-slate-900">
                        Esta funcionalidad está en desarrollo
                    </h2>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Estamos preparando el sistema para recibir, verificar y conciliar
                        pagos de forma segura. El módulo estará disponible próximamente.
                    </p>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        No disponible
                    </span>
                </div>

            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-6 py-4 sm:px-8">
                <p class="text-xs leading-5 text-slate-500">
                    Las órdenes de pago podrán consultarse y gestionarse cuando se
                    complete la integración de los métodos de pago y el proceso de
                    conciliación bancaria.
                </p>
            </div>

        </div>


        {{-- Próximas funciones --}}
        <div class="grid gap-4 md:grid-cols-3">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 6v12m-4-8h8m-9 8h10a2 2 0 002-2V8a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"
                        />
                    </svg>
                </div>

                <h3 class="font-semibold text-slate-900">
                    Registro de pagos
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Creación y consulta de órdenes asociadas a paquetes, aliados y clientes.
                </p>
            </div>


            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M9 12l2 2 4-4m5.5 0a8.5 8.5 0 11-17 0 8.5 8.5 0 0117 0z"
                        />
                    </svg>
                </div>

                <h3 class="font-semibold text-slate-900">
                    Conciliación bancaria
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Verificación de referencias y confirmación de pagos recibidos.
                </p>
            </div>


            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                    <svg
                        class="h-5 w-5"
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
                </div>

                <h3 class="font-semibold text-slate-900">
                    Control financiero
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Seguimiento de pagos confirmados y movimientos financieros relacionados.
                </p>
            </div>

        </div>

    </div>

</div>
