@extends('site.layout')

@section('content')
    <main>
        <article>
            <section class="section dark">
                <div class="section-inner">
                    <p class="kicker">{{ $article->category_label }}</p>
                    <h1>{{ $article->title }}</h1>
                    <p class="lead">{{ $article->excerpt }}</p>
                </div>
            </section>

            <section class="section">
                <div class="section-inner">
                    <div class="card article-body">
                        {!! \Illuminate\Support\Str::markdown($article->body, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]) !!}
                    </div>
                </div>
            </section>
        </article>
    </main>
@endsection
