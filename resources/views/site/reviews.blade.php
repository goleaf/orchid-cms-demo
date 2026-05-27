@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.reviews.kicker') }}</p>
                <h1>{{ tkey('website.reviews.title') }}</h1>
                <p class="lead">{{ tkey('website.reviews.lead') }}</p>
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
                                    <span class="badge">{{ $review->trainingProgram->displayTitle() }}</span>
                                @endif
                                @if ($review->trainingGroup)
                                    <span class="badge">{{ $review->trainingGroup->displayName() }}</span>
                                @endif
                                @if ($review->instructor)
                                    <span class="badge">{{ $review->instructor->name }}</span>
                                @endif
                            </div>
                            @if ($review->admin_reply)
                                <p class="meta">{{ tkey('website.reviews.admin_reply') }}: {{ $review->admin_reply }}</p>
                            @endif
                            @if ($review->video_url)
                                <div class="actions">
                                    <a class="button secondary" href="{{ $review->video_url }}">{{ tkey('website.reviews.video') }}</a>
                                </div>
                            @endif
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.reviews.empty.title') }}</h3>
                            <p class="meta">{{ tkey('website.reviews.empty.body') }}</p>
                        </article>
                    @endforelse
                </div>

                {{ $reviews->links() }}
            </div>
        </section>
    </main>
@endsection
