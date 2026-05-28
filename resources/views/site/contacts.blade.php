@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.contacts.kicker') }}</p>
                <h1>{{ tkey('website.contacts.title') }}</h1>
                <p class="lead">{{ tkey('website.contacts.lead') }}</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid two">
                    <article class="card">
                        <p class="kicker">{{ tkey('website.branches.fields.phone') }}</p>
                        <h2>{{ $settings['default_phone'] ?? tkey('website.contacts.title') }}</h2>
                    </article>
                    <article class="card">
                        <p class="kicker">{{ tkey('website.branches.fields.email') }}</p>
                        <h2>{{ $settings['default_email'] ?? tkey('website.contacts.title') }}</h2>
                    </article>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid two">
                    <div class="grid">
                        @forelse ($branches as $branch)
                            <article class="card">
                                <p class="kicker">{{ $branch->displayCountry() }} · {{ $branch->displayCity() }}</p>
                                <h3>{{ $branch->displayName() }}</h3>
                                <p>{{ $branch->displayAddress() }}</p>
                                <p class="meta">{{ $branch->phone }} · {{ $branch->email }}</p>
                                @if ($branch->displayWorkingHours())
                                    <p class="meta">{{ $branch->displayWorkingHours() }}</p>
                                @endif
                                <div class="facts">
                                    <span class="fact">{{ tkey('website.branches.groups_count', ['count' => $branch->groups_count]) }}</span>
                                    <span class="fact">{{ tkey('website.branches.instructors_count', ['count' => $branch->instructors_count]) }}</span>
                                    <span class="fact">{{ tkey('website.branches.vehicles_count', ['count' => $branch->vehicles_count]) }}</span>
                                </div>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('website.branches.show', ['branch' => $branch->slug]) }}">{{ tkey('website.actions.open') }}</a>
                                </div>
                            </article>
                        @empty
                            <article class="card">
                                <h3>{{ tkey('website.contacts.empty.title') }}</h3>
                                <p class="meta">{{ tkey('website.contacts.empty.body') }}</p>
                            </article>
                        @endforelse
                    </div>

                    <div>
                        <div class="map">
                            <strong>{{ tkey('website.contacts.map_placeholder') }}</strong>
                        </div>
                        <div id="callback" class="card mt-18">
                            <h3>{{ tkey('website.forms.callback.title') }}</h3>
                            <p class="meta">{{ tkey('website.forms.callback.subtitle') }}</p>
                            @include('site.partials.callback-form', [
                                'programs' => $programs,
                                'branches' => $branches,
                            ])
                        </div>

                        <div class="card mt-18">
                            <h3>{{ tkey('website.forms.contact.title') }}</h3>
                            <p class="meta">{{ tkey('website.contacts.lead') }}</p>
                            @include('site.partials.contact-form')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
