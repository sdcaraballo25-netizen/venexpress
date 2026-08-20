<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gold-400 border border-transparent rounded-lg font-semibold text-sm text-navy-900 hover:bg-gold-300 focus:bg-gold-300 active:bg-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
