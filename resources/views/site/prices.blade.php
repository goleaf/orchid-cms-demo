@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.prices.kicker') }}</p>
                <h1>{{ tkey('website.prices.title') }}</h1>
                <p class="lead">{{ tkey('website.prices.lead') }}</p>
            </div>
        </section>

        <section class="section">
            <div class="section-inner">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ tkey('website.prices.columns.course') }}</th>
                                <th>{{ tkey('website.prices.columns.category') }}</th>
                                <th>{{ tkey('website.prices.columns.duration') }}</th>
                                <th>{{ tkey('website.prices.columns.hours') }}</th>
                                <th>{{ tkey('website.prices.columns.format') }}</th>
                                <th>{{ tkey('website.prices.columns.price') }}</th>
                                <th>{{ tkey('website.prices.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($programs as $program)
                                <tr>
                                    <td>
                                        <strong>{{ $program->displayTitle() }}</strong>
                                        <br>
                                        <span class="meta">{{ $program->displayShortDescription() }}</span>
                                    </td>
                                    <td>{{ $program->license_category }}</td>
                                    <td>{{ tkey('website.course.duration_weeks', ['weeks' => $program->duration_weeks]) }}</td>
                                    <td>{{ tkey('website.prices.hours_value', ['theory' => $program->theory_hours, 'practice' => $program->practice_hours]) }}</td>
                                    <td>{{ tkey('website.courses.formats.'.$program->format) }}</td>
                                    <td>
                                        <strong>{{ $program->priceForHumans() }}</strong>
                                        @if ($program->oldPriceForHumans())
                                            <br><span class="meta">{{ tkey('website.course.price.old', ['price' => $program->oldPriceForHumans()]) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="button secondary" href="{{ route('site.courses.show', $program) }}">{{ tkey('website.actions.view_course') }}</a>
                                        <a class="button" href="{{ route('site.apply', ['program' => $program->id]) }}">{{ tkey('website.actions.apply') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">{{ tkey('website.home.empty.programs_body') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.prices.packages.kicker') }}</p>
                        <h2>{{ tkey('website.prices.packages.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.prices.packages.lead') }}</p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ tkey('website.prices.columns.package') }}</th>
                                <th>{{ tkey('website.prices.columns.course') }}</th>
                                <th>{{ tkey('website.prices.columns.hours') }}</th>
                                <th>{{ tkey('website.prices.columns.price') }}</th>
                                <th>{{ tkey('website.prices.columns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($packages as $package)
                                <tr>
                                    <td>
                                        <strong>{{ $package->displayName() }}</strong>
                                        @if ($package->is_featured)
                                            <br><span class="fact">{{ tkey('website.prices.packages.featured') }}</span>
                                        @endif
                                        @if ($package->displayDescription())
                                            <br><span class="meta">{{ $package->displayDescription() }}</span>
                                        @endif
                                        @foreach ($package->displayFeatures() as $feature)
                                            <br><span class="meta">{{ $feature }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if ($package->course)
                                            <a href="{{ route('site.courses.show', $package->course) }}">{{ $package->course->displayTitle() }}</a>
                                        @else
                                            {{ $package->category?->displayName() ?? tkey('website.prices.packages.no_course') }}
                                        @endif
                                    </td>
                                    <td>{{ tkey('website.prices.hours_value', ['theory' => $package->theory_hours ?? 0, 'practice' => $package->practice_hours ?? 0]) }}</td>
                                    <td>
                                        <strong>{{ $package->priceForHumans() }}</strong>
                                        @if ($package->oldPriceForHumans())
                                            <br><span class="meta">{{ tkey('website.course.price.old', ['price' => $package->oldPriceForHumans()]) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="button" href="{{ route('site.apply', array_filter(['program' => $package->course_id])) }}">{{ tkey('website.actions.apply') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">{{ tkey('website.prices.packages.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="section soft">
            <div class="section-inner">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ tkey('website.prices.payment.kicker') }}</p>
                        <h2>{{ tkey('website.prices.payment.title') }}</h2>
                    </div>
                    <p class="lead">{{ tkey('website.prices.payment.lead') }}</p>
                </div>

                <div class="grid three">
                    <article class="card">
                        <h3>{{ tkey('website.prices.payment.installments.title') }}</h3>
                        <p class="meta">{{ tkey('website.prices.payment.installments.body') }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.prices.payment.included.title') }}</h3>
                        <p class="meta">{{ tkey('website.prices.payment.included.body') }}</p>
                    </article>
                    <article class="card">
                        <h3>{{ tkey('website.prices.payment.extra.title') }}</h3>
                        <p class="meta">{{ tkey('website.prices.payment.extra.body') }}</p>
                    </article>
                </div>
            </div>
        </section>
    </main>
@endsection
