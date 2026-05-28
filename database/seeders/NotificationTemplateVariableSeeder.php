<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVariable;
use Illuminate\Database\Seeder;

class NotificationTemplateVariableSeeder extends Seeder
{
    public function run(): void
    {
        if (NotificationTemplate::query()->whereIn('code', array_keys($this->records()))->count() < count($this->records())) {
            $this->call(NotificationTemplateSeeder::class);
        }

        $templates = NotificationTemplate::query()
            ->whereIn('code', array_keys($this->records()))
            ->get(['id', 'code'])
            ->keyBy('code');

        foreach ($this->records() as $templateCode => $variables) {
            $template = $templates[$templateCode] ?? null;

            if ($template === null) {
                continue;
            }

            foreach (array_values($variables) as $index => $definition) {
                $variable = NotificationTemplateVariable::factory()->make([
                    'template_id' => $template->id,
                    'key' => $definition['key'],
                    'label_translations' => $definition['label_translations'],
                    'description_translations' => $definition['description_translations'],
                    'type' => $definition['type'],
                    'is_required' => $definition['is_required'] ?? true,
                    'default_value' => $definition['default_value'] ?? null,
                    'sort_order' => ($index + 1) * 10,
                    'metadata' => ['seeded' => true],
                ]);

                $attributes = $variable->only($variable->getFillable());
                unset($attributes['template_id'], $attributes['key']);

                NotificationTemplateVariable::query()->updateOrCreate(
                    [
                        'template_id' => $template->id,
                        'key' => $definition['key'],
                    ],
                    $attributes,
                );
            }
        }
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function records(): array
    {
        return [
            'student_welcome' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('school_name', 'School name'),
            ],
            'lead_follow_up' => [
                $this->variable('lead_name', 'Lead name'),
                $this->variable('manager_name', 'Manager name'),
            ],
            'lesson_reminder' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('lesson_date', 'Lesson date', 'date'),
                $this->variable('lesson_time', 'Lesson time'),
                $this->variable('instructor_name', 'Instructor name'),
            ],
            'driving_lesson_reminder' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('lesson_date', 'Lesson date', 'date'),
                $this->variable('lesson_time', 'Lesson time'),
            ],
            'payment_due' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('payment_amount', 'Payment amount', 'money'),
                $this->variable('due_date', 'Due date', 'date'),
            ],
            'document_missing' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('document_name', 'Document name'),
            ],
            'document_rejected' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('document_name', 'Document name'),
                $this->variable('rejection_reason', 'Rejection reason'),
            ],
            'exam_reminder' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('exam_date', 'Exam date', 'date'),
                $this->variable('exam_type', 'Exam type'),
            ],
            'contract_generated' => [
                $this->variable('student_name', 'Student name'),
                $this->variable('contract_number', 'Contract number'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function variable(string $key, string $label, string $type = 'string'): array
    {
        return [
            'key' => $key,
            'label_translations' => $this->translations(
                str($label)->replace('Student', 'Ученик')->replace('Lead', 'Лид')->toString(),
                $label,
                $label,
                $label,
            ),
            'description_translations' => $this->translations(
                'Переменная шаблона: '.$label,
                'Template variable: '.$label,
                'Sablono kintamasis: '.$label,
                'Zmienna szablonu: '.$label,
            ),
            'type' => $type,
            'is_required' => true,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
