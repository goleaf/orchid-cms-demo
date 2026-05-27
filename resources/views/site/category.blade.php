@extends('site.layout')

@section('content')
    <header class="section dark">
        <div class="section-inner">
            <p class="kicker">Категория {{ $program->license_category }}</p>
            <h1>{{ $program->title }}</h1>
            <p class="lead">{{ $program->description }}</p>
            <div class="facts">
                <span class="fact">{{ $program->duration_weeks }} weeks</span>
                <span class="fact">{{ $program->theory_hours }} theory hours</span>
                <span class="fact">{{ $program->practice_hours }} practice hours</span>
                <span class="fact">{{ $program->format }}</span>
                <span class="fact">{{ $program->priceForHumans() }}</span>
            </div>
            <div class="actions">
                <a class="button" href="{{ route('site.apply', ['program' => $program->id]) }}">Записаться</a>
            </div>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Программа</p>
                        <h2>Что входит в обучение</h2>
                    </div>
                    <p class="lead">{{ $program->admission_requirements }}</p>
                </div>

                <div class="grid three">
                    <article class="card">
                        <h3>Документы</h3>
                        <div class="badge-list">
                            @foreach (($program->required_documents ?? []) as $document)
                                <span class="badge">{{ $document }}</span>
                            @endforeach
                        </div>
                    </article>
                    <article class="card">
                        <h3>Языки</h3>
                        <div class="badge-list">
                            @foreach (($program->available_languages ?? []) as $language)
                                <span class="badge">{{ $language }}</span>
                            @endforeach
                        </div>
                    </article>
                    <article class="card">
                        <h3>Условия допуска</h3>
                        <p class="meta">{{ $program->admission_requirements }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Расписание</p>
                        <h2>Доступные группы</h2>
                    </div>
                    <p class="lead">Группы показывают филиал, дату старта, дни занятий, время, инструктора и свободные места.</p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Код</th>
                                <th>Группа</th>
                                <th>Филиал</th>
                                <th>Старт</th>
                                <th>Дни</th>
                                <th>Время</th>
                                <th>Инструктор</th>
                                <th>Места</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($program->groups as $group)
                                <tr>
                                    <td>{{ $group->code }}</td>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->branch->city }}</td>
                                    <td>{{ $group->starts_on?->toDateString() ?? '-' }}</td>
                                    <td>{{ implode(', ', $group->meeting_days ?? []) }}</td>
                                    <td>{{ $group->meeting_time?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $group->instructor?->name ?? '-' }}</td>
                                    <td>{{ $group->seatsAvailable() }} / {{ $group->capacity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">Нет опубликованных групп для этой категории.</td>
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
                        <p class="kicker">Доступность</p>
                        <h2>Филиалы, инструкторы и машины</h2>
                    </div>
                    <p class="lead">Подборки ограничены активными филиалами, активными инструкторами и машинами нужной категории.</p>
                </div>

                <div class="grid three">
                    @foreach ($branches as $branch)
                        <article class="card">
                            <p class="kicker">{{ $branch->city }}</p>
                            <h3>{{ $branch->name }}</h3>
                            <p class="meta">{{ $branch->address }}</p>
                            <span class="badge">{{ $branch->groups_count }} groups</span>
                        </article>
                    @endforeach
                </div>

                <div class="grid two mt-18">
                    @foreach ($instructors as $instructor)
                        <article class="card">
                            <p class="kicker">{{ $instructor->branch->city }}</p>
                            <h3>{{ $instructor->name }}</h3>
                            <p class="meta">{{ $instructor->teaching_style }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $instructor->experience_years }} years</span>
                                <span class="badge">{{ $instructor->rating }} rating</span>
                            </div>
                        </article>
                    @endforeach

                    @foreach ($vehicles as $vehicle)
                        <article class="card">
                            <p class="kicker">{{ $vehicle->branch->city }}</p>
                            <h3>{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                            <p class="meta">{{ $vehicle->description }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $vehicle->year }}</span>
                                <span class="badge">{{ $vehicle->transmission }}</span>
                                <span class="badge">{{ $vehicle->instructor?->name ?? 'Reserve' }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
@endsection
