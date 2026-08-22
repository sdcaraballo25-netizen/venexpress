@props(['disabled' => false])

<div x-data="{ show: false }" class="relative flex items-center">
    <input
        :type="show ? 'text' : 'password'"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 bg-white text-blue-950 text-sm placeholder:text-gray-400 shadow-sm focus:border-blue-600 focus:ring-blue-600 disabled:bg-gray-50 disabled:text-gray-400 pr-10']) }}
    >

    <button
        type="button"
        @click="show = !show"
        tabindex="-1"
        class="absolute right-0 inset-y-0 flex w-10 items-center justify-center text-gray-400 hover:text-blue-950 focus:outline-none focus:text-blue-950"
        :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
    >
        <!-- Ojo abierto (mostrar) -->
        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-[18px] w-[18px]">
            <path d="M10 3.5c-4.14 0-7.68 2.61-9.06 6.28a.75.75 0 0 0 0 .44C2.32 13.89 5.86 16.5 10 16.5s7.68-2.61 9.06-6.28a.75.75 0 0 0 0-.44C17.68 6.11 14.14 3.5 10 3.5Zm0 10.75a4.25 4.25 0 1 1 0-8.5 4.25 4.25 0 0 1 0 8.5Z" />
            <path d="M10 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" />
        </svg>

        <!-- Ojo tachado (ocultar) -->
        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-[18px] w-[18px]" style="display: none;">
            <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.86-1.86c1.73-1.14 3.12-2.8 3.99-4.79a.75.75 0 0 0 0-.44C17.68 6.11 14.14 3.5 10 3.5c-1.6 0-3.09.39-4.39 1.08L3.28 2.22Zm4.51 4.51 1.32 1.32a2 2 0 0 1 2.34 2.34l1.32 1.32a4.25 4.25 0 0 0-4.98-4.98Z" clip-rule="evenodd" />
            <path d="m6.4 6.53-2.42-2.42C2.4 5.24 1.16 6.85.44 8.72a.75.75 0 0 0 0 .44C1.82 12.83 5.36 15.44 9.5 15.44c1.29 0 2.52-.25 3.64-.71l-1.98-1.98a4.25 4.25 0 0 1-4.9-4.9L6.4 6.53Z" />
        </svg>
    </button>
</div>
