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
                    <div class="grid">
                        @forelse ($branches as $branch)
                            <article class="card">
                                <p class="kicker">{{ $branch->displayCity() }}</p>
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
                                    <a class="button secondary" href="{{ route('site.branches.show', ['branch' => $branch->slug]) }}">{{ tkey('website.actions.open') }}</a>
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
                            <form method="POST" action="{{ route('site.callback.store') }}" class="form-grid">
                                @csrf
                                <input type="hidden" name="source" value="callback">
                                <input type="hidden" name="form_name" value="callback">
                                <input type="hidden" name="form_page" value="{{ url()->current() }}">

                                <label>
                                    {{ tkey('crm.leads.fields.first_name') }}
                                    <input name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name') <span class="error">{{ $message }}</span> @enderror
                                </label>
                                <label>
                                    {{ tkey('website.forms.fields.phone') }}
                                    <input name="phone" value="{{ old('phone') }}" required>
                                    @error('phone') <span class="error">{{ $message }}</span> @enderror
                                </label>
                                <label class="full">
                                    {{ tkey('website.forms.fields.callback_time') }}
                                    <input name="preferred_time" value="{{ old('preferred_time') }}" placeholder="{{ tkey('website.forms.preferred_time_placeholder') }}">
                                    @error('preferred_time') <span class="error">{{ $message }}</span> @enderror
                                </label>
                                <label class="full">
                                    {{ tkey('website.forms.fields.comment') }}
                                    <textarea name="message">{{ old('message') }}</textarea>
                                    @error('message') <span class="error">{{ $message }}</span> @enderror
                                </label>
                                <label class="full">
                                    <span class="check-row">
                                        <input type="checkbox" name="privacy_consent" value="1" @checked(old('privacy_consent')) required>
                                        {{ tkey('website.forms.fields.consent') }}
                                    </span>
                                    @error('privacy_consent') <span class="error">{{ $message }}</span> @enderror
                                </label>

                                <div class="actions full">
                                    <button class="button" type="submit">{{ tkey('website.actions.callback') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
