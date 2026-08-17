@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'border-gray-200 bg-gray-50/80 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 rounded-xl shadow-sm placeholder-gray-400 transition',
]) }}>
