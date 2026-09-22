{{--
    Visor de imagen a pantalla completa — usado por los adjuntos del
    chat de pedido (ver public/pedido-chat.blade.php y
    livewire/emprendedor/pedido-show.blade.php) para que una foto abra
    en un modal en vez de una pestaña nueva.

    Se incluye una sola vez por layout, antes de @livewireScripts,
    igual que <x-confirm-dialog />. Cualquier imagen lo dispara así:

        <img src="..." @click="$store.lightbox.open('...')" class="cursor-pointer">

    El store (Alpine.store('lightbox', ...)) se registra en
    resources/js/app.js, no aquí — mismo motivo que confirm-dialog.
--}}

<div
    x-show="$store.lightbox.show"
    x-cloak
    @keydown.escape.window="$store.lightbox.close()"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4"
>
    <button
        type="button"
        @click="$store.lightbox.close()"
        class="absolute top-4 right-4 text-white/80 hover:text-white text-3xl leading-none"
    >
        ✕
    </button>

    <div @click.outside="$store.lightbox.close()" x-show="$store.lightbox.show" x-transition class="max-w-full max-h-full">
        <img :src="$store.lightbox.src" class="max-w-[95vw] max-h-[90vh] object-contain rounded-lg" alt="">
    </div>
</div>
