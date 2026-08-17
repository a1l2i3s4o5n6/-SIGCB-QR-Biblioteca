<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary-custom inline-flex items-center px-5 py-2.5 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
