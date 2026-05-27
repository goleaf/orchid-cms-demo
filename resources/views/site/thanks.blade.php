@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.thanks.kicker') }}</p>
                <h1>{{ tkey('website.thanks.title') }}</h1>
                <p class="lead">{{ tkey('website.thanks.lead') }}</p>
                <div class="actions">
                    <a class="button" href="{{ route('site.home') }}">{{ tkey('website.nav.home') }}</a>
                    <a class="button secondary" href="{{ route('site.prices') }}">{{ tkey('website.nav.prices') }}</a>
                </div>
            </div>
        </section>
    </main>
@endsection
