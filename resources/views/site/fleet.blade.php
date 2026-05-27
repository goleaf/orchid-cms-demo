@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">Fleet</p>
                <h1>Автопарк</h1>
                <p class="lead">Машины связаны с категорией, коробкой передач, филиалом, инструктором, доступностью, ТО и рабочим статусом.</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid three">
                    @forelse ($vehicles as $vehicle)
                        <article class="card">
                            <p class="kicker">{{ $vehicle->license_category }} · {{ $vehicle->branch->city }}</p>
                            <h3>{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                            <p class="meta">{{ $vehicle->description }}</p>
                            <div class="badge-list">
                                <span class="badge">{{ $vehicle->year }}</span>
                                <span class="badge">{{ $vehicle->transmission }}</span>
                                <span class="badge">{{ str($vehicle->status->value)->replace('_', ' ')->title() }}</span>
                                <span class="badge">{{ $vehicle->availability_summary }}</span>
                            </div>
                            <div class="facts">
                                <span class="fact">{{ $vehicle->registration_number }}</span>
                                <span class="fact">{{ $vehicle->instructor?->name ?? 'Reserve car' }}</span>
                            </div>
                            <div class="badge-list">
                                @foreach (($vehicle->features ?? []) as $feature)
                                    <span class="badge">{{ $feature }}</span>
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <article class="card">
                            <h3>Автопарк готовится к публикации</h3>
                            <p class="meta">Машины появятся после заполнения в ERP.</p>
                        </article>
                    @endforelse
                </div>

                {{ $vehicles->links() }}
            </div>
        </section>
    </main>
@endsection
