@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">Instructors</p>
                <h1>Инструкторы автошколы</h1>
                <p class="lead">Профили показывают фото, имя, стаж, категории, рейтинг, отзывы, машину, языки, график, филиал и стиль обучения.</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid three">
                    @forelse ($instructors as $instructor)
                        <article class="card">
                            <p class="kicker">{{ $instructor->branch->city }}</p>
                            <h3>{{ $instructor->name }}</h3>
                            <p class="meta">{{ $instructor->bio }}</p>
                            <p>{{ $instructor->teaching_style }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $instructor->experience_years }} years</span>
                                <span class="badge">{{ $instructor->rating }} rating</span>
                                <span class="badge">{{ $instructor->reviews_count }} reviews</span>
                                @foreach (($instructor->categories ?? []) as $category)
                                    <span class="badge">{{ $category }}</span>
                                @endforeach
                                @foreach (($instructor->languages ?? []) as $language)
                                    <span class="badge">{{ $language }}</span>
                                @endforeach
                            </div>
                            <p class="meta">{{ $instructor->availability_summary }}</p>
                            <div class="facts">
                                @foreach ($instructor->vehicles as $vehicle)
                                    <span class="fact">{{ $vehicle->make }} {{ $vehicle->model }}</span>
                                @endforeach
                            </div>
                            <div class="actions">
                                <a class="button" href="{{ route('site.apply', ['instructor' => $instructor->id]) }}">Записаться к инструктору</a>
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>Инструкторы готовятся к публикации</h3>
                            <p class="meta">Публичные профили появятся после заполнения в CRM.</p>
                        </article>
                    @endforelse
                </div>

                {{ $instructors->links() }}
            </div>
        </section>
    </main>
@endsection
