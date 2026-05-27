@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.blog.kicker') }}</p>
                <h1>{{ tkey('website.blog.title') }}</h1>
                <p class="lead">{{ tkey('website.blog.description') }}</p>
                <div class="badge-list">
                    @foreach ($categories as $category)
                        <span class="badge">{{ $category }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid three">
                    @forelse ($articles as $article)
                        <article class="card">
                            <p class="kicker">{{ $article->category_label }}</p>
                            <h3>{{ $article->title }}</h3>
                            <p class="meta">{{ $article->excerpt }}</p>
                            <div class="actions">
                                <a class="button secondary" href="{{ route('site.blog.show', $article) }}">{{ tkey('website.blog.actions.read') }}</a>
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.blog.empty.title') }}</h3>
                            <p class="meta">{{ tkey('website.blog.empty.description') }}</p>
                        </article>
                    @endforelse
                </div>

                {{ $articles->links() }}
            </div>
        </section>
    </main>
@endsection
