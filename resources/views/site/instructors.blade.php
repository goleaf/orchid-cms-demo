@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.instructors.kicker') }}</p>
                <h1>{{ tkey('website.instructors.title') }}</h1>
                <p class="lead">{{ tkey('website.instructors.lead') }}</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid three">
                    @forelse ($instructors as $instructor)
                        <article class="card">
                            <p class="kicker">{{ $instructor->branch->displayCountry() }} · {{ $instructor->branch->displayCity() }}</p>
                            <h3>{{ $instructor->name }}</h3>
                            <p class="meta">{{ $instructor->bio }}</p>
                            <p>{{ $instructor->teaching_style }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ tkey('website.instructors.experience_years', ['years' => $instructor->experience_years]) }}</span>
                                <span class="badge">{{ tkey('website.instructors.rating', ['rating' => $instructor->rating]) }}</span>
                                <span class="badge">{{ tkey('website.instructors.reviews_count', ['count' => $instructor->reviews_count]) }}</span>
                                @forelse (($instructor->categories ?? []) as $category)
                                    <span class="badge">{{ $category }}</span>
                                @empty
                                @endforelse
                                @forelse (($instructor->languages ?? []) as $language)
                                    <span class="badge">{{ $language }}</span>
                                @empty
                                @endforelse
                            </div>
                            <p class="meta">{{ $instructor->availability_summary }}</p>
                            <div class="facts">
                                @forelse ($instructor->vehicles as $vehicle)
                                    <span class="fact">{{ $vehicle->make }} {{ $vehicle->model }}</span>
                                @empty
                                @endforelse
                            </div>
                            <div class="actions">
                                <a class="button" href="{{ route('site.apply', ['instructor' => $instructor->id]) }}">{{ tkey('website.instructors.action_apply') }}</a>
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.instructors.empty.title') }}</h3>
                            <p class="meta">{{ tkey('website.instructors.empty.body') }}</p>
                        </article>
                    @endforelse
                </div>

                {{ $instructors->links() }}
            </div>
        </section>
    </main>
@endsection
