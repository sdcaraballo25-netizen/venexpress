<div class="max-w-2xl mx-auto text-center py-6">

    <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
        App de repartidor
    </span>
    <h1 class="text-3xl font-display font-extrabold text-[#111111]">Descarga la app de Venexpress</h1>
    <p class="text-[#6B6B66] mt-3 max-w-xl mx-auto">
        Descarga la app para gestionar tus rutas y entregas desde tu celular.
    </p>

    <div class="mt-10 bg-white border border-[#E5E5E0] rounded-2xl p-8 shadow-sm">
        @if ($apkAvailable)
            <a href="{{ $apkUrl }}"
                class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white font-semibold text-sm px-8 py-3.5 rounded-lg transition">
                <i class="fa-solid fa-download"></i>
                Descargar APK para Android
            </a>
            <p class="text-xs text-[#B8B8B2] mt-4">
                Al instalar, tu dispositivo puede pedirte permitir "orígenes desconocidos".
            </p>
        @else
            <p class="text-[#6B6B66] text-sm">
                La descarga todavía no está disponible. Vuelve pronto o contacta a soporte para más información.
            </p>
        @endif
    </div>

</div>
