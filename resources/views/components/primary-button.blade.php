<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-400 border border-transparent rounded-lg font-semibold text-sm text-blue-950 hover:bg-amber-500 focus:bg-amber-500 active:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
