@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.thanks.kicker') }}</p>
                <h1>{{ tkey('website.forms.messages.thank_you_title') }}</h1>
                <p class="lead">{{ tkey('website.forms.messages.thank_you_text') }}</p>
                <div class="actions">
                    <a class="button" href="{{ route('website.home') }}">{{ tkey('website.nav.home') }}</a>
                    <a class="button secondary" href="{{ route('website.pricing') }}">{{ tkey('website.nav.pricing') }}</a>
                </div>
            </div>
        </section>
    </main>
@endsection
