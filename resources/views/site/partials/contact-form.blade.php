<form method="POST" action="{{ route('website.contact.store') }}" class="form-grid">
    @csrf
    @include('site.partials.tracking-fields', ['formName' => 'contact', 'source' => 'website'])

    <label>
        {{ tkey('website.forms.fields.full_name') }}
        <input name="full_name" value="{{ old('full_name') }}" required>
        @error('full_name') <span class="error">{{ $message }}</span> @enderror
        @error('first_name') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        {{ tkey('website.forms.fields.phone') }}
        <input name="phone" value="{{ old('phone') }}">
        @error('phone') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label class="full">
        {{ tkey('website.forms.fields.email') }}
        <input type="email" name="email" value="{{ old('email') }}">
        @error('email') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label class="full">
        {{ tkey('website.forms.fields.comment') }}
        <textarea name="comment">{{ old('comment') }}</textarea>
        @error('comment') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label class="full">
        <span class="check-row">
            <input type="checkbox" name="consent_accepted" value="1" @checked(old('consent_accepted')) required>
            {{ tkey('website.forms.fields.consent') }}
        </span>
        @error('consent_accepted') <span class="error">{{ $message }}</span> @enderror
        @error('privacy_consent') <span class="error">{{ $message }}</span> @enderror
    </label>

    <div class="actions full">
        <button class="button" type="submit">{{ tkey('website.actions.send') }}</button>
    </div>
</form>
