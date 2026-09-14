@php
    use App\Models\Ally;
    use App\Models\Driver;

    $user = auth()->user();

    $status = match (true) {
        $user->isAliado() => $user->ally?->status,
        $user->isAliadoTaquilla() => $user->alliedAgency?->status,
        $user->isRepartidor() => $user->driver?->status,
        default => null,
    };

    $copy = match ($status) {
        Ally::STATUS_PENDING => [
            'title' => 'Tu cuenta está en revisión',
            'body' => 'Un administrador de Venexpress todavía no ha aprobado tu cuenta. Te avisaremos por correo apenas quede activa; mientras tanto no puedes acceder al panel.',
            'badge' => 'En revisión',
            'icon' => 'clock',
            'color' => 'amber',
        ],
        Ally::STATUS_REJECTED => [
            'title' => 'Tu solicitud fue rechazada',
            'body' => 'Un administrador revisó tu solicitud y no fue aprobada. Si crees que es un error, contacta a soporte de Venexpress.',
            'badge' => 'Rechazada',
            'icon' => 'x',
            'color' => 'red',
        ],
        Ally::STATUS_SUSPENDED => [
            'title' => 'Tu cuenta está suspendida',
            'body' => 'Tu cuenta fue suspendida por un administrador. Contacta a soporte de Venexpress para más información.',
            'badge' => 'Suspendida',
            'icon' => 'pause',
            'color' => 'slate',
        ],
        default => [
            'title' => 'Tu cuenta no está activa',
            'body' => 'Contacta a soporte de Venexpress para más información.',
            'badge' => 'Inactiva',
            'icon' => 'pause',
            'color' => 'slate',
        ],
    };

    $colorClasses = [
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'badgeBg' => 'bg-amber-100', 'badgeText' => 'text-amber-700'],
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
<body class="font-sans text-blue-950 antialiased bg-[#F3F5F7]">

    <div class="min-h-screen flex flex-col items-center justify-center px-6 py-12">

        <div class="mb-8">
            <x-venexpress-logo size="md" />
        </div>

        <div class="w-full max-w-md bg-white rounded-3xl shadow-sm border border-[#E2E8F0] p-8 text-center">

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

            <div class="mt-6 pt-6 border-t border-[#E2E8F0] flex items-center justify-between text-left">
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
