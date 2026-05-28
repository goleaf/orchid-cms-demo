@php
    $programs = $programs ?? collect();
    $branches = $branches ?? collect();
    $groups = $groups ?? collect();
    $selectedProgram ??= null;
    $selectedBranch ??= null;
    $selectedGroup ??= null;
    $formName ??= 'website_application';
    $lockProgram = (bool) ($lockProgram ?? false);
    $lockBranch = (bool) ($lockBranch ?? false);
    $selectedProgramId = $selectedProgram?->id ?? $selectedProgram ?? null;
    $selectedBranchId = $selectedBranch?->id ?? $selectedBranch ?? null;
    $selectedProgramModel = is_object($selectedProgram) ? $selectedProgram : $programs->firstWhere('id', $selectedProgramId);
    $selectedBranchModel = is_object($selectedBranch) ? $selectedBranch : $branches->firstWhere('id', $selectedBranchId);
    $selectedGroupId = $selectedGroup?->id ?? $selectedGroup ?? null;
    $programContext = $selectedProgramModel?->displayTitle() ?? '';
    $branchContext = collect([
        $selectedBranchModel?->displayCountry(),
        $selectedBranchModel?->displayCity(),
        $selectedBranchModel?->displayName(),
    ])->filter()->implode(' · ');
    $firstError = static fn (array $fields): string => collect($fields)
        ->map(fn (string $field): ?string => $errors->first($field))
        ->first(fn (?string $message): bool => filled($message)) ?? '';
    $courseError = $firstError(['course_id', 'training_program_id']);
    $branchError = $firstError(['branch_id']);
    $groupError = $firstError(['training_group_id']);
    $nameError = $firstError(['full_name', 'first_name']);
    $phoneError = $firstError(['phone']);
    $emailError = $firstError(['email']);
    $preferredTimeError = $firstError(['preferred_time']);
    $messengerError = $firstError(['preferred_messenger', 'messenger']);
    $commentError = $firstError(['comment', 'message']);
    $consentError = $firstError(['consent_accepted', 'privacy_consent']);
@endphp

<form
    method="POST"
    action="{{ route('website.leads.store') }}"
    class="card"
    data-ajax-lead-form
    data-error-message="{{ tkey('website.forms.messages.error') }}"
    data-submit-label="{{ tkey('website.forms.messages.submitting') }}"
    novalidate
>
    @csrf
    @include('site.partials.tracking-fields', ['formName' => $formName, 'source' => 'website'])
    <input type="hidden" name="preferred_format" value="{{ old('preferred_format', 'mixed') }}">
    <input type="hidden" name="preferred_language" value="{{ old('preferred_language', app()->getLocale()) }}">

    <p class="form-alert error" data-form-alert role="alert" @if (! $errors->any()) hidden @endif>
        @if ($errors->any())
            {{ tkey('website.forms.messages.error') }}
        @endif
    </p>

    <div class="form-grid">
        @if ($lockProgram && $selectedProgramId)
            <div class="context-summary">
                <input type="hidden" name="course_id" value="{{ $selectedProgramId }}">
                <span>{{ tkey('website.forms.context.selected_course') }}</span>
                <strong>{{ $programContext }}</strong>
            </div>
            <span class="error" data-error-for="course_id training_program_id" @if (! $courseError) hidden @endif>{{ $courseError }}</span>
        @else
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
                <span class="error" data-error-for="course_id training_program_id" @if (! $courseError) hidden @endif>{{ $courseError }}</span>
            </label>
        @endif

        @if ($lockBranch && $selectedBranchId)
            <div class="context-summary">
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                <span>{{ tkey('website.forms.context.selected_branch') }}</span>
                <strong>{{ $branchContext }}</strong>
            </div>
            <span class="error" data-error-for="branch_id" @if (! $branchError) hidden @endif>{{ $branchError }}</span>
        @else
            <label>
                {{ tkey('website.forms.fields.branch') }}
                <select name="branch_id" required>
                    @forelse ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>
                            {{ $branch->displayCountry() }} · {{ $branch->displayCity() }} · {{ $branch->displayName() }}
                        </option>
                    @empty
                        <option value="">{{ tkey('website.branches.empty.no_branches') }}</option>
                    @endforelse
                </select>
                <span class="error" data-error-for="branch_id" @if (! $branchError) hidden @endif>{{ $branchError }}</span>
            </label>
        @endif

        <label>
            {{ tkey('website.forms.fields.training_group') }}
            <select name="training_group_id">
                <option value="">{{ tkey('website.forms.manager_select_group') }}</option>
                @forelse ($groups as $group)
                    <option value="{{ $group->id }}" @selected((string) old('training_group_id', $selectedGroupId) === (string) $group->id)>
                        {{ $group->displayName() }} · {{ $group->trainingProgram?->displayTitle() }} · {{ $group->branch?->displayCountry() }} · {{ $group->branch?->displayCity() }}
                    </option>
                @empty
                @endforelse
            </select>
            <span class="error" data-error-for="training_group_id" @if (! $groupError) hidden @endif>{{ $groupError }}</span>
        </label>

        <label>
            {{ tkey('website.forms.fields.full_name') }}
            <input name="full_name" value="{{ old('full_name') }}" required>
            <span class="error" data-error-for="full_name first_name" @if (! $nameError) hidden @endif>{{ $nameError }}</span>
        </label>

        <label>
            {{ tkey('website.forms.fields.phone') }}
            <input name="phone" value="{{ old('phone') }}">
            <span class="error" data-error-for="phone" @if (! $phoneError) hidden @endif>{{ $phoneError }}</span>
        </label>

        <label>
            {{ tkey('website.forms.fields.email') }}
            <input type="email" name="email" value="{{ old('email') }}">
            <span class="error" data-error-for="email" @if (! $emailError) hidden @endif>{{ $emailError }}</span>
        </label>

        <label>
            {{ tkey('website.forms.fields.preferred_time') }}
            <input name="preferred_time" value="{{ old('preferred_time') }}" placeholder="{{ tkey('website.forms.preferred_time_placeholder') }}">
            <span class="error" data-error-for="preferred_time" @if (! $preferredTimeError) hidden @endif>{{ $preferredTimeError }}</span>
        </label>

        <label>
            {{ tkey('website.forms.fields.preferred_messenger') }}
            <input name="preferred_messenger" value="{{ old('preferred_messenger') }}" placeholder="{{ tkey('website.forms.messenger_placeholder') }}">
            <span class="error" data-error-for="preferred_messenger messenger" @if (! $messengerError) hidden @endif>{{ $messengerError }}</span>
        </label>

        <label class="full">
            {{ tkey('website.forms.fields.comment') }}
            <textarea name="comment">{{ old('comment') }}</textarea>
            <span class="error" data-error-for="comment message" @if (! $commentError) hidden @endif>{{ $commentError }}</span>
        </label>

        <label class="full">
            <span class="check-row">
                <input type="checkbox" name="consent_accepted" value="1" @checked(old('consent_accepted')) required>
                {{ tkey('website.forms.fields.consent') }}
            </span>
            <span class="error" data-error-for="consent_accepted privacy_consent" @if (! $consentError) hidden @endif>{{ $consentError }}</span>
        </label>
    </div>

    <div class="actions">
        <button class="button" type="submit">{{ tkey('website.actions.submit') }}</button>
    </div>
</form>
