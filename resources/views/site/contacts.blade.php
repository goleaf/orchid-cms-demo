@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">Contacts</p>
                <h1>Филиалы и контакты</h1>
                <p class="lead">Карта филиалов, телефоны, email, онлайн-чат и заявка на обратный звонок.</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="grid two">
                    <div class="grid">
                        @forelse ($branches as $branch)
                            <article class="card">
                                <p class="kicker">{{ $branch->city }}</p>
                                <h3>{{ $branch->name }}</h3>
                                <p>{{ $branch->address }}</p>
                                <p class="meta">{{ $branch->phone }} · {{ $branch->email }}</p>
                                <div class="facts">
                                    <span class="fact">{{ $branch->groups_count }} groups</span>
                                    <span class="fact">{{ $branch->instructors_count }} instructors</span>
                                    <span class="fact">{{ $branch->vehicles_count }} cars</span>
                                </div>
                            </article>
                        @empty
                            <article class="card">
                                <h3>Филиалы готовятся</h3>
                                <p class="meta">Активные филиалы появятся после заполнения в админке.</p>
                            </article>
                        @endforelse
                    </div>

                    <div>
                        <div class="map">
                            <strong>Branch map</strong>
                        </div>
                        <div id="callback" class="card mt-18">
                            <h3>Обратный звонок</h3>
                            <p class="meta">Оставьте заявку, и менеджер перезвонит для подбора категории, филиала и времени.</p>
                            <div class="actions">
                                <a class="button" href="{{ route('site.apply', ['source' => 'callback']) }}">Заказать звонок</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
