@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">Knowledge base</p>
                <h1>Блог и база знаний</h1>
                <p class="lead">Статьи про выбор автошколы, обучение, экзамены, теорию, практику, ошибки, новости и инструкции.</p>
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
                            <p class="kicker">{{ $article->category }}</p>
                            <h3>{{ $article->title }}</h3>
                            <p class="meta">{{ $article->excerpt }}</p>
                            <div class="actions">
                                <a class="button secondary" href="{{ route('site.blog.show', $article) }}">Читать</a>
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>Статьи готовятся</h3>
                            <p class="meta">Опубликованные материалы появятся после модерации.</p>
                        </article>
                    @endforelse
                </div>

                {{ $articles->links() }}
            </div>
        </section>
    </main>
@endsection
