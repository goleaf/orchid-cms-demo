@extends('site.layout')

@section('content')
    <header class="section dark">
        <div class="section-inner">
            <p class="kicker">{{ tkey('website.course.category_label', ['category' => $program->license_category]) }}</p>
            <h1>{{ $program->displayTitle() }}</h1>
            <p class="lead">{{ $program->displayDescription() }}</p>
            <div class="facts">
                <span class="fact">{{ tkey('website.course.duration_weeks', ['weeks' => $program->duration_weeks]) }}</span>
                <span class="fact">{{ tkey('website.course.theory_hours', ['hours' => $program->theory_hours]) }}</span>
                <span class="fact">{{ tkey('website.course.practice_hours', ['hours' => $program->practice_hours]) }}</span>
                <span class="fact">{{ tkey('website.formats.'.$program->format) }}</span>
                <span class="fact">{{ $program->priceForHumans() }}</span>
            </div>
            <div class="actions">
                <a class="button" href="{{ route('site.apply', ['program' => $program->id]) }}">{{ tkey('website.actions.apply') }}</a>
            </div>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.course.program.kicker') }}</p>
                        <h2>{{ tkey('website.course.program.title') }}</h2>
                    </div>
                    <p class="lead">{{ $program->admission_requirements }}</p>
                </div>

                <div class="grid three">
                    <article class="card">
                        <h3>{{ tkey('website.course.documents.title') }}</h3>
                        <div class="badge-list">
                            @foreach (($program->required_documents ?? []) as $document)
                                <span class="badge">{{ $document }}</span>
                            @endforeach
                        </div>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.course.languages.title') }}</h3>
                        <div class="badge-list">
                            @foreach (($program->available_languages ?? []) as $language)
                                <span class="badge">{{ $language }}</span>
                            @endforeach
                        </div>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.course.requirements.title') }}</h3>
                        <p class="meta">{{ $program->admission_requirements }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.course.price.kicker') }}</p>
                        <h2>{{ tkey('website.course.price.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.course.price.lead') }}</p>
                </div>

                <div class="grid three">
                    <article class="card">
                        <p class="kicker">{{ tkey('website.course.price.current') }}</p>
                        <p class="price">{{ $program->priceForHumans() }}</p>
                        @if ($program->oldPriceForHumans())
                            <p class="meta">{{ tkey('website.course.price.old', ['price' => $program->oldPriceForHumans()]) }}</p>
                        @endif
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.course.price.included') }}</h3>
                        <p class="meta">{{ $program->displayIncludedItems() ?: tkey('website.course.price.included_fallback') }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.course.price.extra_costs') }}</h3>
                        <p class="meta">{{ $program->displayExtraCosts() ?: tkey('website.course.price.extra_costs_fallback') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.course.curriculum.kicker') }}</p>
                        <h2>{{ tkey('website.course.curriculum.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.course.curriculum.lead') }}</p>
                </div>

                <div class="grid two">
                    <article class="card">
                        <h3>{{ tkey('website.course.curriculum.theory') }}</h3>
                        <p class="meta">{{ $program->displayTheoryProgram() ?: tkey('website.course.curriculum.theory_fallback') }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.course.curriculum.practice') }}</h3>
                        <p class="meta">{{ $program->displayPracticeProgram() ?: tkey('website.course.curriculum.practice_fallback') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section soft">
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
                                <th>{{ tkey('website.groups.columns.code') }}</th>
                                <th>{{ tkey('website.groups.columns.group') }}</th>
                                <th>{{ tkey('website.groups.columns.branch') }}</th>
                                <th>{{ tkey('website.groups.columns.start') }}</th>
                                <th>{{ tkey('website.groups.columns.days') }}</th>
                                <th>{{ tkey('website.groups.columns.time') }}</th>
                                <th>{{ tkey('website.groups.columns.instructor') }}</th>
                                <th>{{ tkey('website.groups.columns.seats') }}</th>
                                <th>{{ tkey('website.groups.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($program->groups as $group)
                                <tr>
                                    <td>{{ $group->code }}</td>
                                    <td>{{ $group->displayName() }}</td>
                                    <td>{{ $group->branch->displayCity() }}</td>
                                    <td>{{ $group->starts_on?->toDateString() ?? '-' }}</td>
                                    <td>{{ implode(', ', $group->meeting_days ?? []) }}</td>
                                    <td>{{ $group->meeting_time?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $group->instructor?->name ?? '-' }}</td>
                                    <td>{{ tkey('website.groups.seats_value', ['available' => $group->seatsAvailable(), 'capacity' => $group->capacity]) }}</td>
                                    <td>
                                        <a class="button secondary" href="{{ route('site.apply', ['program' => $program->id, 'branch' => $group->branch_id, 'group' => $group->id]) }}">{{ tkey('website.actions.apply') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">{{ tkey('website.groups.empty_for_course') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.course.availability.kicker') }}</p>
                        <h2>{{ tkey('website.course.availability.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.course.availability.lead') }}</p>
                </div>

                <div class="grid three">
                    @forelse ($branches as $branch)
                        <article class="card">
                            <p class="kicker">{{ $branch->displayCity() }}</p>
                            <h3>{{ $branch->displayName() }}</h3>
                            <p class="meta">{{ $branch->displayAddress() }}</p>
                            <span class="badge">{{ tkey('website.branches.groups_count', ['count' => $branch->groups_count]) }}</span>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.contacts.empty.title') }}</h3>
                        </article>
                    @endforelse
                </div>

                <div class="grid two mt-18">
                    @foreach ($instructors as $instructor)
                        <article class="card">
                            <p class="kicker">{{ $instructor->branch->displayCity() }}</p>
                            <h3>{{ $instructor->name }}</h3>
                            <p class="meta">{{ $instructor->teaching_style }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ tkey('website.instructors.experience_years', ['years' => $instructor->experience_years]) }}</span>
                                <span class="badge">{{ tkey('website.instructors.rating', ['rating' => $instructor->rating]) }}</span>
                            </div>
                        </article>
                    @endforeach

                    @foreach ($vehicles as $vehicle)
                        <article class="card">
                            <p class="kicker">{{ $vehicle->branch->displayCity() }}</p>
                            <h3>{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                            <p class="meta">{{ $vehicle->description }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $vehicle->year }}</span>
                                <span class="badge">{{ $vehicle->transmission }}</span>
                                <span class="badge">{{ $vehicle->instructor?->name ?? tkey('website.vehicles.reserve') }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
@endsection
