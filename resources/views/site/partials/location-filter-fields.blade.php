@php
    $filters = $filters ?? ['country' => '', 'city' => ''];
    $filterOptions = $filterOptions ?? ['countries' => collect(), 'cities' => collect()];
@endphp

<label>
    {{ tkey('website.filters.country') }}
    <select name="country" data-location-country>
        <option value="">{{ tkey('website.filters.all_countries') }}</option>
        @forelse ($filterOptions['countries'] as $option)
            <option value="{{ $option['value'] }}" @selected(($filters['country'] ?? '') === $option['value'])>
                {{ $option['label'] }}
            </option>
        @empty
        @endforelse
    </select>
</label>

<label>
    {{ tkey('website.filters.city') }}
    <select name="city" data-location-city>
        <option value="">{{ tkey('website.filters.all_cities') }}</option>
        @forelse ($filterOptions['cities'] as $option)
            <option
                value="{{ $option['value'] }}"
                data-country="{{ $option['country'] ?? '' }}"
                @selected(($filters['city'] ?? '') === $option['value'])
            >
                {{ $option['label'] }}
            </option>
        @empty
        @endforelse
    </select>
</label>
