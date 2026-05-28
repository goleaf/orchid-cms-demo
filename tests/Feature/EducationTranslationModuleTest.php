<?php

namespace Tests\Feature;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use App\Models\TrainingGroupActivity;
use App\Models\TrainingGroupSchedulePattern;
use App\Models\TrainingGroupStatus;
use App\Models\TranslationString;
use Database\Seeders\EducationTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\TrainingGroupStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationTranslationModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');

        $this->seed(LanguageSeeder::class);
        $this->seed(EducationTranslationSeeder::class);
    }

    public function test_required_education_translation_keys_resolve_for_active_languages(): void
    {
        $keys = $this->requiredEducationKeys();
        $strings = TranslationString::query()
            ->with(['values:id,translation_string_id,language_code,value'])
            ->whereIn('key', $keys)
            ->get(['id', 'key'])
            ->keyBy('key');

        $this->assertSame([], array_values(array_diff($keys, $strings->keys()->all())));

        foreach ($keys as $key) {
            $values = $strings[$key]->values->pluck('value', 'language_code');

            foreach (['ru', 'en', 'lt', 'pl'] as $locale) {
                $this->assertTrue($values->has($locale), "{$key} is missing {$locale}");
                $this->assertNotSame('', trim((string) $values[$locale]), "{$key} has empty {$locale}");
                $this->assertNotSame($key, (string) $values[$locale], "{$key} fell back to its key for {$locale}");
            }
        }
    }

    public function test_education_validation_messages_and_attributes_are_translated(): void
    {
        $this->assertSame('Invalid group status transition.', tkey('education.groups.validation.invalid_status_transition', [], 'en'));
        $this->assertSame('Недопустимый переход статуса группы.', tkey('education.groups.validation.invalid_status_transition', [], 'ru'));
        $this->assertSame('group capacity', tkey('validation.attributes.training_group.capacity_total', [], 'en'));
        $this->assertSame('вместимость группы', tkey('validation.attributes.training_group.capacity_total', [], 'ru'));
    }

    public function test_education_dictionary_display_names_use_current_locale(): void
    {
        $this->seed(TrainingGroupStatusSeeder::class);

        app()->setLocale('ru');
        $this->assertSame('Набор открыт', TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail()->display_name);

        app()->setLocale('en');
        $this->assertSame('Recruiting', TrainingGroupStatus::query()->where('code', 'recruiting')->firstOrFail()->display_name);

        $program = LearningProgram::factory()->create([
            'name_translations' => [
                'ru' => 'RU program',
                'en' => 'EN program',
                'lt' => 'LT programa',
                'pl' => 'PL program',
            ],
        ]);
        $module = LearningProgramModule::factory()->create([
            'learning_program_id' => $program->id,
            'name_translations' => [
                'ru' => 'RU module',
                'en' => 'EN module',
                'lt' => 'LT modulis',
                'pl' => 'PL modul',
            ],
        ]);
        $topic = LearningTopic::factory()->create([
            'learning_program_module_id' => $module->id,
            'name_translations' => [
                'ru' => 'RU topic',
                'en' => 'EN topic',
                'lt' => 'LT tema',
                'pl' => 'PL temat',
            ],
        ]);

        app()->setLocale('lt');

        $this->assertSame('LT programa', $program->display_name);
        $this->assertSame('LT modulis', $module->display_name);
        $this->assertSame('LT tema', $topic->display_name);
    }

    public function test_schedule_pattern_day_and_activity_type_labels_are_translated(): void
    {
        app()->setLocale('en');

        $pattern = TrainingGroupSchedulePattern::factory()->create([
            'day_of_week' => 1,
        ]);
        $activity = TrainingGroupActivity::factory()->create([
            'type' => 'student_added',
        ]);

        $this->assertSame('Monday', $pattern->display_day);
        $this->assertSame('Student added', $activity->display_type);
    }

    public function test_missing_translation_falls_back_safely(): void
    {
        $program = LearningProgram::factory()->create([
            'code' => 'fallback-program',
            'name_translations' => [
                'ru' => 'Резервная программа',
            ],
        ]);

        app()->setLocale('en');

        $this->assertSame('Резервная программа', $program->display_name);
    }

    /**
     * @return array<int, string>
     */
    private function requiredEducationKeys(): array
    {
        return [
            'menu.education',
            'menu.education.groups',
            ...$this->prefixed('menu.education.groups.', [
                'all',
                'recruiting',
                'scheduled',
                'active',
                'completed',
                'cancelled',
                'archived',
            ]),
            'menu.education.programs',
            'menu.education.schedule_patterns',
            'menu.education.statuses',
            'menu.education.memberships',
            ...$this->prefixed('education.groups.', [
                'title',
                'create_title',
                'edit_title',
                'view_title',
            ]),
            ...$this->prefixed('education.groups.empty.', [
                'no_groups',
                'no_members',
                'no_schedule_patterns',
                'no_activities',
                'no_program',
            ]),
            ...$this->prefixed('education.groups.sections.', [
                'overview',
                'main_information',
                'translated_content',
                'course_and_branch',
                'dates',
                'capacity',
                'public_visibility',
                'learning_program',
                'schedule_patterns',
                'members',
                'activities',
                'notes',
                'system_data',
            ]),
            ...$this->prefixed('education.groups.fields.', [
                'id',
                'uuid',
                'group_number',
                'code',
                'name',
                'description',
                'public_description',
                'schedule_summary',
                'course',
                'course_category',
                'branch',
                'status',
                'learning_program',
                'manager',
                'administrator',
                'teacher',
                'start_date',
                'planned_end_date',
                'actual_end_date',
                'capacity_total',
                'capacity_reserved',
                'capacity_taken',
                'capacity_waitlist',
                'available_places',
                'capacity_percent',
                'timezone',
                'default_lesson_duration_minutes',
                'is_visible_on_site',
                'is_featured',
                'is_accepting_applications',
                'notes',
                'internal_notes',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ]),
            ...$this->prefixed('education.groups.actions.', [
                'create',
                'save',
                'save_and_return',
                'open',
                'edit',
                'archive',
                'change_status',
                'recalculate_capacity',
                'add_student',
                'remove_student',
                'waitlist_student',
                'transfer_student',
                'complete_membership',
                'create_schedule_pattern',
                'update_schedule_pattern',
                'delete_schedule_pattern',
                'publish_on_site',
                'hide_from_site',
                'assign_learning_program',
                'add_note',
                'export_csv',
                'clear_filters',
            ]),
            ...$this->prefixed('education.groups.messages.', [
                'created',
                'updated',
                'archived',
                'status_changed',
                'capacity_recalculated',
                'student_added',
                'student_removed',
                'student_waitlisted',
                'student_transferred',
                'membership_completed',
                'schedule_pattern_created',
                'schedule_pattern_updated',
                'schedule_pattern_deleted',
                'published_on_site',
                'hidden_from_site',
                'learning_program_assigned',
                'note_added',
            ]),
            ...$this->prefixed('education.groups.statuses.', [
                'draft',
                'recruiting',
                'almost_full',
                'full',
                'closed',
                'scheduled',
                'active',
                'paused',
                'completed',
                'cancelled',
                'archived',
            ]),
            ...$this->prefixed('education.groups.memberships.statuses.', [
                'invited',
                'pending',
                'active',
                'left',
                'waitlisted',
                'transferred',
                'removed',
                'completed',
                'cancelled',
            ]),
            ...$this->prefixed('education.groups.memberships.fields.', [
                'student',
                'enrollment',
                'group',
                'status',
                'joined_at',
                'left_at',
                'transfer_from_group',
                'transfer_to_group',
                'transfer_reason',
                'notes',
                'created_at',
            ]),
            ...$this->programKeys(),
            ...$this->scheduleKeys(),
            ...$this->prefixed('education.groups.activities.types.', [
                'created',
                'updated',
                'archived',
                'status_changed',
                'student_added',
                'student_removed',
                'student_waitlisted',
                'student_transferred_in',
                'student_transferred_out',
                'membership_completed',
                'schedule_pattern_created',
                'schedule_pattern_updated',
                'schedule_pattern_deleted',
                'capacity_changed',
                'published_on_site',
                'hidden_from_site',
                'completed',
                'cancelled',
                'note_added',
                'learning_program_assigned',
                'teacher_assigned',
                'manager_assigned',
            ]),
            ...$this->prefixed('education.groups.filters.', [
                'search',
                'status',
                'course',
                'course_category',
                'branch',
                'manager',
                'teacher',
                'start_date_from',
                'start_date_to',
                'only_visible_on_site',
                'only_accepting_applications',
                'only_open_for_enrollment',
                'only_full',
                'only_almost_full',
            ]),
            ...$this->prefixed('education.groups.segments.', [
                'all',
                'recruiting',
                'almost_full',
                'full',
                'scheduled',
                'active',
                'completed',
                'cancelled',
                'archived',
                'visible_on_site',
            ]),
            ...$this->validationKeys(),
            ...$this->permissionKeys(),
            ...$this->validationAttributeKeys(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function programKeys(): array
    {
        return [
            ...$this->prefixed('education.programs.', [
                'title',
                'create_title',
                'edit_title',
            ]),
            ...$this->prefixed('education.programs.empty.', [
                'no_programs',
                'no_modules',
                'no_topics',
            ]),
            ...$this->prefixed('education.programs.fields.', [
                'id',
                'uuid',
                'code',
                'name',
                'description',
                'course',
                'course_category',
                'is_default',
                'is_active',
                'sort_order',
                'created_at',
                'updated_at',
            ]),
            ...$this->prefixed('education.programs.actions.', [
                'create',
                'save',
                'open',
                'add_module',
                'add_topic',
                'activate',
                'deactivate',
                'set_default',
            ]),
            ...$this->prefixed('education.programs.messages.', [
                'created',
                'updated',
                'module_created',
                'topic_created',
            ]),
            ...$this->prefixed('education.programs.modules.', [
                'title',
                'create_title',
                'edit_title',
            ]),
            ...$this->prefixed('education.programs.modules.fields.', [
                'code',
                'type',
                'name',
                'description',
                'required_hours',
                'sort_order',
                'is_required',
                'is_active',
            ]),
            ...$this->prefixed('education.programs.modules.types.', [
                'theory',
                'practice',
                'exam_preparation',
                'internal_exam',
                'state_exam_preparation',
                'documents',
                'onboarding',
                'other',
            ]),
            ...$this->prefixed('education.programs.topics.', [
                'title',
                'create_title',
                'edit_title',
            ]),
            ...$this->prefixed('education.programs.topics.fields.', [
                'code',
                'name',
                'description',
                'estimated_hours',
                'sort_order',
                'is_required',
                'is_active',
            ]),
            ...$this->prefixed('education.programs.topics.defaults.', [
                'traffic_rules',
                'road_signs',
                'road_safety',
                'parking',
                'city_driving',
                'highway_driving',
                'exam_route',
                'first_drive',
                'safety',
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function scheduleKeys(): array
    {
        return [
            ...$this->prefixed('education.schedule_patterns.', [
                'title',
                'create_title',
                'edit_title',
            ]),
            ...$this->prefixed('education.schedule_patterns.fields.', [
                'type',
                'day_of_week',
                'start_time',
                'end_time',
                'classroom',
                'location',
                'notes',
                'is_active',
            ]),
            ...$this->prefixed('education.schedule_patterns.types.', [
                'theory',
                'practice',
                'consultation',
                'exam_preparation',
                'other',
            ]),
            ...$this->prefixed('common.days.', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function validationKeys(): array
    {
        return $this->prefixed('education.groups.validation.', [
            'invalid_status_transition',
            'group_cannot_be_updated',
            'group_cannot_be_archived',
            'capacity_exceeded',
            'capacity_lower_than_memberships',
            'invalid_capacity',
            'enrollment_cannot_join_group',
            'enrollment_already_in_active_group',
            'group_not_open_for_enrollment',
            'group_cannot_accept_applications',
            'start_date_after_end_date',
            'actual_end_before_start_date',
            'invalid_day_of_week',
            'end_time_before_start_time',
            'duplicate_schedule_pattern',
            'invalid_schedule_pattern_type',
            'learning_program_not_active',
            'invalid_module_type',
            'default_group_name_required',
            'default_learning_program_name_required',
            'group_cannot_be_published',
            'membership_cannot_be_transferred',
            'membership_cannot_be_removed',
            'student_required',
            'enrollment_required',
            'group_required',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function permissionKeys(): array
    {
        return [
            ...$this->prefixed('permissions.education.groups.', [
                'view',
                'create',
                'update',
                'archive',
                'delete',
                'change_status',
                'override_status_transition',
                'manage_students',
                'manage_schedule_patterns',
                'manage_statuses',
                'manage_public_visibility',
                'manage_learning_program',
                'export',
            ]),
            ...$this->prefixed('permissions.education.programs.', [
                'view',
                'create',
                'update',
                'delete',
                'manage_modules',
                'manage_topics',
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function validationAttributeKeys(): array
    {
        return [
            'validation.attributes.training_group.name_translations',
            'validation.attributes.training_group.course_id',
            'validation.attributes.training_group.branch_id',
            'validation.attributes.training_group.status_id',
            'validation.attributes.training_group.learning_program_id',
            'validation.attributes.training_group.start_date',
            'validation.attributes.training_group.planned_end_date',
            'validation.attributes.training_group.capacity_total',
            'validation.attributes.training_group.is_visible_on_site',
            'validation.attributes.training_group_membership.student_id',
            'validation.attributes.training_group_membership.student_enrollment_id',
            'validation.attributes.training_group_schedule_pattern.day_of_week',
            'validation.attributes.training_group_schedule_pattern.start_time',
            'validation.attributes.training_group_schedule_pattern.end_time',
            'validation.attributes.learning_program.name_translations',
            'validation.attributes.learning_program_module.type',
            'validation.attributes.learning_topic.name_translations',
        ];
    }

    /**
     * @param  array<int, string>  $suffixes
     * @return array<int, string>
     */
    private function prefixed(string $prefix, array $suffixes): array
    {
        return array_map(fn (string $suffix): string => $prefix.$suffix, $suffixes);
    }
}
