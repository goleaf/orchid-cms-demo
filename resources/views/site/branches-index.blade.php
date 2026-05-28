@extends('site.layout')

@section('content')
    @php
        $filters = $filters ?? ['country' => '', 'city' => ''];
        $filterOptions = $filterOptions ?? ['countries' => collect(), 'cities' => collect()];
    @endphp

    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.nav.branches') }}</p>
                <h1>{{ tkey('website.branches.title') }}</h1>
                <p class="lead">{{ tkey('website.contacts.lead') }}</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <form class="filter-panel" method="GET" action="{{ route('website.branches.index') }}" data-location-filter>
                    <div class="filter-head">
                        <div>
                            <p class="kicker">{{ tkey('website.filters.kicker') }}</p>
                            <h2>{{ tkey('website.branches.title') }}</h2>
                        </div>
                        <p class="meta">{{ tkey('website.filters.subtitle') }}</p>
                    </div>

                    <div class="filter-grid">
                        @include('site.partials.location-filter-fields', compact('filters', 'filterOptions'))

                        <div class="filter-actions">
                            <button class="button" type="submit">{{ tkey('website.filters.apply') }}</button>
                            <a class="button secondary" href="{{ route('website.branches.index') }}">{{ tkey('website.filters.reset') }}</a>
                        </div>
                    </div>

                    @if ($hasActiveFilters ?? false)
                        <p class="filter-note">{{ tkey('website.filters.active') }}</p>
                    @endif
                </form>

                <div class="grid two">
                    @forelse ($branches as $branch)
                        <article class="card">
                            <p class="kicker">{{ $branch->displayCountry() }} · {{ $branch->displayCity() }}</p>
                            <h2>{{ $branch->displayName() }}</h2>
                            <p>{{ $branch->displayAddress() }}</p>
                            @if ($branch->displayWorkingHours())
                                <p class="meta">{{ $branch->displayWorkingHours() }}</p>
                            @endif
                            <div class="facts">
                                @if ($branch->phone)
                                    <span class="fact">{{ $branch->phone }}</span>
                                @endif
                                @if ($branch->email)
                                    <span class="fact">{{ $branch->email }}</span>
                                @endif
                                <span class="fact">{{ tkey('website.branches.groups_count', ['count' => $branch->groups_count]) }}</span>
                            </div>
                            <div class="actions">
                                <a class="button secondary" href="{{ route('website.branches.show', ['branch' => $branch->slug]) }}">{{ tkey('website.actions.open') }}</a>
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h2>{{ tkey('website.branches.empty.no_branches') }}</h2>
                            <p class="meta">{{ tkey('website.contacts.empty.body') }}</p>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.groups.kicker') }}</p>
                        <h2>{{ tkey('website.branches.fields.groups') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.groups.lead') }}</p>
                </div>

                <div class="grid two">
                    @forelse ($branches as $branch)
                        <article class="card">
                            <h3>{{ $branch->displayName() }}</h3>
                            @forelse ($branch->groups as $group)
                                <p class="meta">
                                    {{ $group->displayName() }} · {{ $group->trainingProgram?->displayTitle() }} · {{ $group->starts_on?->toDateString() ?? '-' }}
                                </p>
                            @empty
                                <p class="meta">{{ tkey('website.groups.empty_for_branch') }}</p>
                            @endforelse
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.branches.empty.no_branches') }}</h3>
                        </article>
                    @endforelse
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
                    'groups' => $branches->flatMap->groups,
                    'formName' => 'branches_index_application',
                ])
            </div>
        </section>
    </main>
@endsection
