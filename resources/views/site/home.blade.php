@extends('site.layout')

@section('content')
    <header class="hero">
        <div class="hero-inner">
            <div class="hero-copy">
                @if ($page->displayText('eyebrow'))
                    <p class="eyebrow">{{ $page->displayText('eyebrow') }}</p>
                @endif
                <h1>{{ $page->displayText('hero_title') }}</h1>
                <p class="lead">{{ $page->displayText('hero_summary') }}</p>
                <div class="hero-actions">
                    <a class="button" href="{{ route('site.apply') }}">{{ tkey('website.actions.apply') }}</a>
                    <a class="button secondary" href="#programs">{{ tkey('website.home.hero.secondary_action') }}</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="section soft">
            <div class="section-inner">
                <div class="grid four">
                    <div class="card stat">
                        <strong>{{ $stats['students'] }}</strong>
                        <span class="meta">{{ tkey('website.home.stats.students') }}</span>
                    </div>
                    <div class="card stat">
                        <strong>{{ $stats['pass_rate'] }}%</strong>
                        <span class="meta">{{ tkey('website.home.stats.pass_rate') }}</span>
                    </div>
                    <div class="card stat">
                        <strong>{{ $stats['instructors'] }}</strong>
                        <span class="meta">{{ tkey('website.home.stats.instructors') }}</span>
                    </div>
                    <div class="card stat">
                        <strong>{{ $stats['vehicles'] }}</strong>
                        <span class="meta">{{ tkey('website.home.stats.vehicles') }}</span>
                    </div>
                </div>
            </div>
        </section>

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

        <section id="programs" class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.home.programs.kicker') }}</p>
                        <h2>{{ tkey('website.home.programs.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.home.programs.lead') }}</p>
                </div>

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
                                <span class="fact">{{ tkey('website.formats.'.$program->format) }}</span>
                            </div>
                            <p class="price">{{ $program->priceForHumans() }}</p>
                            <div class="actions">
                                <a class="button secondary" href="{{ route('site.courses.show', $program) }}">{{ tkey('website.actions.details') }}</a>
                                <a class="button" href="{{ route('site.apply', ['program' => $program->id]) }}">{{ tkey('website.actions.apply') }}</a>
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
                                    <td>{{ $group->branch->displayCity() }}</td>
                                    <td>{{ $group->starts_on?->toDateString() ?? '-' }}</td>
                                    <td>{{ $group->instructor?->name ?? '-' }}</td>
                                    <td>{{ tkey('website.groups.seats_value', ['available' => $group->seatsAvailable(), 'capacity' => $group->capacity]) }}</td>
                                    <td>
                                        <a class="button secondary" href="{{ route('site.apply', ['program' => $group->training_program_id, 'branch' => $group->branch_id, 'group' => $group->id]) }}">{{ tkey('website.actions.apply') }}</a>
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

                <div class="actions">
                    <a class="button" href="{{ route('site.prices') }}">{{ tkey('website.nav.prices') }}</a>
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

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.apply.kicker') }}</p>
                        <h2>{{ tkey('website.apply.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.apply.lead') }}</p>
                </div>

                <form method="POST" action="{{ route('site.apply.store') }}" class="card">
                    @csrf
                    <input type="hidden" name="source" value="website">
                    <input type="hidden" name="form_name" value="homepage_enrollment">
                    <input type="hidden" name="preferred_format" value="mixed">
                    <input type="hidden" name="preferred_language" value="{{ app()->getLocale() }}">

                    <div class="inline-form">
                        <label>
                            {{ tkey('crm.leads.fields.course') }}
                            <select name="training_program_id" required>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->displayTitle() }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            {{ tkey('crm.leads.fields.branch') }}
                            <select name="branch_id" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->displayName() }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            {{ tkey('crm.leads.fields.first_name') }}
                            <input name="first_name" required>
                        </label>
                        <label>
                            {{ tkey('crm.leads.fields.phone') }}
                            <input name="phone" required>
                        </label>
                        <label class="full">
                            <span class="check-row">
                                <input type="checkbox" name="privacy_consent" value="1" required>
                                {{ tkey('website.forms.privacy_consent') }}
                            </span>
                        </label>
                    </div>

                    <div class="actions">
                        <button class="button" type="submit">{{ tkey('website.actions.submit_application') }}</button>
                    </div>
                </form>
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
                                <p class="meta">{{ $branch->displayCity() }}, {{ $branch->displayAddress() }}</p>
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
