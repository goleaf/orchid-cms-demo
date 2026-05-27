@php
    $programs = $programs ?? collect();
    $branches = $branches ?? collect();
    $selectedProgram ??= null;
    $selectedBranch ??= null;
    $selectedProgramId = $selectedProgram?->id ?? $selectedProgram ?? null;
    $selectedBranchId = $selectedBranch?->id ?? $selectedBranch ?? null;
@endphp

<form method="POST" action="{{ route('website.callback.store') }}" class="form-grid">
    @csrf
    @include('site.partials.tracking-fields', ['formName' => 'callback', 'source' => 'callback'])

    <label>
        {{ tkey('website.forms.fields.full_name') }}
        <input name="full_name" value="{{ old('full_name') }}" required>
        @error('full_name') <span class="error">{{ $message }}</span> @enderror
        @error('first_name') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        {{ tkey('website.forms.fields.phone') }}
        <input name="phone" value="{{ old('phone') }}" required>
        @error('phone') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        {{ tkey('website.forms.fields.course') }}
        <select name="course_id">
            <option value="">{{ tkey('website.forms.manager_select_group') }}</option>
            @forelse ($programs as $program)
                <option value="{{ $program->id }}" @selected((string) old('course_id', $selectedProgramId) === (string) $program->id)>
                    {{ $program->displayTitle() }}
                </option>
            @empty
            @endforelse
        </select>
        @error('course_id') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        {{ tkey('website.forms.fields.branch') }}
        <select name="branch_id">
            <option value="">{{ tkey('website.forms.manager_select_group') }}</option>
            @forelse ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>
                    {{ $branch->displayCity() }} · {{ $branch->displayName() }}
                </option>
            @empty
            @endforelse
        </select>
        @error('branch_id') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label class="full">
        {{ tkey('website.forms.fields.callback_time') }}
        <input name="callback_time" value="{{ old('callback_time') }}" placeholder="{{ tkey('website.forms.preferred_time_placeholder') }}">
        @error('callback_time') <span class="error">{{ $message }}</span> @enderror
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
        <button class="button" type="submit">{{ tkey('website.actions.callback') }}</button>
    </div>
</form>
