@extends('site.layout')

@section('content')
    <header class="hero">
        <div class="hero-inner">
            <div class="hero-copy">
                @if ($page->eyebrow)
                    <p class="eyebrow">{{ $page->eyebrow }}</p>
                @endif
                <h1>{{ $page->hero_title }}</h1>
                <p class="lead">{{ $page->hero_summary }}</p>
                <div class="hero-actions">
                    <a class="button" href="{{ route('site.apply') }}">Записаться</a>
                    <a class="button secondary" href="#programs">Категории и цены</a>
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
                        <span class="meta">учеников в системе</span>
                    </div>
                    <div class="card stat">
                        <strong>{{ $stats['pass_rate'] }}%</strong>
                        <span class="meta">целевой процент сдачи</span>
                    </div>
                    <div class="card stat">
                        <strong>{{ $stats['instructors'] }}</strong>
                        <span class="meta">активных инструкторов</span>
                    </div>
                    <div class="card stat">
                        <strong>{{ $stats['vehicles'] }}</strong>
                        <span class="meta">учебных автомобилей</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Автошкола</p>
                        <h2>{{ $page->about_heading }}</h2>
                    </div>
                    <p class="lead">{{ $page->about_body }}</p>
                </div>

                <div class="grid three">
                    @forelse ($offers as $offer)
                        <article class="card">
                            <p class="kicker">Преимущество {{ $loop->iteration }}</p>
                            <h3>{{ $offer['title'] }}</h3>
                            <p class="meta">{{ $offer['body'] }}</p>
                        </article>
                    @empty
                        <article class="card">
                            <h3>Операционная база готова</h3>
                            <p class="meta">Добавьте преимущества из Orchid CMS.</p>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="programs" class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Категории</p>
                        <h2>Программы, цены и часы обучения</h2>
                    </div>
                    <p class="lead">Каждая категория ведет на отдельную страницу с документами, группами, филиалами, инструкторами и машинами.</p>
                </div>

                <div class="grid three">
                    @forelse ($programs as $program)
                        <article class="card">
                            <p class="kicker">Категория {{ $program->license_category }}</p>
                            <h3>{{ $program->title }}</h3>
                            <p class="meta">{{ $program->description }}</p>
                            <div class="facts">
                                <span class="fact">{{ $program->duration_weeks }} weeks</span>
                                <span class="fact">{{ $program->theory_hours }} theory h</span>
                                <span class="fact">{{ $program->practice_hours }} practice h</span>
                                <span class="fact">{{ $program->format }}</span>
                            </div>
                            <p class="price">{{ $program->priceForHumans() }}</p>
                            <div class="actions">
                                <a class="button" href="{{ route('site.categories.show', $program) }}">Подробнее</a>
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>Программы готовятся</h3>
                            <p class="meta">Активные программы появятся после публикации в админке.</p>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Наборы</p>
                        <h2>Ближайшие группы</h2>
                    </div>
                    <p class="lead">Группы связаны с филиалом, программой, инструктором, вместимостью и статусом набора.</p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Группа</th>
                                <th>Категория</th>
                                <th>Филиал</th>
                                <th>Старт</th>
                                <th>Инструктор</th>
                                <th>Места</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingGroups as $group)
                                <tr>
                                    <td>{{ $group->name }}<br><span class="meta">{{ $group->code }}</span></td>
                                    <td>{{ $group->trainingProgram->title }}</td>
                                    <td>{{ $group->branch->city }}</td>
                                    <td>{{ $group->starts_on?->toDateString() ?? '-' }}</td>
                                    <td>{{ $group->instructor?->name ?? '-' }}</td>
                                    <td>{{ $group->seatsAvailable() }} / {{ $group->capacity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Ближайшие наборы пока не опубликованы.</td>
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
                        <p class="kicker">Процесс</p>
                        <h2>Как проходит обучение</h2>
                    </div>
                    <p class="lead">Путь ученика проходит через заявку, CRM, LMS, расписание, документы, платежи и экзамены.</p>
                </div>

                <div class="grid four">
                    @foreach ($steps as $step)
                        <article class="card">
                            <p class="kicker">Шаг {{ $loop->iteration }}</p>
                            <h3>{{ $step['title'] }}</h3>
                            <p class="meta">{{ $step['body'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Команда и автопарк</p>
                        <h2>Инструкторы и учебные машины</h2>
                    </div>
                    <p class="lead">Публичные профили показывают стаж, категории, языки, рейтинг, машину, филиал и доступность.</p>
                </div>

                <div class="grid two">
                    @foreach ($featuredInstructors as $instructor)
                        <article class="card">
                            <p class="kicker">{{ $instructor->branch->city }}</p>
                            <h3>{{ $instructor->name }}</h3>
                            <p class="meta">{{ $instructor->teaching_style }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $instructor->experience_years }} years</span>
                                <span class="badge">{{ $instructor->rating }} rating</span>
                                @foreach (($instructor->languages ?? []) as $language)
                                    <span class="badge">{{ $language }}</span>
                                @endforeach
                            </div>
                        </article>
                    @endforeach

                    @foreach ($featuredVehicles as $vehicle)
                        <article class="card">
                            <p class="kicker">{{ $vehicle->branch->city }}</p>
                            <h3>{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                            <p class="meta">{{ $vehicle->description }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $vehicle->license_category }}</span>
                                <span class="badge">{{ $vehicle->transmission }}</span>
                                <span class="badge">{{ str($vehicle->status->value)->title() }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Отзывы</p>
                        <h2>Рейтинг школы {{ $stats['rating'] ?: '5.0' }}</h2>
                    </div>
                    <p class="lead">Публикуются только проверенные отзывы, с привязкой к курсу, группе или инструктору.</p>
                </div>

                <div class="grid three">
                    @foreach ($reviews as $review)
                        <article class="card">
                            <p class="kicker">{{ $review->rating }} / 5</p>
                            <h3>{{ $review->title }}</h3>
                            <p>{{ $review->body }}</p>
                            <p class="meta">{{ $review->author_name }} · {{ $review->trainingProgram?->title }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="actions">
                    <a class="button secondary" href="{{ route('site.reviews') }}">Все отзывы</a>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">FAQ</p>
                        <h2>Частые вопросы</h2>
                    </div>
                    <p class="lead">Ответы закрывают вопросы по формату, документам, выбору инструктора и интенсивным курсам.</p>
                </div>

                <div class="grid two">
                    @foreach ($faq as $item)
                        <article class="card">
                            <h3>{{ $item['question'] }}</h3>
                            <p class="meta">{{ $item['answer'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">База знаний</p>
                        <h2>Статьи для учеников</h2>
                    </div>
                    <p class="lead">Материалы по выбору автошколы, теории, практике, экзаменам, ошибкам и изменениям правил.</p>
                </div>

                <div class="grid three">
                    @foreach ($articles as $article)
                        <article class="card">
                            <p class="kicker">{{ $article->category }}</p>
                            <h3>{{ $article->title }}</h3>
                            <p class="meta">{{ $article->excerpt }}</p>
                            <div class="actions">
                                <a class="button secondary" href="{{ route('site.blog.show', $article) }}">Читать</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section" id="contacts">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">Контакты</p>
                        <h2>Филиалы и карта</h2>
                    </div>
                    <p class="lead">Каждый филиал связан с группами, инструкторами, машинами и заявками.</p>
                </div>

                <div class="grid two">
                    <div class="grid">
                        @foreach ($branches as $branch)
                            <article class="card">
                                <h3>{{ $branch->name }}</h3>
                                <p class="meta">{{ $branch->city }}, {{ $branch->address }}</p>
                                <div class="facts">
                                    <span class="fact">{{ $branch->groups_count }} groups</span>
                                    <span class="fact">{{ $branch->instructors_count }} instructors</span>
                                    <span class="fact">{{ $branch->vehicles_count }} cars</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="map">
                        <strong>Branch map</strong>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
