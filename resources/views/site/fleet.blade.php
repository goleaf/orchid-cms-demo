@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.vehicles.kicker') }}</p>
                <h1>{{ tkey('website.vehicles.title') }}</h1>
                <p class="lead">{{ tkey('website.vehicles.lead') }}</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid three">
                    @forelse ($vehicles as $vehicle)
                        <article class="card">
                            <p class="kicker">{{ $vehicle->license_category }} · {{ $vehicle->branch->displayCity() }}</p>
                            <h3>{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                            <p class="meta">{{ $vehicle->description }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $vehicle->year }}</span>
                                <span class="badge">{{ tkey('website.transmissions.'.$vehicle->transmission) }}</span>
                                <span class="badge">{{ tkey('website.vehicles.status.'.$vehicle->status->value) }}</span>
                                <span class="badge">{{ $vehicle->availability_summary }}</span>
                            </div>
                            <div class="facts">
                                <span class="fact">{{ $vehicle->registration_number }}</span>
                                <span class="fact">{{ $vehicle->instructor?->name ?? tkey('website.vehicles.reserve') }}</span>
                            </div>
                            <div class="badge-list">
                                @forelse (($vehicle->features ?? []) as $feature)
                                    <span class="badge">{{ $feature }}</span>
                                @empty
                                @endforelse
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>{{ tkey('website.vehicles.empty.title') }}</h3>
                            <p class="meta">{{ tkey('website.vehicles.empty.body') }}</p>
                        </article>
                    @endforelse
                </div>

                {{ $vehicles->links() }}
            </div>
        </section>
    </main>
@endsection
