@props([
    'href',
    'variant' => 'primary',
])

<a
    {{ $attributes->class([
        'site-button',
        'site-button-secondary' => $variant === 'secondary',
    ])->merge(['href' => $href]) }}
>
    {{ $slot }}
</a>
