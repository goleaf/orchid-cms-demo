@php
    $programs = $programs ?? collect();
    $branches = $branches ?? collect();
    $groups = $groups ?? collect();
    $selectedProgram ??= null;
    $selectedBranch ??= null;
    $selectedGroup ??= null;
    $formName ??= 'website_application';
    $selectedProgramId = $selectedProgram?->id ?? $selectedProgram ?? null;
    $selectedBranchId = $selectedBranch?->id ?? $selectedBranch ?? null;
    $selectedGroupId = $selectedGroup?->id ?? $selectedGroup ?? null;
@endphp

<form method="POST" action="{{ route('website.leads.store') }}" class="card">
    @csrf
    @include('site.partials.tracking-fields', ['formName' => $formName, 'source' => 'website'])
    <input type="hidden" name="preferred_format" value="{{ old('preferred_format', 'mixed') }}">
    <input type="hidden" name="preferred_language" value="{{ old('preferred_language', app()->getLocale()) }}">

    <div class="form-grid">
        <label>
            {{ tkey('website.forms.fields.course') }}
            <select name="course_id" required>
                @forelse ($programs as $program)
                    <option value="{{ $program->id }}" @selected((string) old('course_id', $selectedProgramId) === (string) $program->id)>
                        {{ $program->displayTitle() }}
                    </option>
                @empty
                    <option value="">{{ tkey('website.courses.empty.no_courses') }}</option>
                @endforelse
            </select>
            @error('course_id') <span class="error">{{ $message }}</span> @enderror
            @error('training_program_id') <span class="error">{{ $message }}</span> @enderror
        </label>

        <label>
            {{ tkey('website.forms.fields.branch') }}
            <select name="branch_id" required>
                @forelse ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>
                        {{ $branch->displayCity() }} · {{ $branch->displayName() }}
                    </option>
                @empty
                    <option value="">{{ tkey('website.branches.empty.no_branches') }}</option>
                @endforelse
            </select>
            @error('branch_id') <span class="error">{{ $message }}</span> @enderror
        </label>

        <label>
            {{ tkey('website.forms.fields.training_group') }}
            <select name="training_group_id">
                <option value="">{{ tkey('website.forms.manager_select_group') }}</option>
                @forelse ($groups as $group)
                    <option value="{{ $group->id }}" @selected((string) old('training_group_id', $selectedGroupId) === (string) $group->id)>
                        {{ $group->displayName() }} · {{ $group->trainingProgram?->displayTitle() }} · {{ $group->branch?->displayCity() }}
                    </option>
                @empty
                @endforelse
            </select>
            @error('training_group_id') <span class="error">{{ $message }}</span> @enderror
        </label>

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

        <label>
            {{ tkey('website.forms.fields.email') }}
            <input type="email" name="email" value="{{ old('email') }}">
            @error('email') <span class="error">{{ $message }}</span> @enderror
        </label>

        <label>
            {{ tkey('website.forms.fields.preferred_time') }}
            <input name="preferred_time" value="{{ old('preferred_time') }}" placeholder="{{ tkey('website.forms.preferred_time_placeholder') }}">
            @error('preferred_time') <span class="error">{{ $message }}</span> @enderror
        </label>

        <label>
            {{ tkey('website.forms.fields.preferred_messenger') }}
            <input name="preferred_messenger" value="{{ old('preferred_messenger') }}" placeholder="{{ tkey('website.forms.messenger_placeholder') }}">
            @error('preferred_messenger') <span class="error">{{ $message }}</span> @enderror
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
    </div>

    <div class="actions">
        <button class="button" type="submit">{{ tkey('website.actions.submit') }}</button>
    </div>
</form>
