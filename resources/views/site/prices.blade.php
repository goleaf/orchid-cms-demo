@extends('site.layout')

@section('content')
    @php
        $filters = $filters ?? [
            'course' => '',
            'category' => '',
            'format' => '',
            'duration' => '',
            'theory_min' => '',
            'practice_min' => '',
            'price_min' => '',
            'price_max' => '',
            'country' => '',
            'city' => '',
        ];
        $filterOptions = $filterOptions ?? [
            'courses' => collect(),
            'categories' => collect(),
            'formats' => collect(),
            'durations' => collect(),
            'countries' => collect(),
            'cities' => collect(),
        ];
        $courseContextQuery = $courseContextQuery ?? [];
    @endphp

    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.prices.kicker') }}</p>
                <h1>{{ tkey('website.prices.title') }}</h1>
                <p class="lead">{{ tkey('website.prices.lead') }}</p>
            </div>
        </section>

        <section class="section" id="pricing-results">
            <div class="section-inner">
                <form class="filter-panel" method="GET" action="{{ route('website.pricing') }}#pricing-results" data-location-filter>
                    <div class="filter-head">
                        <div>
                            <p class="kicker">{{ tkey('website.prices.filters.kicker') }}</p>
                            <h2>{{ tkey('website.prices.filters.title') }}</h2>
                        </div>
                        <p class="meta">{{ tkey('website.prices.filters.subtitle') }}</p>
                    </div>

                    <div class="filter-grid">
                        @include('site.partials.location-filter-fields', compact('filters', 'filterOptions'))

                        <label>
                            {{ tkey('website.pricing.fields.course') }}
                            <select name="course">
                                <option value="">{{ tkey('website.prices.filters.all_courses') }}</option>
                                @forelse ($filterOptions['courses'] as $program)
                                    <option value="{{ $program->slug }}" @selected(($filters['course'] ?? '') === $program->slug)>
                                        {{ $program->displayTitle() }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </label>

                        <label>
                            {{ tkey('website.filters.category') }}
                            <select name="category">
                                <option value="">{{ tkey('website.filters.all_categories') }}</option>
                                @forelse ($filterOptions['categories'] as $category)
                                    <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>
                                        {{ $category->displayName() }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </label>

                        <label>
                            {{ tkey('website.prices.columns.format') }}
                            <select name="format">
                                <option value="">{{ tkey('website.prices.filters.all_formats') }}</option>
                                @forelse ($filterOptions['formats'] as $option)
                                    <option value="{{ $option['value'] }}" @selected(($filters['format'] ?? '') === $option['value'])>
                                        {{ $option['label'] }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </label>

                        <label>
                            {{ tkey('website.prices.columns.duration') }}
                            <select name="duration">
                                <option value="">{{ tkey('website.prices.filters.all_durations') }}</option>
                                @forelse ($filterOptions['durations'] as $duration)
                                    <option value="{{ $duration }}" @selected((string) ($filters['duration'] ?? '') === (string) $duration)>
                                        {{ tkey('website.prices.filters.duration_weeks', ['weeks' => $duration]) }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </label>

                        <label>
                            {{ tkey('website.pricing.fields.theory_hours') }}
                            <input type="number" min="0" step="1" name="theory_min" value="{{ $filters['theory_min'] ?? '' }}">
                        </label>

                        <label>
                            {{ tkey('website.pricing.fields.practice_hours') }}
                            <input type="number" min="0" step="1" name="practice_min" value="{{ $filters['practice_min'] ?? '' }}">
                        </label>

                        <label>
                            {{ tkey('website.prices.filters.price_min') }}
                            <input type="number" min="0" step="1" name="price_min" value="{{ $filters['price_min'] ?? '' }}">
                        </label>

                        <label>
                            {{ tkey('website.prices.filters.price_max') }}
                            <input type="number" min="0" step="1" name="price_max" value="{{ $filters['price_max'] ?? '' }}">
                        </label>

                        <div class="filter-actions">
                            <button class="button" type="submit">{{ tkey('website.filters.apply') }}</button>
                            <a class="button secondary" href="{{ route('website.pricing') }}#pricing-results">{{ tkey('website.filters.reset') }}</a>
                        </div>
                    </div>

                    @if ($hasActiveFilters ?? false)
                        <p class="filter-note">{{ tkey('website.prices.filters.active') }}</p>
                    @endif
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ tkey('website.prices.columns.course') }}</th>
                                <th>{{ tkey('website.prices.columns.category') }}</th>
                                <th>{{ tkey('website.prices.columns.duration') }}</th>
                                <th>{{ tkey('website.prices.columns.hours') }}</th>
                                <th>{{ tkey('website.prices.columns.format') }}</th>
                                <th>{{ tkey('website.prices.columns.price') }}</th>
                                <th>{{ tkey('website.prices.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($programs as $program)
                                <tr>
                                    <td>
                                        <strong>{{ $program->displayTitle() }}</strong>
                                        <br>
                                        <span class="meta">{{ $program->displayShortDescription() }}</span>
                                    </td>
                                    <td>{{ $program->license_category }}</td>
                                    <td>{{ tkey('website.course.duration_weeks', ['weeks' => $program->duration_weeks]) }}</td>
                                    <td>{{ tkey('website.prices.hours_value', ['theory' => $program->theory_hours, 'practice' => $program->practice_hours]) }}</td>
                                    <td>{{ tkey('website.courses.formats.'.$program->format) }}</td>
                                    <td>
                                        <strong>{{ $program->priceForHumans() }}</strong>
                                        @if ($program->oldPriceForHumans())
                                            <br><span class="meta">{{ tkey('website.course.price.old', ['price' => $program->oldPriceForHumans()]) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php($courseLink = route('website.courses.show', $program).($courseContextQuery !== [] ? '?'.http_build_query($courseContextQuery) : ''))
                                        <a class="button secondary" href="{{ $courseLink }}">{{ tkey('website.actions.view_course') }}</a>
                                        <a class="button" href="#application-form">{{ tkey('website.actions.apply') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">{{ tkey('website.home.empty.programs_body') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.prices.packages.kicker') }}</p>
                        <h2>{{ tkey('website.prices.packages.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.prices.packages.lead') }}</p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ tkey('website.prices.columns.package') }}</th>
                                <th>{{ tkey('website.prices.columns.course') }}</th>
                                <th>{{ tkey('website.prices.columns.hours') }}</th>
                                <th>{{ tkey('website.prices.columns.price') }}</th>
                                <th>{{ tkey('website.prices.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($packages as $package)
                                <tr>
                                    <td>
                                        <strong>{{ $package->displayName() }}</strong>
                                        @if ($package->is_featured)
                                            <br><span class="fact">{{ tkey('website.prices.packages.featured') }}</span>
                                        @endif
                                        @if ($package->displayDescription())
                                            <br><span class="meta">{{ $package->displayDescription() }}</span>
                                        @endif
                                        @foreach ($package->displayFeatures() as $feature)
                                            <br><span class="meta">{{ $feature }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if ($package->course)
                                            @php($packageCourseLink = route('website.courses.show', $package->course).($courseContextQuery !== [] ? '?'.http_build_query($courseContextQuery) : ''))
                                            <a href="{{ $packageCourseLink }}">{{ $package->course->displayTitle() }}</a>
                                        @else
                                            {{ $package->category?->displayName() ?? tkey('website.prices.packages.no_course') }}
                                        @endif
                                    </td>
                                    <td>{{ tkey('website.prices.hours_value', ['theory' => $package->theory_hours ?? 0, 'practice' => $package->practice_hours ?? 0]) }}</td>
                                    <td>
                                        <strong>{{ $package->priceForHumans() }}</strong>
                                        @if ($package->oldPriceForHumans())
                                            <br><span class="meta">{{ tkey('website.course.price.old', ['price' => $package->oldPriceForHumans()]) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="button" href="#application-form">{{ tkey('website.actions.apply') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">{{ tkey('website.prices.packages.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.prices.payment.kicker') }}</p>
                        <h2>{{ tkey('website.prices.payment.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.prices.payment.lead') }}</p>
                </div>

                <div class="grid three">
                    <article class="card">
                        <h3>{{ tkey('website.prices.payment.installments.title') }}</h3>
                        <p class="meta">{{ tkey('website.prices.payment.installments.body') }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.prices.payment.included.title') }}</h3>
                        <p class="meta">{{ tkey('website.prices.payment.included.body') }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.prices.payment.extra.title') }}</h3>
                        <p class="meta">{{ tkey('website.prices.payment.extra.body') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section" id="application-form">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.apply.kicker') }}</p>
                        <h2>{{ tkey('website.forms.apply.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.forms.apply.subtitle') }}</p>
                </div>

                @include('site.partials.lead-form', [
                    'programs' => $programs,
                    'branches' => $branches,
                    'groups' => $groups,
                    'selectedProgram' => $selectedProgram ?? null,
                    'formName' => 'pricing_application',
                ])
            </div>
        </section>
    </main>
@endsection
