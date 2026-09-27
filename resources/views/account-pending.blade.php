@php
    use App\Models\Ally;
    use App\Models\Driver;
    use App\Models\Emprendedor;

    $user = auth()->user();

    $status = match (true) {
        $user->isAliado() => $user->ally?->status,
        $user->isAliadoTaquilla() => $user->alliedAgency?->status,
        $user->isRepartidor() => $user->driver?->status,
        $user->isEmprendedor() => $user->emprendedor?->status,
        default => null,
    };

    /*
     * verification_status (Fase 1-3) es la fuente de verdad de si la
     * solicitud fue rechazada o sigue en revisión — status ya NO
     * refleja eso desde que reject() dejó de tocarlo (ver
     * DriversApprovalManager/AlliesManager/EmprendedoresApprovalManager::reject()).
     * Se sigue leyendo $status para "suspendida" (eso sí es puramente
     * operativo) y como respaldo para cuentas históricas.
     */
    $verification = match (true) {
        $user->isAliado() => $user->ally?->verification_status,
        $user->isAliadoTaquilla() => $user->alliedAgency?->verification_status,
        $user->isRepartidor() => $user->driver?->verification_status,
        $user->isEmprendedor() => $user->emprendedor?->verification_status,
        default => null,
    };

    $rejectionReason = match (true) {
        $user->isAliado() => $user->ally?->verification_rejection_reason,
        $user->isRepartidor() => $user->driver?->verification_rejection_reason,
        $user->isEmprendedor() => $user->emprendedor?->verification_rejection_reason,
        default => null,
    };

    // Ruta de "Mi Verificación" del rol correspondiente. La Taquilla
    // no tiene identidad propia que verificar (opera bajo la
    // verificación de su agencia), así que no tiene una.
    $verificationRoute = match (true) {
        $user->isAliado() => 'ally.verificacion',
        $user->isRepartidor() => 'repartidor.verificacion',
        $user->isEmprendedor() => 'emprendedor.verificacion',
        default => null,
    };

    $copy = match (true) {
        $status === Ally::STATUS_SUSPENDED => [
            'title' => 'Tu cuenta está suspendida',
            'body' => 'Tu cuenta fue suspendida por un administrador. Contacta a soporte de Venexpress para más información.',
            'badge' => 'Suspendida',
            'icon' => 'pause',
            'color' => 'slate',
            'cta' => null,
        ],
        $verification === Ally::VERIFICATION_REJECTED => [
            'title' => 'Tu solicitud fue rechazada',
            'body' => $rejectionReason
                ? "Motivo: {$rejectionReason}"
                : 'Un administrador revisó tu solicitud y no fue aprobada. Si crees que es un error, contacta a soporte de Venexpress.',
            'badge' => 'Rechazada',
            'icon' => 'x',
            'color' => 'red',
            'cta' => $verificationRoute ? ['label' => 'Revisar mi verificación', 'route' => $verificationRoute] : null,
        ],
        $verification === Ally::VERIFICATION_IN_REVIEW => [
            'title' => 'Tu información está en revisión',
            'body' => 'Ya enviaste tus datos y documentos de verificación. Un administrador los revisará pronto — te avisaremos por correo apenas haya una respuesta.',
            'badge' => 'En revisión',
            'icon' => 'clock',
            // 'sky', no 'amber': mismo color que usa
            // x-verification-status-badge para EN_REVISION en "Mi
            // Verificación" — antes se veía igual que PENDIENTE aquí,
            // aunque son estados distintos.
            'color' => 'sky',
            'cta' => $verificationRoute ? ['label' => 'Ver mi información enviada', 'route' => $verificationRoute] : null,
        ],
        $verification === Ally::VERIFICATION_PENDING => [
            'title' => 'Completa tu verificación',
            'body' => 'Todavía no has enviado tus datos y documentos de verificación. Complétalos para que un administrador pueda aprobar tu cuenta.',
            'badge' => 'Pendiente',
            'icon' => 'clock',
            'color' => 'amber',
            'cta' => $verificationRoute ? ['label' => 'Completar mi verificación', 'route' => $verificationRoute] : null,
        ],
        default => [
            'title' => 'Tu cuenta no está activa',
            'body' => 'Contacta a soporte de Venexpress para más información.',
            'badge' => 'Inactiva',
            'icon' => 'pause',
            'color' => 'slate',
            'cta' => null,
        ],
    };

    $colorClasses = [
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'badgeBg' => 'bg-amber-100', 'badgeText' => 'text-amber-700'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'badgeBg' => 'bg-sky-100', 'badgeText' => 'text-sky-700'],
        'red' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'badgeBg' => 'bg-red-100', 'badgeText' => 'text-red-700'],
        'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'badgeBg' => 'bg-slate-100', 'badgeText' => 'text-slate-600'],
    ][$copy['color']];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $copy['title'] }} — VenExpress</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body, .font-sans, .font-display { font-family: 'Poppins', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-blue-950 antialiased bg-[#F7F7F4]">

    <div class="min-h-screen flex flex-col items-center justify-center px-6 py-12">

        <div class="mb-8">
            <x-venexpress-logo size="md" />
        </div>

        <div class="w-full max-w-md bg-white rounded-3xl shadow-sm border border-[#E5E5E0] p-8 text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }}">
                @if ($copy['icon'] === 'clock')
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @elseif ($copy['icon'] === 'x')
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>

            <span class="mt-5 inline-flex items-center gap-1.5 rounded-full {{ $colorClasses['badgeBg'] }} {{ $colorClasses['badgeText'] }} px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                {{ $copy['badge'] }}
            </span>

            <h1 class="mt-4 font-display text-xl font-bold text-blue-950">
                {{ $copy['title'] }}
            </h1>

            <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                {{ $copy['body'] }}
            </p>

            @if ($copy['cta'])
                <a href="{{ route($copy['cta']['route']) }}"
                    class="mt-5 inline-flex items-center justify-center gap-2 w-full rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 transition">
                    {{ $copy['cta']['label'] }}
                </a>
            @endif

            <div class="mt-6 pt-6 border-t border-[#E5E5E0] flex items-center justify-between text-left">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-blue-950 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit"
                        class="text-sm font-semibold text-blue-700 hover:text-blue-900 transition">
                        Cerrar sesión
                    </button>
                </form>
            </div>

        </div>

        <p class="mt-8 text-xs text-gray-400">
            ¿Necesitas ayuda? Escríbenos a
            <a href="mailto:info@venexpress.com" class="font-semibold text-blue-700 hover:underline">info@venexpress.com</a>
        </p>

    </div>

</body>
</html>
