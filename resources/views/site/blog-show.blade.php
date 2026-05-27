@extends('site.layout')

@section('content')
    <main>
        <article>
            <section class="section dark">
                <div class="section-inner">
                    <p class="kicker">{{ $article->category }}</p>
                    <h1>{{ $article->title }}</h1>
                    <p class="lead">{{ $article->excerpt }}</p>
                </div>
            </section>

            <section class="section">
                <div class="section-inner">
                    <div class="card">
                        @foreach (preg_split('/\n+/', $article->body) as $paragraph)
                            @if (filled($paragraph))
                                <p>{{ $paragraph }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
        </article>
    </main>
@endsection
