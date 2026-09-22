{{--
    Diálogo de confirmación reutilizable que reemplaza el confirm()
    nativo del navegador (antes disparado vía wire:confirm) por un
    modal con el estilo visual de Venexpress.

    Se incluye una sola vez por layout, antes de @livewireScripts.
    Cualquier botón lo dispara así, en vez de wire:confirm:

        <button
            type="button"
            @click.prevent="$store.confirm.open({
                message: '¿Estás seguro de que deseas aprobar este aliado?',
                confirmText: 'Aprobar',
                variant: 'primary', // 'primary' | 'danger' | 'warning'
                onConfirm: () => $wire.approve({{ $ally->id }}),
            })"
        >
            Aprobar
        </button>

    onConfirm captura el $wire del componente Livewire donde vive el
    botón, así que sigue apuntando a la instancia correcta aunque el
    modal en sí viva fuera de ese componente (es global, en el layout).

    El store en sí (Alpine.store('confirm', ...)) se registra en
    resources/js/app.js, no aquí — ver el comentario ahí para el motivo
    (wire:navigate no re-ejecuta <script> insertados en el body).
--}}

<div
    x-show="$store.confirm.show"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4"
>
    <div
        x-show="$store.confirm.show"
        x-transition
        @click.outside="$store.confirm.cancel()"
        class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6"
    >
        <template x-if="$store.confirm.title">
            <h3 class="font-display text-lg font-bold text-[#111111] mb-2" x-text="$store.confirm.title"></h3>
        </template>

        <p class="text-sm text-[#4A4A45] mb-6 whitespace-pre-line" x-text="$store.confirm.message"></p>

        <div class="flex justify-end gap-2">
            <button
                type="button"
                @click="$store.confirm.cancel()"
                class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition"
            >
                <span x-text="$store.confirm.cancelText"></span>
            </button>

            <button
                type="button"
                @click="$store.confirm.confirmAction()"
                class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition"
                :class="{
                    'bg-blue-600 hover:bg-blue-700': $store.confirm.variant === 'primary',
                    'bg-red-600 hover:bg-red-700': $store.confirm.variant === 'danger',
                    'bg-amber-500 hover:bg-amber-600': $store.confirm.variant === 'warning',
                }"
            >
                <span x-text="$store.confirm.confirmText"></span>
            </button>
        </div>
    </div>
</div>
