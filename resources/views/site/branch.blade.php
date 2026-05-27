@extends('site.layout')

@section('content')
    <header class="section dark">
        <div class="section-inner">
            <p class="kicker">{{ $branch->displayCity() }}</p>
            <h1>{{ $branch->displayName() }}</h1>
            <p class="lead">{{ $branch->displayDescription() ?: tkey('website.branches.detail.lead', ['city' => $branch->displayCity()]) }}</p>
            <div class="facts">
                <span class="fact">{{ $branch->displayAddress() }}</span>
                @if ($branch->phone)
                    <span class="fact">{{ $branch->phone }}</span>
                @endif
                @if ($branch->email)
                    <span class="fact">{{ $branch->email }}</span>
                @endif
            </div>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.branches.detail.kicker') }}</p>
                        <h2>{{ tkey('website.branches.detail.location_title') }}</h2>
                    </div>
                    <p class="lead">{{ $branch->displayWorkingHours() ?: tkey('website.branches.detail.working_hours_fallback') }}</p>
                </div>

                <div class="grid three">
                    <article class="card">
                        <h3>{{ tkey('website.branches.detail.groups') }}</h3>
                        <p class="price">{{ $branch->groups_count }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.branches.detail.instructors') }}</h3>
                        <p class="price">{{ $branch->instructors_count }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.branches.detail.vehicles') }}</h3>
                        <p class="price">{{ $branch->vehicles_count }}</p>
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
                                <th>{{ tkey('website.groups.columns.group') }}</th>
                                <th>{{ tkey('website.groups.columns.course') }}</th>
                                <th>{{ tkey('website.groups.columns.start') }}</th>
                                <th>{{ tkey('website.groups.columns.time') }}</th>
                                <th>{{ tkey('website.groups.columns.seats') }}</th>
                                <th>{{ tkey('website.groups.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branch->groups as $group)
                                <tr>
                                    <td>{{ $group->displayName() }}<br><span class="meta">{{ $group->code }}</span></td>
                                    <td>{{ $group->trainingProgram->displayTitle() }}</td>
                                    <td>{{ $group->starts_on?->toDateString() ?? '-' }}</td>
                                    <td>{{ $group->meeting_time?->format('H:i') ?? '-' }}</td>
                                    <td>{{ tkey('website.groups.seats_value', ['available' => $group->seatsAvailable(), 'capacity' => $group->capacity]) }}</td>
                                    <td>
                                        <a class="button secondary" href="#application-form">{{ tkey('website.actions.apply') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">{{ tkey('website.groups.empty_for_branch') }}</td>
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
                        <p class="kicker">{{ tkey('website.branches.detail.resources_kicker') }}</p>
                        <h2>{{ tkey('website.branches.detail.resources_title') }}</h2>
                    </div>
                </div>

                <div class="grid two">
                    @forelse ($branch->instructors as $instructor)
                        <article class="card">
                            <p class="kicker">{{ tkey('website.nav.instructors') }}</p>
                            <h3>{{ $instructor->name }}</h3>
                            <p class="meta">{{ $instructor->teaching_style }}</p>
                            <span class="badge">{{ tkey('website.instructors.rating', ['rating' => $instructor->rating]) }}</span>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.instructors.empty.title') }}</h3>
                            <p class="meta">{{ tkey('website.instructors.empty.body') }}</p>
                        </article>
                    @endforelse

                    @forelse ($branch->vehicles as $vehicle)
                        <article class="card">
                            <p class="kicker">{{ tkey('website.nav.fleet') }}</p>
                            <h3>{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                            <p class="meta">{{ $vehicle->description }}</p>
                            <span class="badge">{{ tkey('website.transmissions.'.$vehicle->transmission) }}</span>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.vehicles.empty.title') }}</h3>
                            <p class="meta">{{ tkey('website.vehicles.empty.body') }}</p>
                        </article>
                    @endforelse
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
                    'programs' => $programs,
                    'branches' => collect([$branch]),
                    'groups' => $branch->groups,
                    'selectedBranch' => $branch,
                    'formName' => 'branch_detail_application',
                ])
            </div>
        </section>
    </main>
@endsection
