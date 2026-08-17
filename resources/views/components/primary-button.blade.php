<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-primary-500 to-violet-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-primary-glow hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/40 focus:ring-offset-2',
]) }}>
    {{ $slot }}
</button>
