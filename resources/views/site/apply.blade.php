@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">{{ tkey('website.apply.kicker') }}</p>
                <h1>{{ tkey('website.forms.apply.title') }}</h1>
                <p class="lead">{{ tkey('website.forms.apply.subtitle') }}</p>
            </div>
        </section>

        <section class="section" id="application-form">
            <div class="section-inner">
                @if (session('status'))
                    <p class="notice">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('site.apply.store') }}" enctype="multipart/form-data" class="card">
                    @csrf

                    <input type="hidden" name="source" value="{{ old('source', $tracking['source']) }}">
                    <input type="hidden" name="utm_source" value="{{ old('utm_source', $tracking['utm_source']) }}">
                    <input type="hidden" name="utm_medium" value="{{ old('utm_medium', $tracking['utm_medium']) }}">
                    <input type="hidden" name="utm_campaign" value="{{ old('utm_campaign', $tracking['utm_campaign']) }}">
                    <input type="hidden" name="utm_term" value="{{ old('utm_term', $tracking['utm_term']) }}">
                    <input type="hidden" name="utm_content" value="{{ old('utm_content', $tracking['utm_content']) }}">
                    <input type="hidden" name="referrer_url" value="{{ old('referrer_url', $tracking['referrer_url']) }}">
                    <input type="hidden" name="landing_page" value="{{ old('landing_page', $tracking['landing_page']) }}">
                    <input type="hidden" name="form_page" value="{{ old('form_page', $tracking['form_page']) }}">
                    <input type="hidden" name="form_name" value="{{ old('form_name', $tracking['form_name']) }}">

                    <div class="form-grid">
                        <label>
                            {{ tkey('website.forms.fields.course') }}
                            <select name="training_program_id" required>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}" @selected((string) old('training_program_id', $tracking['program']) === (string) $program->id)>
                                        {{ $program->displayTitle() }} · {{ $program->priceForHumans() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('training_program_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('website.forms.fields.branch') }}
                            <select name="branch_id" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $tracking['branch']) === (string) $branch->id)>
                                        {{ $branch->displayCountry() }} · {{ $branch->displayCity() }} · {{ $branch->displayName() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('website.forms.fields.training_group') }}
                            <select name="training_group_id">
                                <option value="">{{ tkey('website.forms.manager_select_group') }}</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" @selected((string) old('training_group_id', $tracking['group']) === (string) $group->id)>
                                        {{ $group->code }} · {{ $group->trainingProgram->displayTitle() }} · {{ $group->branch->displayCountry() }} · {{ $group->branch->displayCity() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('training_group_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.leads.fields.instructor') }}
                            <select name="instructor_id">
                                <option value="">{{ tkey('website.forms.no_instructor_preference') }}</option>
                                @foreach ($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" @selected((string) old('instructor_id', $tracking['instructor']) === (string) $instructor->id)>
                                        {{ $instructor->name }} · {{ $instructor->branch->displayCountry() }} · {{ $instructor->branch->displayCity() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.leads.fields.preferred_format') }}
                            <select name="preferred_format" required>
                                @foreach ($formats as $value => $label)
                                    <option value="{{ $value }}" @selected(old('preferred_format', 'mixed') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('preferred_format') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.leads.fields.locale') }}
                            <select name="preferred_language" required>
                                @foreach ($languages as $code => $language)
                                    <option value="{{ $code }}" @selected(old('preferred_language', app()->getLocale()) === $code)>{{ $language }}</option>
                                @endforeach
                            </select>
                            @error('preferred_language') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.leads.fields.first_name') }}
                            <input name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.leads.fields.last_name') }}
                            <input name="last_name" value="{{ old('last_name') }}">
                            @error('last_name') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('website.forms.fields.email') }}
                            <input type="email" name="email" value="{{ old('email') }}">
                            @error('email') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('website.forms.fields.phone') }}
                            <input name="phone" value="{{ old('phone') }}">
                            @error('phone') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('website.forms.fields.preferred_messenger') }}
                            <input name="messenger" value="{{ old('messenger') }}" placeholder="{{ tkey('website.forms.messenger_placeholder') }}">
                            @error('messenger') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.leads.fields.city') }}
                            <input name="city" value="{{ old('city') }}" placeholder="{{ tkey('website.forms.city_placeholder') }}">
                            @error('city') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('website.forms.fields.preferred_time') }}
                            <input name="preferred_time" value="{{ old('preferred_time') }}" placeholder="{{ tkey('website.forms.preferred_time_placeholder') }}">
                            @error('preferred_time') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.leads.fields.budget') }}
                            <input type="number" min="0" step="10" name="budget_eur" value="{{ old('budget_eur') }}">
                            @error('budget_eur') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            {{ tkey('crm.documents.title') }}
                            <input type="file" name="documents[]" multiple>
                            @error('documents') <span class="error">{{ $message }}</span> @enderror
                            @error('documents.*') <span class="error">{{ $message }}</span> @enderror
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
                    </div>

                    <div class="actions">
                        <button class="button" type="submit">{{ tkey('website.actions.submit') }}</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
@endsection
