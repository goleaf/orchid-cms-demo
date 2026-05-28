@extends('site.layout')

@section('content')
    @php
        $filters = $filters ?? ['country' => '', 'city' => '', 'category' => ''];
        $filterOptions = $filterOptions ?? ['countries' => collect(), 'cities' => collect(), 'categories' => collect()];
        $courseContextQuery = $courseContextQuery ?? [];
    @endphp

    <header class="hero">
        <div class="hero-inner">
            <div class="hero-copy">
                @if ($page->displayText('eyebrow'))
                    <p class="eyebrow">{{ $page->displayText('eyebrow') }}</p>
                @endif
                <h1>{{ $page->displayText('hero_title') }}</h1>
                <p class="lead">{{ $page->displayText('hero_summary') }}</p>
                <div class="hero-actions">
                    <a class="button" href="#application-form">{{ tkey('website.actions.apply') }}</a>
                    <a class="button secondary" href="#programs">{{ tkey('website.home.hero.secondary_action') }}</a>
                </div>
                <div class="hero-metrics">
                    <div>
                        <strong>{{ $stats['students'] }}</strong>
                        <span>{{ tkey('website.home.stats.students') }}</span>
                    </div>
                    <div>
                        <strong>{{ $stats['pass_rate'] }}%</strong>
                        <span>{{ tkey('website.home.stats.pass_rate') }}</span>
                    </div>
                    <div>
                        <strong>{{ $stats['instructors'] }}</strong>
                        <span>{{ tkey('website.home.stats.instructors') }}</span>
                    </div>
                    <div>
                        <strong>{{ $stats['vehicles'] }}</strong>
                        <span>{{ tkey('website.home.stats.vehicles') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.home.about.kicker') }}</p>
                        <h2>{{ $page->displayText('about_heading') }}</h2>
                    </div>
                    <p class="lead">{{ $page->displayText('about_body') }}</p>
                </div>

                <div class="grid three">
                    @forelse ($offers as $offer)
                        <article class="card">
                            <p class="kicker">{{ tkey('website.home.about.benefit_number', ['number' => $loop->iteration]) }}</p>
                            <h3>{{ $offer['title'] }}</h3>
                            <p class="meta">{{ $offer['body'] }}</p>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.home.empty.benefits_title') }}</h3>
                            <p class="meta">{{ tkey('website.home.empty.benefits_body') }}</p>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.home.groups.kicker') }}</p>
                        <h2>{{ tkey('website.home.groups.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.home.groups.lead') }}</p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ tkey('website.groups.columns.group') }}</th>
                                <th>{{ tkey('website.groups.columns.course') }}</th>
                                <th>{{ tkey('website.groups.columns.branch') }}</th>
                                <th>{{ tkey('website.groups.columns.start') }}</th>
                                <th>{{ tkey('website.groups.columns.instructor') }}</th>
                                <th>{{ tkey('website.groups.columns.seats') }}</th>
                                <th>{{ tkey('website.groups.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingGroups as $group)
                                <tr>
                                    <td>{{ $group->displayName() }}<br><span class="meta">{{ $group->code }}</span></td>
                                    <td>{{ $group->trainingProgram->displayTitle() }}</td>
                                    <td>{{ $group->branch->displayCountry() }} · {{ $group->branch->displayCity() }}</td>
                                    <td>{{ $group->starts_on?->toDateString() ?? '-' }}</td>
                                    <td>{{ $group->instructor?->name ?? '-' }}</td>
                                    <td>{{ tkey('website.groups.seats_value', ['available' => $group->seatsAvailable(), 'capacity' => $group->capacity]) }}</td>
                                    <td>
                                        <a class="button secondary" href="#application-form">{{ tkey('website.actions.apply') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">{{ tkey('website.groups.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="programs" class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.home.programs.kicker') }}</p>
                        <h2>{{ tkey('website.home.programs.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.home.programs.lead') }}</p>
                </div>

                <form class="filter-panel" method="GET" action="{{ route('website.home') }}#programs" data-location-filter>
                    <div class="filter-head">
                        <div>
                            <p class="kicker">{{ tkey('website.filters.kicker') }}</p>
                            <h3>{{ tkey('website.filters.title') }}</h3>
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
                            <a class="button secondary" href="{{ route('website.home') }}#programs">{{ tkey('website.filters.reset') }}</a>
                        </div>
                    </div>

                    @if ($hasActiveFilters ?? false)
                        <p class="filter-note">{{ tkey('website.filters.active') }}</p>
                    @endif
                </form>

                <div class="grid three">
                    @forelse ($programs as $program)
                        <article class="card">
                            <p class="kicker">{{ tkey('website.course.category_label', ['category' => $program->license_category]) }}</p>
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
                            <h3>{{ tkey('website.home.empty.programs_title') }}</h3>
                            <p class="meta">{{ tkey('website.home.empty.programs_body') }}</p>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section dark">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.home.process.kicker') }}</p>
                        <h2>{{ tkey('website.home.process.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.home.process.lead') }}</p>
                </div>

                <div class="grid four">
                    @forelse ($steps as $step)
                        <article class="card">
                            <p class="kicker">{{ tkey('website.home.process.step_number', ['number' => $loop->iteration]) }}</p>
                            <h3>{{ $step['title'] }}</h3>
                            <p class="meta">{{ $step['body'] }}</p>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('common.empty.no_records') }}</h3>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section" id="prices">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.prices.kicker') }}</p>
                        <h2>{{ tkey('website.prices.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.prices.lead') }}</p>
                </div>

                <div class="grid four">
                    @forelse ($pricingPackages as $package)
                        <article class="card">
                            <p class="kicker">{{ $package->course?->displayTitle() ?? $package->category?->displayName() ?? tkey('website.prices.packages.no_course') }}</p>
                            <h3>{{ $package->displayName() }}</h3>
                            <p class="price">{{ $package->priceForHumans() }}</p>
                            @if ($package->displayDescription())
                                <p class="meta">{{ $package->displayDescription() }}</p>
                            @endif
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.pricing.empty.no_packages') }}</h3>
                        </article>
                    @endforelse
                </div>

                <div class="actions">
                    <a class="button" href="{{ route('website.pricing') }}">{{ tkey('website.nav.pricing') }}</a>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.nav.reviews') }}</p>
                        <h2>{{ tkey('website.home.testimonials_title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.testimonials.title') }}</p>
                </div>

                <div class="grid three">
                    @forelse ($testimonials as $testimonial)
                        <article class="card">
                            <p class="kicker">{{ tkey('website.testimonials.fields.rating') }}: {{ $testimonial->rating }}</p>
                            <h3>{{ $testimonial->displayName() }}</h3>
                            <p class="meta">{{ $testimonial->displayText() }}</p>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.testimonials.empty.no_testimonials') }}</h3>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.home.faq.kicker') }}</p>
                        <h2>{{ tkey('website.home.faq.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.home.faq.lead') }}</p>
                </div>

                <div class="grid two">
                    @forelse ($faq as $item)
                        <article class="card">
                            <h3>{{ $item['question'] }}</h3>
                            <p class="meta">{{ $item['answer'] }}</p>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('common.empty.no_records') }}</h3>
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
                        <h2>{{ tkey('website.apply.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.apply.lead') }}</p>
                </div>

                @include('site.partials.lead-form', [
                    'programs' => $programs,
                    'branches' => $branches,
                    'groups' => $upcomingGroups,
                    'selectedBranch' => $selectedBranch ?? null,
                    'formName' => 'homepage_application',
                ])
            </div>
        </section>

        <section class="section" id="contacts">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.contacts.kicker') }}</p>
                        <h2>{{ tkey('website.contacts.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.contacts.lead') }}</p>
                </div>

                <div class="grid two">
                    <div class="grid">
                        @forelse ($branches as $branch)
                            <article class="card">
                                <h3>{{ $branch->displayName() }}</h3>
                                <p class="meta">{{ $branch->displayCountry() }} · {{ $branch->displayCity() }}, {{ $branch->displayAddress() }}</p>
                                @if ($branch->displayWorkingHours())
                                    <p class="meta">{{ $branch->displayWorkingHours() }}</p>
                                @endif
                                <div class="facts">
                                    <span class="fact">{{ tkey('website.branches.groups_count', ['count' => $branch->groups_count]) }}</span>
                                    <span class="fact">{{ tkey('website.branches.instructors_count', ['count' => $branch->instructors_count]) }}</span>
                                    <span class="fact">{{ tkey('website.branches.vehicles_count', ['count' => $branch->vehicles_count]) }}</span>
                                </div>
                            </article>
                        @empty
                            <article class="card">
                                <h3>{{ tkey('website.contacts.empty.title') }}</h3>
                                <p class="meta">{{ tkey('website.contacts.empty.body') }}</p>
                            </article>
                        @endforelse
                    </div>
                    <div class="map">
                        <strong>{{ tkey('website.contacts.map_placeholder') }}</strong>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
