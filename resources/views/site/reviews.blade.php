@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">Reviews</p>
                <h1>Отзывы учеников</h1>
                <p class="lead">Отзывы проходят модерацию и могут быть привязаны к курсу, группе, инструктору или видеоотзыву.</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid three">
                    @forelse ($reviews as $review)
                        <article class="card">
                            <p class="kicker">{{ $review->rating }} / 5</p>
                            <h3>{{ $review->title }}</h3>
                            <p>{{ $review->body }}</p>
                            <p class="meta">{{ $review->author_name }}</p>
                            <div class="badge-list">
                                @if ($review->trainingProgram)
                                    <span class="badge">{{ $review->trainingProgram->title }}</span>
                                @endif
                                @if ($review->trainingGroup)
                                    <span class="badge">{{ $review->trainingGroup->code }}</span>
                                @endif
                                @if ($review->instructor)
                                    <span class="badge">{{ $review->instructor->name }}</span>
                                @endif
                            </div>
                            @if ($review->admin_reply)
                                <p class="meta">Admin reply: {{ $review->admin_reply }}</p>
                            @endif
                            @if ($review->video_url)
                                <div class="actions">
                                    <a class="button secondary" href="{{ $review->video_url }}">Видеоотзыв</a>
                                </div>
                            @endif
                        </article>
                    @empty
                        <article class="card">
                            <h3>Отзывы еще не опубликованы</h3>
                            <p class="meta">После модерации отзывы появятся здесь.</p>
                        </article>
                    @endforelse
                </div>

                {{ $reviews->links() }}
            </div>
        </section>
    </main>
@endsection
