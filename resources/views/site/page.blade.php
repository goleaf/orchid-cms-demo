@extends('site.layout')

@section('content')
    <main class="section">
        <div class="section-head">
            <div>
                <h1>{{ $page->displayTitle() }}</h1>
                @if ($page->getTranslation('subtitle'))
                    <p class="lead">{{ $page->getTranslation('subtitle') }}</p>
                @endif
            </div>
        </div>

        @if ($page->displayContent())
            <div class="card article-body">
                {!! $page->displayContent() !!}
            </div>
        @endif
    </main>
@endsection
