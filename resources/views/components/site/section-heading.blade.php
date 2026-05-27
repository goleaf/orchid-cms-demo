@props([
    'eyebrow' => null,
    'title',
])

<div class="section-heading">
    @if ($eyebrow)
        <p>{{ $eyebrow }}</p>
    @endif

    <h2>{{ $title }}</h2>
</div>
