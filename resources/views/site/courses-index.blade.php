@extends('site.layout')

@section('content')
    @php
        $filters = $filters ?? ['country' => '', 'city' => '', 'category' => ''];
        $filterOptions = $filterOptions ?? ['countries' => collect(), 'cities' => collect(), 'categories' => collect()];
        $courseContextQuery = $courseContextQuery ?? [];
    @endphp

    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.nav.courses') }}</p>
                <h1>{{ tkey('website.courses.title') }}</h1>
                <p class="lead">{{ tkey('website.home.courses_subtitle') }}</p>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.home.programs.kicker') }}</p>
                        <h2>{{ tkey('website.home.courses_title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.home.programs.lead') }}</p>
                </div>

                <form class="filter-panel" method="GET" action="{{ route('website.courses.index') }}" data-location-filter>
                    <div class="filter-head">
                        <div>
                            <p class="kicker">{{ tkey('website.filters.kicker') }}</p>
                            <h2>{{ tkey('website.filters.title') }}</h2>
                        </div>
                        <p class="meta">{{ tkey('website.filters.subtitle') }}</p>
                    </div>

                    <div class="filter-grid">
                        @include('site.partials.location-filter-fields', compact('filters', 'filterOptions'))

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

                        <div class="filter-actions">
                            <button class="button" type="submit">{{ tkey('website.filters.apply') }}</button>
                            <a class="button secondary" href="{{ route('website.courses.index') }}">{{ tkey('website.filters.reset') }}</a>
                        </div>
                    </div>

                    @if ($hasActiveFilters ?? false)
                        <p class="filter-note">{{ tkey('website.filters.active') }}</p>
                    @endif
                </form>

                <div class="badge-list">
                    <a class="badge" href="{{ route('website.courses.index', $courseContextQuery) }}">{{ tkey('crm.leads.filters.all_courses') }}</a>
                    @forelse ($categories as $category)
                        <a class="badge" href="{{ route('website.courses.index', [...$courseContextQuery, 'category' => $category->slug]) }}">
                            {{ $category->displayName() }} · {{ $category->courses_count }}
                        </a>
                    @empty
                        <span class="badge">{{ tkey('website.courses.empty.no_courses') }}</span>
                    @endforelse
                </div>

                <div class="grid three mt-18">
                    @forelse ($courses as $program)
                        <article class="card">
                            <p class="kicker">{{ $program->category?->displayName() ?? tkey('website.course.category_label', ['category' => $program->license_category]) }}</p>
                            <h3>{{ $program->displayTitle() }}</h3>
                            <p class="meta">{{ $program->displayShortDescription() }}</p>
                            <div class="facts">
                                <span class="fact">{{ tkey('website.course.duration_weeks', ['weeks' => $program->duration_weeks]) }}</span>
                                <span class="fact">{{ tkey('website.course.theory_hours_short', ['hours' => $program->theory_hours]) }}</span>
                                <span class="fact">{{ tkey('website.course.practice_hours_short', ['hours' => $program->practice_hours]) }}</span>
                                <span class="fact">{{ tkey('website.courses.formats.'.$program->format) }}</span>
                            </div>
                            <p class="price">{{ $program->priceForHumans() }}</p>
                            <div class="actions">
                                @php($courseLink = route('website.courses.show', $program).($courseContextQuery !== [] ? '?'.http_build_query($courseContextQuery) : ''))
                                <a class="button secondary" href="{{ $courseLink }}">{{ tkey('website.actions.view_course') }}</a>
                                <a class="button" href="#application-form">{{ tkey('website.actions.apply') }}</a>
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.courses.empty.no_courses') }}</h3>
                            <p class="meta">{{ tkey('website.home.empty.programs_body') }}</p>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.groups.kicker') }}</p>
                        <h2>{{ tkey('website.groups.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.groups.lead') }}</p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ tkey('website.groups.columns.group') }}</th>
                                <th>{{ tkey('website.groups.columns.course') }}</th>
                                <th>{{ tkey('website.groups.columns.branch') }}</th>
                                <th>{{ tkey('website.groups.columns.start') }}</th>
                                <th>{{ tkey('website.groups.columns.seats') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groups as $group)
                                <tr>
                                    <td>{{ $group->displayName() }}</td>
                                    <td>{{ $group->trainingProgram?->displayTitle() }}</td>
                                    <td>{{ $group->branch?->displayCountry() }} · {{ $group->branch?->displayCity() }}</td>
                                    <td>{{ $group->starts_on?->toDateString() ?? '-' }}</td>
                                    <td>{{ tkey('website.groups.seats_value', ['available' => $group->seatsAvailable(), 'capacity' => $group->capacity]) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">{{ tkey('website.groups.empty.no_groups') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="section soft" id="application-form">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.apply.kicker') }}</p>
                        <h2>{{ tkey('website.forms.apply.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.forms.apply.subtitle') }}</p>
                </div>

                @include('site.partials.lead-form', [
                    'programs' => $courses,
                    'branches' => $branches,
                    'groups' => $groups,
                    'formName' => 'courses_index_application',
                ])
            </div>
        </section>
    </main>
@endsection
